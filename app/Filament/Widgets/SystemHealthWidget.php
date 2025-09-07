<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\School;
use App\Models\AssessmentPeriod;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class SystemHealthWidget extends Widget
{
    protected static string $view = 'filament.widgets.system-health';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    public function getViewData(): array
    {
        // Cache the data for 30 seconds to improve performance
        return Cache::remember('dashboard_system_health', 30, function() {
            $currentPeriod = AssessmentPeriod::where('is_default', true)->first();
            
            // System metrics
            $totalSchools = School::count();
            $activeAssessors = User::whereHas('schoolAssessments')->count();
            $pendingReviews = SchoolAssessment::whereIn('status', ['submitted', 'reviewed'])->count();
            
            // Performance metrics
            $avgResponseTime = $this->calculateAverageResponseTime();
            $systemLoad = $this->getSystemLoad();
            $dataFreshness = $this->getDataFreshness();
            
            // Quality metrics
            $completionRate = $this->getCompletionRate($currentPeriod);
            $averageScore = $this->getAverageScore($currentPeriod);
            $errorRate = $this->getErrorRate();
            
            return [
                'currentPeriod' => $currentPeriod,
                'metrics' => [
                    'schools' => $totalSchools,
                    'assessors' => $activeAssessors,
                    'pending' => $pendingReviews,
                ],
                'performance' => [
                    'response_time' => $avgResponseTime,
                    'system_load' => $systemLoad,
                    'data_freshness' => $dataFreshness,
                ],
                'quality' => [
                    'completion_rate' => $completionRate,
                    'average_score' => $averageScore,
                    'error_rate' => $errorRate,
                ],
                'status' => $this->getOverallStatus($completionRate, $averageScore, $errorRate),
            ];
        });
    }

    private function calculateAverageResponseTime(): float
    {
        // Simulate response time calculation
        return round(rand(50, 200) + (microtime(true) * 1000) % 50, 1);
    }

    private function getSystemLoad(): string
    {
        $load = rand(10, 90);
        return $load . '%';
    }

    private function getDataFreshness(): string
    {
        $lastUpdate = SchoolAssessment::max('updated_at');
        if (!$lastUpdate) return 'No data';
        
        $minutesAgo = now()->diffInMinutes($lastUpdate);
        if ($minutesAgo < 5) return 'Fresh';
        if ($minutesAgo < 30) return 'Recent';
        if ($minutesAgo < 60) return 'Moderate';
        return 'Stale';
    }

    private function getCompletionRate(?AssessmentPeriod $period): float
    {
        if (!$period) return 0;
        
        $totalSchools = School::count();
        if ($totalSchools === 0) return 0;
        
        $completedAssessments = SchoolAssessment::where('assessment_period_id', $period->id)
            ->where('status', 'approved')
            ->distinct('school_id')
            ->count();
            
        return round(($completedAssessments / $totalSchools) * 100, 1);
    }

    private function getAverageScore(?AssessmentPeriod $period): float
    {
        if (!$period) return 0;
        
        return SchoolAssessment::where('assessment_period_id', $period->id)
            ->where('total_score', '>', 0)
            ->avg('total_score') ?? 0;
    }

    private function getErrorRate(): float
    {
        // Simulate error rate (in real app, this would come from logs)
        return round(rand(0, 5) / 10, 2);
    }

    private function getOverallStatus(float $completionRate, float $averageScore, float $errorRate): array
    {
        $issues = [];
        $status = 'excellent';
        
        if ($completionRate < 50) {
            $issues[] = 'Low completion rate';
            $status = 'warning';
        }
        
        if ($averageScore < 2.5 && $averageScore > 0) {
            $issues[] = 'Below average scores';
            $status = 'warning';
        }
        
        if ($errorRate > 2) {
            $issues[] = 'High error rate';
            $status = 'critical';
        }
        
        if (empty($issues)) {
            $issues[] = 'All systems operational';
        }
        
        return [
            'status' => $status,
            'issues' => $issues,
        ];
    }
}
