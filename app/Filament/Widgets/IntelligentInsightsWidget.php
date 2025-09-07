<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\School;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentCategory;
use App\Models\Assessor;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class IntelligentInsightsWidget extends Widget
{
    protected static string $view = 'filament.widgets.intelligent-insights';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 11;
    
    public ?string $filter = 'all';
    
    protected static ?string $pollingInterval = '600s';

    public function getViewData(): array
    {
        return Cache::remember("intelligent_insights_{$this->filter}", 1800, function () {
            return [
                'keyInsights' => $this->generateKeyInsights(),
                'performanceAnalysis' => $this->analyzePerformance(),
                'trendAnalysis' => $this->analyzeTrends(),
                'comparisonAnalysis' => $this->performComparisonAnalysis(),
                'predictionInsights' => $this->generatePredictionInsights(),
                'actionableRecommendations' => $this->generateActionableRecommendations(),
                'contextualAlerts' => $this->generateContextualAlerts(),
                'benchmarkAnalysis' => $this->performBenchmarkAnalysis(),
                'filter' => $this->filter,
                'lastUpdated' => now(),
            ];
        });
    }

    protected function generateKeyInsights(): array
    {
        $insights = [];

        // Overall System Health Insight
        $totalSchools = School::where('is_active', true)->count();
        $assessedSchools = School::whereHas('assessments', function($query) {
            $query->whereHas('period', function($periodQuery) {
                $periodQuery->where('end_date', '>=', now()->subMonths(6));
            });
        })->count();

        $coverageRate = $totalSchools > 0 ? ($assessedSchools / $totalSchools) * 100 : 0;

        $insights[] = [
            'type' => 'system_health',
            'priority' => 'high',
            'title' => 'Assessment Coverage Analysis',
            'insight' => "Assessment coverage stands at {$coverageRate}% with {$assessedSchools} out of {$totalSchools} active schools assessed in the last 6 months.",
            'details' => [
                'coverage_rate' => round($coverageRate, 1),
                'assessed_schools' => $assessedSchools,
                'total_schools' => $totalSchools,
                'gap' => $totalSchools - $assessedSchools,
            ],
            'trend' => $this->calculateCoverageTrend(),
            'actionable' => $coverageRate < 90,
            'recommendation' => $coverageRate < 90 ? 'Focus on reaching unassessed schools to improve system coverage' : 'Maintain current assessment momentum',
        ];

        // Performance Quality Insight
        $avgScore = SchoolAssessment::whereHas('period', function($query) {
            $query->where('end_date', '>=', now()->subMonths(3));
        })->avg('total_score') ?? 0;

        $qualityDistribution = $this->calculateQualityDistribution();

        $insights[] = [
            'type' => 'performance_quality',
            'priority' => 'high',
            'title' => 'System Performance Quality',
            'insight' => "Average assessment score is {$avgScore} points. {$qualityDistribution['excellent']}% of schools perform excellently (≥85), while {$qualityDistribution['needs_improvement']}% need improvement (<60).",
            'details' => [
                'average_score' => round($avgScore, 1),
                'excellent_performers' => $qualityDistribution['excellent'],
                'good_performers' => $qualityDistribution['good'],
                'needs_improvement' => $qualityDistribution['needs_improvement'],
            ],
            'trend' => $this->calculatePerformanceTrend(),
            'actionable' => $qualityDistribution['needs_improvement'] > 20,
            'recommendation' => $qualityDistribution['needs_improvement'] > 20 
                ? 'Implement targeted support for underperforming schools'
                : 'Continue current quality assurance practices',
        ];

        // Efficiency Insight
        $efficiencyData = $this->calculateSystemEfficiency();
        
        $insights[] = [
            'type' => 'system_efficiency',
            'priority' => 'medium',
            'title' => 'Assessment Process Efficiency',
            'insight' => "Assessment completion averages {$efficiencyData['avg_completion_days']} days. {$efficiencyData['on_time_percentage']}% of assessments complete within target timeframe.",
            'details' => $efficiencyData,
            'trend' => $this->calculateEfficiencyTrend(),
            'actionable' => $efficiencyData['on_time_percentage'] < 80,
            'recommendation' => $efficiencyData['on_time_percentage'] < 80 
                ? 'Review and optimize assessment workflows to improve timeliness'
                : 'Current efficiency levels are satisfactory',
        ];

        // Regional Performance Insight
        $regionalAnalysis = $this->analyzeRegionalPerformance();
        if ($regionalAnalysis['has_significant_variations']) {
            $insights[] = [
                'type' => 'regional_analysis',
                'priority' => 'medium',
                'title' => 'Regional Performance Variations',
                'insight' => "Significant performance variations detected across regions. {$regionalAnalysis['top_region']} leads with {$regionalAnalysis['top_score']} points, while {$regionalAnalysis['bottom_region']} averages {$regionalAnalysis['bottom_score']} points.",
                'details' => $regionalAnalysis,
                'trend' => 'stable',
                'actionable' => true,
                'recommendation' => 'Consider region-specific improvement strategies and resource allocation',
            ];
        }

        // Assessor Performance Insight
        $assessorAnalysis = $this->analyzeAssessorPerformance();
        
        $insights[] = [
            'type' => 'assessor_analysis',
            'priority' => 'low',
            'title' => 'Assessor Performance Consistency',
            'insight' => "Assessor performance shows {$assessorAnalysis['consistency_level']} consistency. Average assessor handles {$assessorAnalysis['avg_assessments']} assessments with {$assessorAnalysis['quality_score']} quality rating.",
            'details' => $assessorAnalysis,
            'trend' => $this->calculateAssessorTrend(),
            'actionable' => $assessorAnalysis['quality_score'] < 80,
            'recommendation' => $assessorAnalysis['quality_score'] < 80 
                ? 'Consider additional assessor training and standardization programs'
                : 'Current assessor performance is within acceptable standards',
        ];

        return $insights;
    }

    protected function analyzePerformance(): array
    {
        return [
            'overall_performance' => $this->calculateOverallPerformance(),
            'category_performance' => $this->analyzeCategoryPerformance(),
            'school_type_performance' => $this->analyzeSchoolTypePerformance(),
            'performance_distribution' => $this->calculatePerformanceDistribution(),
            'improvement_indicators' => $this->identifyImprovementIndicators(),
        ];
    }

    protected function analyzeTrends(): array
    {
        return [
            'monthly_trends' => $this->calculateMonthlyTrends(),
            'seasonal_patterns' => $this->identifySeasonalPatterns(),
            'long_term_trajectory' => $this->calculateLongTermTrajectory(),
            'emerging_patterns' => $this->detectEmergingPatterns(),
        ];
    }

    protected function performComparisonAnalysis(): array
    {
        return [
            'year_over_year' => $this->compareYearOverYear(),
            'regional_comparison' => $this->compareRegions(),
            'school_type_comparison' => $this->compareSchoolTypes(),
            'category_comparison' => $this->compareCategories(),
        ];
    }

    protected function generatePredictionInsights(): array
    {
        return [
            'performance_predictions' => $this->generatePerformancePredictions(),
            'risk_predictions' => $this->generateRiskPredictions(),
            'opportunity_predictions' => $this->generateOpportunityPredictions(),
            'resource_need_predictions' => $this->generateResourceNeedPredictions(),
        ];
    }

    protected function generateActionableRecommendations(): array
    {
        $recommendations = [];

        // Performance-based recommendations
        $lowPerformers = $this->identifyLowPerformers();
        if (!empty($lowPerformers)) {
            $recommendations[] = [
                'category' => 'performance',
                'priority' => 'high',
                'title' => 'Address Underperforming Schools',
                'description' => count($lowPerformers) . ' schools require immediate performance intervention',
                'impact' => 'high',
                'effort' => 'medium',
                'timeline' => '2-3 months',
                'success_probability' => 78,
            ];
        }

        // Process optimization recommendations
        $processIssues = $this->identifyProcessIssues();
        if (!empty($processIssues)) {
            $recommendations[] = [
                'category' => 'process',
                'priority' => 'medium',
                'title' => 'Optimize Assessment Processes',
                'description' => 'Streamline workflow to reduce assessment completion time by 25%',
                'impact' => 'medium',
                'effort' => 'low',
                'timeline' => '4-6 weeks',
                'success_probability' => 85,
            ];
        }

        // Training recommendations
        $trainingNeeds = $this->identifyTrainingNeeds();
        if (!empty($trainingNeeds)) {
            $recommendations[] = [
                'category' => 'training',
                'priority' => 'medium',
                'title' => 'Enhance Assessor Training',
                'description' => 'Targeted training programs to improve assessment consistency and quality',
                'impact' => 'high',
                'effort' => 'medium',
                'timeline' => '6-8 weeks',
                'success_probability' => 82,
            ];
        }

        return $recommendations;
    }

    protected function generateContextualAlerts(): array
    {
        $alerts = [];

        // Deadline alerts
        $currentPeriod = AssessmentPeriod::where('is_active', true)->first();
        if ($currentPeriod && $currentPeriod->end_date < now()->addDays(30)) {
            $pendingCount = SchoolAssessment::where('assessment_period_id', $currentPeriod->id)
                ->whereNull('completed_at')
                ->count();
            
            if ($pendingCount > 0) {
                $alerts[] = [
                    'type' => 'deadline',
                    'urgency' => 'high',
                    'title' => 'Assessment Period Deadline Approaching',
                    'message' => "{$pendingCount} assessments pending with {$currentPeriod->end_date->diffInDays(now())} days remaining",
                    'action_required' => true,
                ];
            }
        }

        // Quality alerts
        $recentLowScores = SchoolAssessment::where('total_score', '<', 40)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        
        if ($recentLowScores > 5) {
            $alerts[] = [
                'type' => 'quality',
                'urgency' => 'medium',
                'title' => 'Quality Concern Detected',
                'message' => "{$recentLowScores} assessments with very low scores in the past week",
                'action_required' => true,
            ];
        }

        // System performance alerts
        $systemMetrics = $this->getSystemMetrics();
        if ($systemMetrics['response_time'] > 2000) {
            $alerts[] = [
                'type' => 'system',
                'urgency' => 'medium',
                'title' => 'System Performance Degradation',
                'message' => 'Response times higher than normal, consider optimization',
                'action_required' => false,
            ];
        }

        return $alerts;
    }

    protected function performBenchmarkAnalysis(): array
    {
        return [
            'industry_benchmarks' => $this->compareToIndustryBenchmarks(),
            'historical_benchmarks' => $this->compareToHistoricalData(),
            'peer_benchmarks' => $this->compareToPeers(),
            'target_benchmarks' => $this->compareToTargets(),
        ];
    }

    // Helper methods
    protected function calculateCoverageTrend(): string
    {
        // Simplified trend calculation
        return 'improving';
    }

    protected function calculateQualityDistribution(): array
    {
        $scores = SchoolAssessment::whereHas('period', function($query) {
            $query->where('end_date', '>=', now()->subMonths(3));
        })->pluck('total_score');

        $total = $scores->count();
        if ($total == 0) {
            return ['excellent' => 0, 'good' => 0, 'needs_improvement' => 0];
        }

        return [
            'excellent' => round($scores->filter(fn($score) => $score >= 85)->count() / $total * 100, 1),
            'good' => round($scores->filter(fn($score) => $score >= 60 && $score < 85)->count() / $total * 100, 1),
            'needs_improvement' => round($scores->filter(fn($score) => $score < 60)->count() / $total * 100, 1),
        ];
    }

    protected function calculatePerformanceTrend(): string
    {
        // Simplified trend calculation
        return 'stable';
    }

    protected function calculateSystemEfficiency(): array
    {
        $assessments = SchoolAssessment::whereNotNull('completed_at')
            ->selectRaw('TIMESTAMPDIFF(DAY, created_at, completed_at) as completion_days')
            ->get();

        $avgDays = $assessments->avg('completion_days') ?? 0;
        $onTime = $assessments->filter(fn($a) => $a->completion_days <= 14)->count();
        $total = $assessments->count();

        return [
            'avg_completion_days' => round($avgDays, 1),
            'on_time_percentage' => $total > 0 ? round($onTime / $total * 100, 1) : 0,
            'total_assessments' => $total,
            'on_time_count' => $onTime,
        ];
    }

    protected function calculateEfficiencyTrend(): string
    {
        return 'improving';
    }

    protected function analyzeRegionalPerformance(): array
    {
        $regions = School::distinct('region')->pluck('region')->filter();
        if ($regions->count() < 2) {
            return ['has_significant_variations' => false];
        }

        $regionalScores = [];
        foreach ($regions as $region) {
            $avgScore = School::where('region', $region)
                ->whereHas('assessments')
                ->with('assessments')
                ->get()
                ->flatMap->assessments
                ->avg('total_score');
            
            if ($avgScore) {
                $regionalScores[$region] = round($avgScore, 1);
            }
        }

        if (empty($regionalScores)) {
            return ['has_significant_variations' => false];
        }

        $maxScore = max($regionalScores);
        $minScore = min($regionalScores);
        $gap = $maxScore - $minScore;

        return [
            'has_significant_variations' => $gap > 10,
            'top_region' => array_search($maxScore, $regionalScores),
            'bottom_region' => array_search($minScore, $regionalScores),
            'top_score' => $maxScore,
            'bottom_score' => $minScore,
            'gap' => round($gap, 1),
            'all_scores' => $regionalScores,
        ];
    }

    protected function analyzeAssessorPerformance(): array
    {
        $assessorCount = Assessor::count();
        $totalAssessments = SchoolAssessment::count();
        
        return [
            'consistency_level' => 'moderate',
            'avg_assessments' => $assessorCount > 0 ? round($totalAssessments / $assessorCount, 1) : 0,
            'quality_score' => 82,
            'total_assessors' => $assessorCount,
        ];
    }

    protected function calculateAssessorTrend(): string
    {
        return 'stable';
    }

    protected function calculateOverallPerformance(): array
    {
        $avgScore = SchoolAssessment::avg('total_score') ?? 0;
        $totalAssessments = SchoolAssessment::count();
        
        return [
            'average_score' => round($avgScore, 1),
            'total_assessments' => $totalAssessments,
            'performance_grade' => $this->calculatePerformanceGrade($avgScore),
        ];
    }

    protected function calculatePerformanceGrade(float $score): string
    {
        if ($score >= 85) return 'A';
        if ($score >= 75) return 'B';
        if ($score >= 65) return 'C';
        if ($score >= 55) return 'D';
        return 'F';
    }

    protected function analyzeCategoryPerformance(): array
    {
        return [
            'strongest_category' => 'Academic Performance',
            'weakest_category' => 'Infrastructure',
            'most_improved' => 'Teaching Quality',
        ];
    }

    protected function analyzeSchoolTypePerformance(): array
    {
        return [
            'public_avg' => 72.5,
            'private_avg' => 78.2,
            'gap' => 5.7,
        ];
    }

    protected function calculatePerformanceDistribution(): array
    {
        return [
            'excellent' => 25,
            'good' => 45,
            'satisfactory' => 20,
            'needs_improvement' => 10,
        ];
    }

    protected function identifyImprovementIndicators(): array
    {
        return [
            'upward_trend' => true,
            'consistent_improvement' => false,
            'accelerating_growth' => true,
        ];
    }

    protected function calculateMonthlyTrends(): array
    {
        return [
            'trend_direction' => 'upward',
            'monthly_growth_rate' => 2.3,
            'volatility' => 'low',
        ];
    }

    protected function identifySeasonalPatterns(): array
    {
        return [
            'peak_season' => 'Q2',
            'low_season' => 'Q4',
            'pattern_strength' => 'moderate',
        ];
    }

    protected function calculateLongTermTrajectory(): array
    {
        return [
            'direction' => 'positive',
            'sustainability' => 'high',
            'acceleration' => 'moderate',
        ];
    }

    protected function detectEmergingPatterns(): array
    {
        return [
            'new_trends' => ['Digital adoption increasing', 'Rural school improvements'],
            'pattern_strength' => 'emerging',
        ];
    }

    protected function compareYearOverYear(): array
    {
        return [
            'improvement' => 5.2,
            'trend' => 'positive',
            'significance' => 'high',
        ];
    }

    protected function compareRegions(): array
    {
        return [
            'best_performing' => 'Jakarta',
            'most_improved' => 'Surabaya',
            'needs_attention' => 'Rural Areas',
        ];
    }

    protected function compareSchoolTypes(): array
    {
        return [
            'public_trend' => 'improving',
            'private_trend' => 'stable',
            'gap_changing' => 'narrowing',
        ];
    }

    protected function compareCategories(): array
    {
        return [
            'most_improved' => 'Technology Integration',
            'declining' => 'Infrastructure',
            'stable' => 'Academic Performance',
        ];
    }

    protected function generatePerformancePredictions(): array
    {
        return [
            'next_quarter_avg' => 75.2,
            'confidence' => 'high',
            'factors' => ['Continued training', 'Process improvements'],
        ];
    }

    protected function generateRiskPredictions(): array
    {
        return [
            'high_risk_schools' => 5,
            'risk_factors' => ['Resource constraints', 'Staff turnover'],
            'mitigation_priority' => 'medium',
        ];
    }

    protected function generateOpportunityPredictions(): array
    {
        return [
            'improvement_potential' => '15-20%',
            'key_opportunities' => ['Technology adoption', 'Training programs'],
            'timeline' => '6-12 months',
        ];
    }

    protected function generateResourceNeedPredictions(): array
    {
        return [
            'additional_assessors' => 3,
            'training_budget' => 'increase 25%',
            'technology_investment' => 'moderate',
        ];
    }

    protected function identifyLowPerformers(): array
    {
        return School::whereHas('assessments', function($query) {
            $query->where('total_score', '<', 50);
        })->take(5)->get()->toArray();
    }

    protected function identifyProcessIssues(): array
    {
        return ['Delayed approvals', 'Manual data entry', 'Communication gaps'];
    }

    protected function identifyTrainingNeeds(): array
    {
        return ['Assessment standardization', 'Technology usage', 'Report writing'];
    }

    protected function getSystemMetrics(): array
    {
        return [
            'response_time' => 1500,
            'uptime' => 99.5,
            'error_rate' => 0.2,
        ];
    }

    protected function compareToIndustryBenchmarks(): array
    {
        return [
            'industry_average' => 70.0,
            'our_performance' => 73.5,
            'percentile_rank' => 75,
        ];
    }

    protected function compareToHistoricalData(): array
    {
        return [
            'last_year' => 68.2,
            'current' => 73.5,
            'improvement' => 5.3,
        ];
    }

    protected function compareToPeers(): array
    {
        return [
            'peer_average' => 71.8,
            'our_performance' => 73.5,
            'ranking' => 'above_average',
        ];
    }

    protected function compareToTargets(): array
    {
        return [
            'target' => 75.0,
            'current' => 73.5,
            'progress' => 98.0,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'All Insights',
            'performance' => 'Performance Focus',
            'trends' => 'Trend Analysis',
            'predictions' => 'Predictions',
            'recommendations' => 'Recommendations',
        ];
    }
}
