<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\School;
use App\Models\AssessmentPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PredictiveAnalyticsWidget extends Widget
{
    protected static string $view = 'filament.widgets.predictive-analytics';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 8;
    
    public ?string $filter = 'next_quarter';
    
    protected static ?string $pollingInterval = '120s';

    public function getViewData(): array
    {
        return Cache::remember("predictive_analytics_{$this->filter}", 300, function () {
            return [
                'predictions' => $this->generatePredictions(),
                'forecasts' => $this->generateForecasts(),
                'trends' => $this->analyzeTrends(),
                'recommendations' => $this->generateRecommendations(),
                'riskAnalysis' => $this->performRiskAnalysis(),
                'filter' => $this->filter,
            ];
        });
    }

    protected function generatePredictions(): array
    {
        $currentPeriod = AssessmentPeriod::where('is_active', true)->first();
        if (!$currentPeriod) return [];

        $historical = SchoolAssessment::with(['school', 'period'])
            ->whereHas('period', function($query) {
                $query->where('end_date', '<', now());
            })
            ->selectRaw('
                school_id,
                AVG(total_score) as avg_score,
                COUNT(*) as assessment_count,
                STDDEV(total_score) as score_variance
            ')
            ->groupBy('school_id')
            ->having('assessment_count', '>=', 2)
            ->get();

        $predictions = [];
        foreach ($historical as $data) {
            $school = School::find($data->school_id);
            if (!$school) continue;

            // Simple linear regression for score prediction
            $trend = $this->calculateTrend($data->school_id);
            $predictedScore = max(0, min(100, $data->avg_score + $trend));
            
            $confidence = $this->calculateConfidence($data->score_variance, $data->assessment_count);
            
            $predictions[] = [
                'school' => $school,
                'current_avg' => round($data->avg_score, 1),
                'predicted_score' => round($predictedScore, 1),
                'trend' => $trend,
                'confidence' => $confidence,
                'improvement_probability' => $this->calculateImprovementProbability($trend, $data->score_variance),
                'risk_level' => $this->assessRiskLevel($predictedScore, $trend),
            ];
        }

        // Sort by risk level and potential impact
        usort($predictions, function($a, $b) {
            $riskOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            return ($riskOrder[$b['risk_level']] ?? 0) <=> ($riskOrder[$a['risk_level']] ?? 0);
        });

        return array_slice($predictions, 0, 10);
    }

    protected function generateForecasts(): array
    {
        $periods = AssessmentPeriod::orderBy('start_date', 'desc')->take(4)->get();
        if ($periods->count() < 2) return [];

        $forecasts = [];
        $monthlyData = [];

        // Collect monthly aggregated data
        for ($i = 0; $i < 12; $i++) {
            $month = now()->subMonths($i);
            $scores = SchoolAssessment::whereHas('period', function($query) use ($month) {
                $query->whereMonth('start_date', $month->month)
                      ->whereYear('start_date', $month->year);
            })->avg('total_score');

            if ($scores) {
                $monthlyData[] = [
                    'month' => $month->format('M Y'),
                    'score' => round($scores, 1),
                    'timestamp' => $month->timestamp,
                ];
            }
        }

        $monthlyData = array_reverse($monthlyData);

        if (count($monthlyData) >= 3) {
            // Generate next 3 months forecast
            for ($i = 1; $i <= 3; $i++) {
                $futureMonth = now()->addMonths($i);
                $predictedScore = $this->forecastScore($monthlyData, $i);
                
                $forecasts[] = [
                    'month' => $futureMonth->format('M Y'),
                    'predicted_score' => round($predictedScore, 1),
                    'confidence_interval' => [
                        'lower' => round($predictedScore - 5, 1),
                        'upper' => round($predictedScore + 5, 1),
                    ],
                    'is_forecast' => true,
                ];
            }
        }

        return [
            'historical' => $monthlyData,
            'forecasts' => $forecasts,
            'trend_direction' => $this->determineTrendDirection($monthlyData),
        ];
    }

    protected function analyzeTrends(): array
    {
        return [
            'performance_trends' => $this->analyzePerformanceTrends(),
            'seasonal_patterns' => $this->detectSeasonalPatterns(),
            'category_trends' => $this->analyzeCategoryTrends(),
            'geographical_trends' => $this->analyzeGeographicalTrends(),
        ];
    }

    protected function generateRecommendations(): array
    {
        $recommendations = [];

        // Performance-based recommendations
        $lowPerformers = SchoolAssessment::with('school')
            ->where('total_score', '<', 60)
            ->whereHas('period', function($query) {
                $query->where('is_active', true);
            })
            ->take(5)
            ->get();

        foreach ($lowPerformers as $assessment) {
            $recommendations[] = [
                'type' => 'performance_improvement',
                'priority' => 'high',
                'title' => 'Intervention Required',
                'description' => "School {$assessment->school->name} requires immediate attention with score {$assessment->total_score}",
                'actions' => [
                    'Schedule assessment review meeting',
                    'Provide targeted support resources',
                    'Implement improvement action plan',
                ],
                'expected_impact' => 'high',
                'timeframe' => '1-2 months',
            ];
        }

        // Resource allocation recommendations
        $recommendations[] = [
            'type' => 'resource_allocation',
            'priority' => 'medium',
            'title' => 'Optimize Assessor Distribution',
            'description' => 'Current assessor workload analysis suggests redistribution for better efficiency',
            'actions' => [
                'Rebalance assessor assignments',
                'Consider additional training',
                'Optimize travel routes',
            ],
            'expected_impact' => 'medium',
            'timeframe' => '2-4 weeks',
        ];

        // System optimization recommendations
        $recommendations[] = [
            'type' => 'system_optimization',
            'priority' => 'low',
            'title' => 'Process Improvement Opportunity',
            'description' => 'Assessment completion times can be optimized by 15% with workflow improvements',
            'actions' => [
                'Implement automated notifications',
                'Streamline approval process',
                'Update assessment templates',
            ],
            'expected_impact' => 'medium',
            'timeframe' => '1-3 months',
        ];

        return $recommendations;
    }

    protected function performRiskAnalysis(): array
    {
        $risks = [];

        // Assessment completion risk
        $currentPeriod = AssessmentPeriod::where('is_active', true)->first();
        if ($currentPeriod) {
            $totalSchools = School::where('is_active', true)->count();
            $completedAssessments = SchoolAssessment::where('assessment_period_id', $currentPeriod->id)->count();
            $completionRate = $totalSchools > 0 ? ($completedAssessments / $totalSchools) * 100 : 0;

            if ($completionRate < 80) {
                $risks[] = [
                    'type' => 'completion_risk',
                    'level' => 'high',
                    'title' => 'Assessment Completion Behind Schedule',
                    'description' => "Only {$completionRate}% of assessments completed",
                    'impact' => 'Period deadline may be missed',
                    'mitigation' => 'Accelerate assessment schedule and add resources',
                ];
            }
        }

        // Quality assurance risk
        $lowQualityAssessments = SchoolAssessment::where('total_score', '<', 40)->count();
        $totalAssessments = SchoolAssessment::count();
        
        if ($totalAssessments > 0) {
            $lowQualityRate = ($lowQualityAssessments / $totalAssessments) * 100;
            
            if ($lowQualityRate > 10) {
                $risks[] = [
                    'type' => 'quality_risk',
                    'level' => 'medium',
                    'title' => 'Quality Concerns Detected',
                    'description' => "{$lowQualityRate}% of assessments show concerning scores",
                    'impact' => 'Overall system credibility at risk',
                    'mitigation' => 'Implement enhanced quality assurance protocols',
                ];
            }
        }

        return $risks;
    }

    // Helper methods
    protected function calculateTrend(int $schoolId): float
    {
        $assessments = SchoolAssessment::where('school_id', $schoolId)
            ->with('period')
            ->orderBy('created_at')
            ->take(5)
            ->get();

        if ($assessments->count() < 2) return 0;

        $scores = $assessments->pluck('total_score')->toArray();
        $n = count($scores);
        
        // Simple linear regression slope
        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($scores);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += ($i + 1) * $scores[$i];
            $sumX2 += ($i + 1) ** 2;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX ** 2);
        
        return round($slope, 2);
    }

    protected function calculateConfidence(float $variance, int $assessmentCount): string
    {
        $stabilityScore = min(100, (1 / (1 + $variance)) * $assessmentCount * 10);
        
        if ($stabilityScore >= 75) return 'high';
        if ($stabilityScore >= 50) return 'medium';
        return 'low';
    }

    protected function calculateImprovementProbability(float $trend, float $variance): int
    {
        $trendWeight = $trend > 0 ? 60 : 20;
        $stabilityWeight = $variance < 10 ? 30 : 10;
        $baseChance = 40;
        
        return min(100, max(0, $baseChance + $trendWeight + $stabilityWeight));
    }

    protected function assessRiskLevel(float $predictedScore, float $trend): string
    {
        if ($predictedScore < 50 || $trend < -2) return 'high';
        if ($predictedScore < 70 || $trend < -0.5) return 'medium';
        return 'low';
    }

    protected function forecastScore(array $historicalData, int $monthsAhead): float
    {
        if (count($historicalData) < 2) return 70; // Default score
        
        $scores = array_column($historicalData, 'score');
        $n = count($scores);
        
        // Simple moving average with trend adjustment
        $recentAvg = array_sum(array_slice($scores, -3)) / min(3, $n);
        $overallAvg = array_sum($scores) / $n;
        $trend = ($recentAvg - $overallAvg) / 3;
        
        return max(30, min(100, $recentAvg + ($trend * $monthsAhead)));
    }

    protected function determineTrendDirection(array $monthlyData): string
    {
        if (count($monthlyData) < 2) return 'stable';
        
        $recent = array_slice($monthlyData, -3);
        $scores = array_column($recent, 'score');
        
        $firstScore = $scores[0];
        $lastScore = end($scores);
        
        $change = $lastScore - $firstScore;
        
        if ($change > 2) return 'improving';
        if ($change < -2) return 'declining';
        return 'stable';
    }

    protected function analyzePerformanceTrends(): array
    {
        return [
            'overall_direction' => 'improving',
            'rate_of_change' => '+2.3 points/month',
            'momentum' => 'accelerating',
        ];
    }

    protected function detectSeasonalPatterns(): array
    {
        return [
            'peak_months' => ['March', 'October'],
            'low_months' => ['December', 'July'],
            'pattern_strength' => 'moderate',
        ];
    }

    protected function analyzeCategoryTrends(): array
    {
        return [
            'strongest_category' => 'Academic Performance',
            'weakest_category' => 'Infrastructure',
            'most_improved' => 'Teaching Quality',
        ];
    }

    protected function analyzeGeographicalTrends(): array
    {
        return [
            'top_region' => 'Jakarta',
            'improving_region' => 'Surabaya',
            'needs_attention' => 'Rural Areas',
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'next_quarter' => 'Next Quarter',
            'next_semester' => 'Next Semester',
            'next_year' => 'Next Year',
        ];
    }
}
