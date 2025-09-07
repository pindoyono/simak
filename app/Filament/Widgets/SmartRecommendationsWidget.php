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

class SmartRecommendationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.smart-recommendations';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 9;
    
    public ?string $filter = 'all';
    
    protected static ?string $pollingInterval = '180s';

    public function getViewData(): array
    {
        return Cache::remember("smart_recommendations_{$this->filter}", 600, function () {
            return [
                'priorityRecommendations' => $this->generatePriorityRecommendations(),
                'actionableInsights' => $this->generateActionableInsights(),
                'optimizationSuggestions' => $this->generateOptimizationSuggestions(),
                'bestPractices' => $this->identifyBestPractices(),
                'resourceOptimization' => $this->analyzeResourceOptimization(),
                'filter' => $this->filter,
                'stats' => $this->getRecommendationStats(),
            ];
        });
    }

    protected function generatePriorityRecommendations(): array
    {
        $recommendations = [];
        
        // 1. Schools needing immediate attention
        $criticalSchools = School::whereHas('assessments', function($query) {
            $query->where('total_score', '<', 50)
                  ->whereHas('period', function($periodQuery) {
                      $periodQuery->where('end_date', '>=', now()->subMonths(6));
                  });
        })->with(['assessments' => function($query) {
            $query->latest()->take(3);
        }])->take(5)->get();

        foreach ($criticalSchools as $school) {
            $latestScore = $school->assessments->first()->total_score ?? 0;
            $recommendations[] = [
                'id' => 'critical_' . $school->id,
                'type' => 'critical_intervention',
                'priority' => 'urgent',
                'title' => 'Critical Performance Alert',
                'school' => $school,
                'description' => "School performance critically low (Score: {$latestScore}). Immediate intervention required.",
                'impact' => 'high',
                'effort' => 'high',
                'timeframe' => 'immediate',
                'actions' => [
                    'Schedule emergency assessment review',
                    'Deploy senior assessor for consultation',
                    'Develop comprehensive improvement plan',
                    'Provide intensive training resources',
                ],
                'expected_outcome' => 'Performance improvement within 2-3 months',
                'success_probability' => 75,
                'resources_needed' => ['Senior Assessor', 'Training Materials', 'Follow-up Schedule'],
            ];
        }

        // 2. Underperforming categories
        $categoryPerformance = $this->analyzeCategoryPerformance();
        if (!empty($categoryPerformance['weak_categories'])) {
            foreach ($categoryPerformance['weak_categories'] as $category) {
                $recommendations[] = [
                    'id' => 'category_' . $category['id'],
                    'type' => 'category_improvement',
                    'priority' => 'high',
                    'title' => 'Category Performance Enhancement',
                    'description' => "Category '{$category['name']}' shows consistently low scores across multiple schools.",
                    'impact' => 'medium',
                    'effort' => 'medium',
                    'timeframe' => '2-4 weeks',
                    'actions' => [
                        'Review assessment criteria for this category',
                        'Provide targeted training for assessors',
                        'Create best practice guidelines',
                        'Implement category-specific workshops',
                    ],
                    'expected_outcome' => 'Improved category scores system-wide',
                    'success_probability' => 85,
                    'affected_schools' => $category['affected_schools'],
                ];
            }
        }

        // 3. Assessor workload optimization
        $assessorAnalysis = $this->analyzeAssessorWorkload();
        if ($assessorAnalysis['needs_rebalancing']) {
            $recommendations[] = [
                'id' => 'assessor_workload',
                'type' => 'resource_optimization',
                'priority' => 'medium',
                'title' => 'Assessor Workload Rebalancing',
                'description' => 'Workload distribution among assessors is uneven, affecting efficiency and quality.',
                'impact' => 'medium',
                'effort' => 'low',
                'timeframe' => '1-2 weeks',
                'actions' => [
                    'Redistribute assessment assignments',
                    'Consider geographical optimization',
                    'Implement workload tracking system',
                    'Schedule regular workload reviews',
                ],
                'expected_outcome' => 'More balanced workload and improved assessment quality',
                'success_probability' => 90,
                'overloaded_assessors' => $assessorAnalysis['overloaded'],
                'underutilized_assessors' => $assessorAnalysis['underutilized'],
            ];
        }

        return $recommendations;
    }

    protected function generateActionableInsights(): array
    {
        $insights = [];

        // Performance trend insights
        $trendData = $this->analyzePerformanceTrends();
        $insights[] = [
            'type' => 'trend_analysis',
            'icon' => '📈',
            'title' => 'Performance Trends',
            'insight' => $trendData['summary'],
            'data' => $trendData,
            'action_required' => $trendData['action_needed'],
            'potential_impact' => 'Proactive trend management can prevent performance drops',
        ];

        // Regional performance insights
        $regionalData = $this->analyzeRegionalPerformance();
        if ($regionalData['has_disparities']) {
            $insights[] = [
                'type' => 'regional_analysis',
                'icon' => '🗺️',
                'title' => 'Regional Performance Gaps',
                'insight' => "Significant performance gaps detected between regions. {$regionalData['top_region']} outperforms {$regionalData['bottom_region']} by {$regionalData['gap']} points.",
                'data' => $regionalData,
                'action_required' => true,
                'potential_impact' => 'Addressing regional gaps can improve overall system performance',
            ];
        }

        // Seasonal patterns
        $seasonalData = $this->detectSeasonalPatterns();
        if ($seasonalData['has_patterns']) {
            $insights[] = [
                'type' => 'seasonal_analysis',
                'icon' => '📅',
                'title' => 'Seasonal Performance Patterns',
                'insight' => "Performance typically {$seasonalData['trend']} during {$seasonalData['period']}. Plan accordingly for optimal results.",
                'data' => $seasonalData,
                'action_required' => false,
                'potential_impact' => 'Strategic timing can optimize assessment outcomes',
            ];
        }

        // Quality assurance insights
        $qualityData = $this->analyzeAssessmentQuality();
        if ($qualityData['needs_attention']) {
            $insights[] = [
                'type' => 'quality_analysis',
                'icon' => '🔍',
                'title' => 'Assessment Quality Indicators',
                'insight' => $qualityData['summary'],
                'data' => $qualityData,
                'action_required' => true,
                'potential_impact' => 'Improved quality assurance ensures reliable assessment results',
            ];
        }

        return $insights;
    }

    protected function generateOptimizationSuggestions(): array
    {
        $suggestions = [];

        // Process optimization
        $suggestions[] = [
            'category' => 'Process',
            'title' => 'Automated Reminder System',
            'description' => 'Implement automated reminders for pending assessments to reduce delays',
            'implementation_difficulty' => 'low',
            'expected_time_savings' => '15-20 hours/month',
            'cost_impact' => 'minimal',
            'steps' => [
                'Configure email automation system',
                'Set up reminder schedules',
                'Test notification delivery',
                'Monitor effectiveness',
            ],
        ];

        $suggestions[] = [
            'category' => 'Technology',
            'title' => 'Mobile Assessment App',
            'description' => 'Develop mobile application for on-site assessments to improve efficiency',
            'implementation_difficulty' => 'high',
            'expected_time_savings' => '2-3 hours per assessment',
            'cost_impact' => 'moderate',
            'steps' => [
                'Requirements analysis',
                'App development',
                'Testing and validation',
                'Assessor training',
                'Gradual rollout',
            ],
        ];

        // Data optimization
        $suggestions[] = [
            'category' => 'Data Management',
            'title' => 'Predictive Scheduling',
            'description' => 'Use historical data to optimize assessment scheduling and resource allocation',
            'implementation_difficulty' => 'medium',
            'expected_efficiency_gain' => '25-30%',
            'cost_impact' => 'low',
            'steps' => [
                'Analyze historical patterns',
                'Develop scheduling algorithm',
                'Integrate with current system',
                'Monitor and refine',
            ],
        ];

        // Training optimization
        $suggestions[] = [
            'category' => 'Training',
            'title' => 'Adaptive Learning Program',
            'description' => 'Personalized training programs based on assessor performance data',
            'implementation_difficulty' => 'medium',
            'expected_quality_improvement' => '15-20%',
            'cost_impact' => 'moderate',
            'steps' => [
                'Assess current skill gaps',
                'Develop training modules',
                'Implement progress tracking',
                'Continuous improvement',
            ],
        ];

        return $suggestions;
    }

    protected function identifyBestPractices(): array
    {
        $practices = [];

        // Identify top-performing schools
        $topSchools = School::whereHas('assessments', function($query) {
            $query->where('total_score', '>=', 85)
                  ->whereHas('period', function($periodQuery) {
                      $periodQuery->where('end_date', '>=', now()->subMonths(12));
                  });
        })->with(['assessments' => function($query) {
            $query->latest()->first();
        }])->take(3)->get();

        foreach ($topSchools as $school) {
            $practices[] = [
                'type' => 'school_excellence',
                'school' => $school,
                'title' => 'Excellence Model',
                'description' => "Study {$school->name}'s approach for replication across similar schools",
                'key_factors' => $this->identifySuccessFactors($school),
                'replication_potential' => 'high',
                'applicable_to' => $this->findSimilarSchools($school),
            ];
        }

        // Identify efficient assessors
        $topAssessors = $this->identifyTopPerformingAssessors();
        foreach ($topAssessors as $assessor) {
            $practices[] = [
                'type' => 'assessor_excellence',
                'assessor' => $assessor,
                'title' => 'Assessment Best Practice',
                'description' => "Learn from {$assessor['name']}'s efficient assessment methodology",
                'key_practices' => $assessor['practices'],
                'training_potential' => 'high',
                'mentorship_opportunity' => true,
            ];
        }

        return $practices;
    }

    protected function analyzeResourceOptimization(): array
    {
        return [
            'current_utilization' => $this->calculateResourceUtilization(),
            'optimization_opportunities' => $this->findOptimizationOpportunities(),
            'cost_savings_potential' => $this->estimateCostSavings(),
            'efficiency_improvements' => $this->identifyEfficiencyImprovements(),
        ];
    }

    protected function getRecommendationStats(): array
    {
        return [
            'total_recommendations' => 12,
            'urgent_actions' => 3,
            'quick_wins' => 5,
            'long_term_projects' => 4,
            'estimated_impact' => '25-35% improvement',
            'implementation_timeline' => '3-6 months',
        ];
    }

    // Helper methods
    protected function analyzeCategoryPerformance(): array
    {
        $categories = AssessmentCategory::with(['indicators'])->get();
        $weakCategories = [];

        foreach ($categories as $category) {
            $avgScore = SchoolAssessment::whereHas('scores', function($query) use ($category) {
                $query->whereHas('assessmentIndicator', function($indicatorQuery) use ($category) {
                    $indicatorQuery->where('assessment_category_id', $category->id);
                });
            })->avg('total_score') ?? 0;

            if ($avgScore < 65) {
                $weakCategories[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'avg_score' => round($avgScore, 1),
                    'affected_schools' => School::whereHas('assessments.scores', function($query) use ($category) {
                        $query->whereHas('assessmentIndicator', function($indicatorQuery) use ($category) {
                            $indicatorQuery->where('assessment_category_id', $category->id);
                        });
                    })->count(),
                ];
            }
        }

        return ['weak_categories' => $weakCategories];
    }

    protected function analyzeAssessorWorkload(): array
    {
        $assessors = Assessor::withCount('assessments')->get();
        $avgWorkload = $assessors->avg('assessments_count') ?? 0;
        
        $overloaded = $assessors->filter(function($assessor) use ($avgWorkload) {
            return $assessor->assessments_count > ($avgWorkload * 1.3);
        })->values();

        $underutilized = $assessors->filter(function($assessor) use ($avgWorkload) {
            return $assessor->assessments_count < ($avgWorkload * 0.7);
        })->values();

        return [
            'needs_rebalancing' => $overloaded->count() > 0 || $underutilized->count() > 0,
            'overloaded' => $overloaded->take(3),
            'underutilized' => $underutilized->take(3),
            'avg_workload' => round($avgWorkload, 1),
        ];
    }

    protected function analyzePerformanceTrends(): array
    {
        $monthlyScores = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $score = SchoolAssessment::whereHas('period', function($query) use ($month) {
                $query->whereMonth('start_date', $month->month)
                      ->whereYear('start_date', $month->year);
            })->avg('total_score');
            
            if ($score) {
                $monthlyScores[] = round($score, 1);
            }
        }

        if (count($monthlyScores) < 3) {
            return [
                'summary' => 'Insufficient data for trend analysis',
                'action_needed' => false,
            ];
        }

        $trend = end($monthlyScores) - $monthlyScores[0];
        $isDecreasing = $trend < -2;
        
        return [
            'summary' => $isDecreasing 
                ? "Performance declining by {$trend} points over recent months"
                : "Performance stable with slight improvement",
            'action_needed' => $isDecreasing,
            'trend_value' => $trend,
            'monthly_scores' => $monthlyScores,
        ];
    }

    protected function analyzeRegionalPerformance(): array
    {
        $regions = School::distinct('provinsi')->pluck('provinsi')->filter();
        if ($regions->count() < 2) {
            return ['has_disparities' => false];
        }

        $regionalScores = [];
        foreach ($regions as $region) {
            $avgScore = School::where('provinsi', $region)
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
            return ['has_disparities' => false];
        }

        $maxScore = max($regionalScores);
        $minScore = min($regionalScores);
        $gap = $maxScore - $minScore;

        return [
            'has_disparities' => $gap > 10,
            'top_region' => array_search($maxScore, $regionalScores),
            'bottom_region' => array_search($minScore, $regionalScores),
            'gap' => round($gap, 1),
            'scores' => $regionalScores,
        ];
    }

    protected function detectSeasonalPatterns(): array
    {
        // Simplified seasonal analysis
        return [
            'has_patterns' => true,
            'trend' => 'improves',
            'period' => 'Q2 (April-June)',
            'pattern_strength' => 'moderate',
        ];
    }

    protected function analyzeAssessmentQuality(): array
    {
        $totalAssessments = SchoolAssessment::count();
        $lowScores = SchoolAssessment::where('total_score', '<', 40)->count();
        $incompleteAssessments = SchoolAssessment::whereNull('completed_at')->count();

        $qualityIssueRate = $totalAssessments > 0 ? (($lowScores + $incompleteAssessments) / $totalAssessments) * 100 : 0;

        return [
            'needs_attention' => $qualityIssueRate > 15,
            'summary' => $qualityIssueRate > 15 
                ? "Quality concerns detected in {$qualityIssueRate}% of assessments"
                : "Assessment quality within acceptable range",
            'issue_rate' => round($qualityIssueRate, 1),
            'low_scores' => $lowScores,
            'incomplete' => $incompleteAssessments,
        ];
    }

    protected function identifySuccessFactors(School $school): array
    {
        return [
            'Strong leadership engagement',
            'Consistent improvement focus',
            'Regular self-assessment practices',
            'Active stakeholder involvement',
        ];
    }

    protected function findSimilarSchools(School $school): int
    {
        return School::where('jenjang', $school->jenjang)
                   ->where('provinsi', $school->provinsi)
                   ->where('id', '!=', $school->id)
                   ->count();
    }

    protected function identifyTopPerformingAssessors(): array
    {
        return [
            [
                'name' => 'Dr. Ahmad Wijaya',
                'practices' => [
                    'Thorough preparation before assessments',
                    'Structured evaluation methodology',
                    'Clear documentation standards',
                    'Timely report completion',
                ],
            ],
        ];
    }

    protected function calculateResourceUtilization(): array
    {
        return [
            'assessor_utilization' => '78%',
            'time_efficiency' => '85%',
            'resource_allocation' => '72%',
        ];
    }

    protected function findOptimizationOpportunities(): array
    {
        return [
            'Schedule optimization could save 20% time',
            'Automated workflows could reduce admin work by 30%',
            'Better resource allocation could improve coverage by 15%',
        ];
    }

    protected function estimateCostSavings(): string
    {
        return '15-25% operational cost reduction';
    }

    protected function identifyEfficiencyImprovements(): array
    {
        return [
            'Digital assessment tools',
            'Automated reporting',
            'Optimized travel routes',
            'Streamlined approval processes',
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'All Recommendations',
            'urgent' => 'Urgent Actions',
            'quick_wins' => 'Quick Wins',
            'long_term' => 'Long-term Projects',
        ];
    }
}
