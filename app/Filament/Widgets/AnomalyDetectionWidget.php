<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\School;
use App\Models\AssessmentPeriod;
use App\Models\Assessor;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnomalyDetectionWidget extends Widget
{
    protected static string $view = 'filament.widgets.anomaly-detection';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 10;
    
    public ?string $filter = 'all';
    
    protected static ?string $pollingInterval = '300s';

    public function getViewData(): array
    {
        return Cache::remember("anomaly_detection_{$this->filter}", 900, function () {
            return [
                'performanceAnomalies' => $this->detectPerformanceAnomalies(),
                'patternAnomalies' => $this->detectPatternAnomalies(),
                'timelineAnomalies' => $this->detectTimelineAnomalies(),
                'qualityAnomalies' => $this->detectQualityAnomalies(),
                'behavioralAnomalies' => $this->detectBehavioralAnomalies(),
                'systemAnomalies' => $this->detectSystemAnomalies(),
                'anomalySummary' => $this->generateAnomalySummary(),
                'filter' => $this->filter,
            ];
        });
    }

    protected function detectPerformanceAnomalies(): array
    {
        $anomalies = [];

        // Statistical outliers in school performance
        $schoolPerformances = School::with(['assessments' => function($query) {
            $query->whereHas('period', function($periodQuery) {
                $periodQuery->where('end_date', '>=', now()->subMonths(12));
            });
        }])->get()->map(function($school) {
            $scores = $school->assessments->pluck('total_score');
            return [
                'school' => $school,
                'avg_score' => $scores->avg(),
                'score_count' => $scores->count(),
                'std_deviation' => $this->calculateStandardDeviation($scores->toArray()),
                'recent_scores' => $scores->take(3)->toArray(),
            ];
        })->filter(function($data) {
            return $data['score_count'] >= 2; // Need at least 2 assessments
        });

        $overallAvg = $schoolPerformances->avg('avg_score');
        $overallStdDev = $this->calculateStandardDeviation($schoolPerformances->pluck('avg_score')->toArray());

        // Detect statistical outliers (beyond 2 standard deviations)
        foreach ($schoolPerformances as $performance) {
            $zScore = abs(($performance['avg_score'] - $overallAvg) / $overallStdDev);
            
            if ($zScore > 2) {
                $anomalies[] = [
                    'type' => 'statistical_outlier',
                    'severity' => $zScore > 3 ? 'critical' : 'high',
                    'school' => $performance['school'],
                    'description' => $performance['avg_score'] > $overallAvg 
                        ? 'Exceptionally high performance detected'
                        : 'Significantly low performance detected',
                    'details' => [
                        'avg_score' => round($performance['avg_score'], 1),
                        'system_avg' => round($overallAvg, 1),
                        'z_score' => round($zScore, 2),
                        'std_deviation' => round($performance['std_deviation'], 2),
                    ],
                    'investigation_needed' => $performance['avg_score'] < $overallAvg,
                ];
            }
        }

        // Sudden performance drops
        foreach ($schoolPerformances as $performance) {
            if (count($performance['recent_scores']) >= 2) {
                $scores = $performance['recent_scores'];
                $recentDrop = $scores[0] - $scores[1]; // Latest - Previous
                
                if ($recentDrop < -15) { // Drop of more than 15 points
                    $anomalies[] = [
                        'type' => 'sudden_drop',
                        'severity' => $recentDrop < -25 ? 'critical' : 'high',
                        'school' => $performance['school'],
                        'description' => 'Significant performance drop detected in recent assessment',
                        'details' => [
                            'current_score' => $scores[0],
                            'previous_score' => $scores[1],
                            'drop_amount' => abs($recentDrop),
                            'drop_percentage' => round(abs($recentDrop) / $scores[1] * 100, 1),
                        ],
                        'investigation_needed' => true,
                    ];
                }
            }
        }

        return $anomalies;
    }

    protected function detectPatternAnomalies(): array
    {
        $anomalies = [];

        // Unusual assessment patterns
        $assessmentCounts = School::withCount(['assessments' => function($query) {
            $query->whereHas('period', function($periodQuery) {
                $periodQuery->where('end_date', '>=', now()->subMonths(12));
            });
        }])->get();

        $avgAssessmentCount = $assessmentCounts->avg('assessments_count');
        
        // Schools with unusual assessment frequency
        foreach ($assessmentCounts as $school) {
            if ($school->assessments_count == 0) {
                $anomalies[] = [
                    'type' => 'no_assessments',
                    'severity' => 'critical',
                    'school' => $school,
                    'description' => 'No assessments recorded in the past 12 months',
                    'details' => [
                        'expected_assessments' => round($avgAssessmentCount),
                        'actual_assessments' => 0,
                    ],
                    'investigation_needed' => true,
                ];
            } elseif ($school->assessments_count > ($avgAssessmentCount * 2)) {
                $anomalies[] = [
                    'type' => 'excessive_assessments',
                    'severity' => 'medium',
                    'school' => $school,
                    'description' => 'Unusually high number of assessments',
                    'details' => [
                        'expected_assessments' => round($avgAssessmentCount),
                        'actual_assessments' => $school->assessments_count,
                    ],
                    'investigation_needed' => false,
                ];
            }
        }

        // Seasonal pattern violations
        $monthlyAssessments = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = SchoolAssessment::whereHas('period', function($query) use ($month) {
                $query->whereMonth('start_date', $month->month)
                      ->whereYear('start_date', $month->year);
            })->count();
            
            $monthlyAssessments[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
                'month_number' => $month->month,
            ];
        }

        $avgMonthly = collect($monthlyAssessments)->avg('count');
        
        foreach ($monthlyAssessments as $monthData) {
            if ($monthData['count'] > ($avgMonthly * 3)) {
                $anomalies[] = [
                    'type' => 'unusual_monthly_spike',
                    'severity' => 'medium',
                    'description' => "Unusual spike in assessments during {$monthData['month']}",
                    'details' => [
                        'month' => $monthData['month'],
                        'assessments' => $monthData['count'],
                        'monthly_average' => round($avgMonthly, 1),
                    ],
                    'investigation_needed' => false,
                ];
            }
        }

        return $anomalies;
    }

    protected function detectTimelineAnomalies(): array
    {
        $anomalies = [];

        // Assessments taking unusually long
        $assessmentDurations = SchoolAssessment::whereNotNull('completed_at')
            ->selectRaw('
                *,
                TIMESTAMPDIFF(DAY, created_at, completed_at) as duration_days
            ')
            ->with('school')
            ->get();

        if ($assessmentDurations->count() > 0) {
            $avgDuration = $assessmentDurations->avg('duration_days');
            $stdDev = $this->calculateStandardDeviation($assessmentDurations->pluck('duration_days')->toArray());
            
            $threshold = $avgDuration + (2 * $stdDev);
            
            $longAssessments = $assessmentDurations->filter(function($assessment) use ($threshold) {
                return $assessment->duration_days > $threshold;
            });

            foreach ($longAssessments->take(10) as $assessment) {
                $anomalies[] = [
                    'type' => 'extended_duration',
                    'severity' => $assessment->duration_days > ($avgDuration + 3 * $stdDev) ? 'high' : 'medium',
                    'school' => $assessment->school,
                    'description' => 'Assessment took unusually long to complete',
                    'details' => [
                        'duration_days' => $assessment->duration_days,
                        'average_duration' => round($avgDuration, 1),
                        'started_at' => $assessment->created_at->format('Y-m-d'),
                        'completed_at' => $assessment->completed_at->format('Y-m-d'),
                    ],
                    'investigation_needed' => true,
                ];
            }
        }

        // Pending assessments beyond normal timeframe
        $currentPeriod = AssessmentPeriod::where('is_active', true)->first();
        if ($currentPeriod) {
            $pendingAssessments = SchoolAssessment::where('assessment_period_id', $currentPeriod->id)
                ->whereNull('completed_at')
                ->where('created_at', '<', now()->subDays(30))
                ->with('school')
                ->get();

            foreach ($pendingAssessments->take(5) as $assessment) {
                $daysPending = now()->diffInDays($assessment->created_at);
                
                $anomalies[] = [
                    'type' => 'overdue_assessment',
                    'severity' => $daysPending > 60 ? 'critical' : 'high',
                    'school' => $assessment->school,
                    'description' => 'Assessment overdue for completion',
                    'details' => [
                        'days_pending' => $daysPending,
                        'created_at' => $assessment->created_at->format('Y-m-d'),
                        'period_name' => $currentPeriod->name,
                    ],
                    'investigation_needed' => true,
                ];
            }
        }

        return $anomalies;
    }

    protected function detectQualityAnomalies(): array
    {
        $anomalies = [];

        // Assessments with all perfect or all minimum scores
        $suspiciousScores = SchoolAssessment::with(['school', 'scores.indicator'])
            ->get()
            ->filter(function($assessment) {
                $scores = $assessment->scores;
                if ($scores->count() == 0) return false;
                
                $allPerfect = $scores->every(function($score) {
                    return $score->score >= 95;
                });
                
                $allMinimum = $scores->every(function($score) {
                    return $score->score <= 10;
                });
                
                return $allPerfect || $allMinimum;
            });

        foreach ($suspiciousScores->take(10) as $assessment) {
            $scores = $assessment->scores;
            $avgScore = $scores->avg('score');
            
            $anomalies[] = [
                'type' => 'suspicious_scoring',
                'severity' => 'high',
                'school' => $assessment->school,
                'description' => $avgScore >= 95 
                    ? 'All indicators scored at maximum level' 
                    : 'All indicators scored at minimum level',
                'details' => [
                    'average_score' => round($avgScore, 1),
                    'indicator_count' => $scores->count(),
                    'score_range' => $scores->min('score') . ' - ' . $scores->max('score'),
                    'assessment_date' => $assessment->created_at->format('Y-m-d'),
                ],
                'investigation_needed' => true,
            ];
        }

        // Inconsistent scoring patterns by assessor
        $assessorPatterns = Assessor::with(['assessments.scores'])
            ->get()
            ->map(function($assessor) {
                $assessments = $assessor->assessments;
                if ($assessments->count() < 3) return null;
                
                $avgScores = $assessments->map(function($assessment) {
                    return $assessment->scores->avg('score');
                })->filter();
                
                return [
                    'assessor' => $assessor,
                    'assessment_count' => $assessments->count(),
                    'score_variance' => $this->calculateStandardDeviation($avgScores->toArray()),
                    'avg_score' => $avgScores->avg(),
                ];
            })
            ->filter()
            ->filter(function($data) {
                return $data['score_variance'] < 5 || $data['score_variance'] > 25;
            });

        foreach ($assessorPatterns->take(5) as $pattern) {
            $anomalies[] = [
                'type' => 'assessor_consistency',
                'severity' => 'medium',
                'description' => $pattern['score_variance'] < 5 
                    ? 'Assessor shows unusually consistent scoring patterns'
                    : 'Assessor shows highly inconsistent scoring patterns',
                'details' => [
                    'assessor_name' => $pattern['assessor']->name,
                    'assessment_count' => $pattern['assessment_count'],
                    'score_variance' => round($pattern['score_variance'], 2),
                    'average_score' => round($pattern['avg_score'], 1),
                ],
                'investigation_needed' => true,
            ];
        }

        return $anomalies;
    }

    protected function detectBehavioralAnomalies(): array
    {
        $anomalies = [];

        // Login pattern anomalies (if we had login data)
        // This is a placeholder for future implementation
        
        // Assessment submission patterns
        $submissionHours = SchoolAssessment::whereNotNull('completed_at')
            ->selectRaw('HOUR(completed_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Check for unusual submission times (late night/early morning)
        $unusualHours = [];
        foreach ([0, 1, 2, 3, 4, 5, 22, 23] as $hour) {
            if (isset($submissionHours[$hour]) && $submissionHours[$hour] > 5) {
                $unusualHours[] = [
                    'hour' => $hour,
                    'count' => $submissionHours[$hour],
                ];
            }
        }

        if (!empty($unusualHours)) {
            $anomalies[] = [
                'type' => 'unusual_work_hours',
                'severity' => 'low',
                'description' => 'Assessments being completed during unusual hours',
                'details' => [
                    'unusual_hours' => $unusualHours,
                    'total_unusual_submissions' => array_sum(array_column($unusualHours, 'count')),
                ],
                'investigation_needed' => false,
            ];
        }

        return $anomalies;
    }

    protected function detectSystemAnomalies(): array
    {
        $anomalies = [];

        // Data integrity checks
        $orphanedAssessments = SchoolAssessment::whereDoesntHave('school')->count();
        if ($orphanedAssessments > 0) {
            $anomalies[] = [
                'type' => 'data_integrity',
                'severity' => 'high',
                'description' => 'Assessments found without associated schools',
                'details' => [
                    'orphaned_count' => $orphanedAssessments,
                ],
                'investigation_needed' => true,
            ];
        }

        $incompleteAssessments = SchoolAssessment::whereDoesntHave('scores')->count();
        if ($incompleteAssessments > 0) {
            $anomalies[] = [
                'type' => 'incomplete_data',
                'severity' => 'medium',
                'description' => 'Assessments found without any scores',
                'details' => [
                    'incomplete_count' => $incompleteAssessments,
                ],
                'investigation_needed' => true,
            ];
        }

        // Performance anomalies
        $recentResponseTimes = $this->getSystemPerformanceMetrics();
        if ($recentResponseTimes['avg_response_time'] > 2000) { // 2 seconds
            $anomalies[] = [
                'type' => 'performance_degradation',
                'severity' => 'medium',
                'description' => 'System response times are slower than expected',
                'details' => $recentResponseTimes,
                'investigation_needed' => true,
            ];
        }

        return $anomalies;
    }

    protected function generateAnomalySummary(): array
    {
        $allAnomalies = array_merge(
            $this->detectPerformanceAnomalies(),
            $this->detectPatternAnomalies(),
            $this->detectTimelineAnomalies(),
            $this->detectQualityAnomalies(),
            $this->detectBehavioralAnomalies(),
            $this->detectSystemAnomalies()
        );

        $severityCounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        $typeCounts = [];
        $investigationNeeded = 0;

        foreach ($allAnomalies as $anomaly) {
            $severity = $anomaly['severity'] ?? 'low';
            $severityCounts[$severity]++;
            
            $type = $anomaly['type'] ?? 'unknown';
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
            
            if ($anomaly['investigation_needed'] ?? false) {
                $investigationNeeded++;
            }
        }

        return [
            'total_anomalies' => count($allAnomalies),
            'severity_breakdown' => $severityCounts,
            'type_breakdown' => $typeCounts,
            'investigation_needed' => $investigationNeeded,
            'risk_level' => $this->calculateOverallRiskLevel($severityCounts),
            'recent_anomalies' => array_slice($allAnomalies, 0, 5),
        ];
    }

    // Helper methods
    protected function calculateStandardDeviation(array $values): float
    {
        if (count($values) <= 1) return 0;
        
        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        $variance = array_sum($squaredDifferences) / count($values);
        return sqrt($variance);
    }

    protected function getSystemPerformanceMetrics(): array
    {
        // Simulated performance metrics
        return [
            'avg_response_time' => 1200, // milliseconds
            'error_rate' => 0.5, // percentage
            'cpu_usage' => 65, // percentage
            'memory_usage' => 78, // percentage
        ];
    }

    protected function calculateOverallRiskLevel(array $severityCounts): string
    {
        if ($severityCounts['critical'] > 0) return 'critical';
        if ($severityCounts['high'] > 2) return 'high';
        if ($severityCounts['medium'] > 5) return 'medium';
        return 'low';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'All Anomalies',
            'critical' => 'Critical Only',
            'performance' => 'Performance',
            'quality' => 'Quality Issues',
            'timeline' => 'Timeline Issues',
        ];
    }
}
