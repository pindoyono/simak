<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AssessmentIndicator;

trait AssessmentMemoryOptimization
{
    /**
     * Memory monitoring and optimization for Assessment Wizard
     */

    private $indicatorCache = [];

    /**
     * Check and log current memory usage
     */
    protected function checkMemoryUsage(string $context = ''): void
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();

        $memoryUsageMB = round($memoryUsage / 1024 / 1024, 2);
        $memoryPeakMB = round($memoryPeak / 1024 / 1024, 2);
        $memoryLimitMB = round($memoryLimit / 1024 / 1024, 2);

        if (config('assessment.performance.monitor_memory', true)) {
            Log::info("Memory Usage [$context]: Current: {$memoryUsageMB}MB, Peak: {$memoryPeakMB}MB, Limit: {$memoryLimitMB}MB");
        }

        // Warning if approaching memory limit
        $warningThreshold = config('assessment.performance.memory_warning_threshold', 100 * 1024 * 1024);
        if ($memoryUsage > $warningThreshold) {
            Log::warning("High memory usage detected [$context]: {$memoryUsageMB}MB");
            $this->optimizeMemory();
        }
    }

    /**
     * Get memory limit in bytes
     */
    protected function getMemoryLimit(): int
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit == -1) {
            return PHP_INT_MAX;
        }

        $memoryLimit = strtolower(trim($memoryLimit));
        $lastChar = substr($memoryLimit, -1);
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($lastChar) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Optimize memory usage by clearing caches and variables
     */
    protected function optimizeMemory(): void
    {
        // Clear internal caches
        $this->indicatorCache = [];

        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        Log::info('Memory optimization performed');
    }

    /**
     * Get indicators with caching to prevent N+1 queries
     */
    protected function getCachedIndicators(array $indicatorIds): \Illuminate\Support\Collection
    {
        sort($indicatorIds);
        $cacheKey = 'indicators_' . md5(implode(',', $indicatorIds));

        if (config('assessment.query_optimization.enable_caching', true)) {
            return Cache::remember($cacheKey, config('assessment.query_optimization.cache_ttl', 300), function () use ($indicatorIds) {
                return $this->loadIndicators($indicatorIds);
            });
        }

        return $this->loadIndicators($indicatorIds);
    }

    /**
     * Load indicators with optimized query
     */
    private function loadIndicators(array $indicatorIds): \Illuminate\Support\Collection
    {
        $this->checkMemoryUsage('before_indicator_load');

        $maxBatchSize = config('assessment.query_optimization.max_batch_size', 100);

        // If too many indicators, process in batches
        if (count($indicatorIds) > $maxBatchSize) {
            $indicators = collect();
            $batches = array_chunk($indicatorIds, $maxBatchSize);

            foreach ($batches as $batch) {
                $batchIndicators = AssessmentIndicator::whereIn('id', $batch)
                    ->select('id', 'skor_maksimal', 'bobot_indikator', 'nama_indikator')
                    ->get();

                $indicators = $indicators->merge($batchIndicators);

                // Memory check after each batch
                $this->checkMemoryUsage('after_batch_load');
            }

            return $indicators->keyBy('id');
        }

        $indicators = AssessmentIndicator::whereIn('id', $indicatorIds)
            ->select('id', 'skor_maksimal', 'bobot_indikator', 'nama_indikator')
            ->get()
            ->keyBy('id');

        $this->checkMemoryUsage('after_indicator_load');

        return $indicators;
    }

    /**
     * Set memory limit for current operation
     */
    protected function setMemoryLimit(string $operation = 'default'): void
    {
        $limits = config('assessment.memory_limit', []);

        $memoryLimit = match($operation) {
            'assessment_wizard' => $limits['assessment_wizard'] ?? '512M',
            'bulk_operations' => $limits['bulk_operations'] ?? '1G',
            'file_uploads' => $limits['file_uploads'] ?? '256M',
            default => '512M'
        };

        ini_set('memory_limit', $memoryLimit);

        Log::info("Memory limit set to {$memoryLimit} for operation: {$operation}");
    }

    /**
     * Set execution time limit for current operation
     */
    protected function setExecutionTimeLimit(): void
    {
        $maxTime = config('assessment.performance.max_execution_time', 300);
        set_time_limit($maxTime);

        Log::info("Execution time limit set to {$maxTime} seconds");
    }
}
