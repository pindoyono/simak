<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\AssessmentScore;
use App\Models\AssessmentCategory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Performance Trends';
    protected static ?string $description = 'Track performance improvements over time';
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = '120s';
    protected static string $color = 'success';
    
    public ?string $filter = 'monthly_scores';
    
    protected function getData(): array
    {
        return match ($this->filter) {
            'monthly_scores' => $this->getMonthlyScoreData(),
            'category_performance' => $this->getCategoryPerformanceData(),
            'completion_rate' => $this->getCompletionRateData(),
            default => $this->getMonthlyScoreData(),
        };
    }

    protected function getType(): string
    {
        return match ($this->filter) {
            'category_performance' => 'radar',
            'completion_rate' => 'bar',
            default => 'line',
        };
    }

    private function getMonthlyScoreData(): array
    {
        $months = collect();
        $scores = collect();
        
        // Get last 6 months of data
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            $avgScore = SchoolAssessment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->avg('total_score') ?? 0;
                
            $months->push($monthName);
            $scores->push(round($avgScore, 2));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average Score',
                    'data' => $scores->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 6,
                ]
            ],
            'labels' => $months->toArray(),
        ];
    }

    private function getCategoryPerformanceData(): array
    {
        $categories = AssessmentCategory::orderBy('nama_kategori')->get();
        $scores = [];
        $labels = [];
        
        foreach ($categories as $category) {
            $avgScore = AssessmentScore::whereHas('assessmentIndicator', function($q) use ($category) {
                    $q->where('assessment_category_id', $category->id);
                })
                ->avg('skor') ?? 0;
                
            $scores[] = round($avgScore, 2);
            $labels[] = $category->nama_kategori;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Category Performance',
                    'data' => $scores,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#3b82f6',
                ]
            ],
            'labels' => $labels,
        ];
    }

    private function getCompletionRateData(): array
    {
        $weeks = collect();
        $completions = collect();
        
        // Get last 8 weeks of completion data
        for ($i = 7; $i >= 0; $i--) {
            $startDate = Carbon::now()->subWeeks($i)->startOfWeek();
            $endDate = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $weekLabel = $startDate->format('M d');
            $completed = SchoolAssessment::whereBetween('updated_at', [$startDate, $endDate])
                ->where('status', 'approved')
                ->count();
                
            $weeks->push($weekLabel);
            $completions->push($completed);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Completed Assessments',
                    'data' => $completions->toArray(),
                    'backgroundColor' => [
                        '#ef4444', '#f59e0b', '#10b981', '#3b82f6',
                        '#8b5cf6', '#f97316', '#06b6d4', '#84cc16'
                    ],
                    'borderColor' => [
                        '#dc2626', '#d97706', '#059669', '#2563eb',
                        '#7c3aed', '#ea580c', '#0891b2', '#65a30d'
                    ],
                    'borderWidth' => 2,
                ]
            ],
            'labels' => $weeks->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        $baseOptions = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ]
            ],
        ];

        return match ($this->filter) {
            'category_performance' => array_merge($baseOptions, [
                'scales' => [
                    'r' => [
                        'beginAtZero' => true,
                        'max' => 4,
                        'ticks' => [
                            'stepSize' => 1
                        ]
                    ]
                ]
            ]),
            'completion_rate' => array_merge($baseOptions, [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'stepSize' => 1
                        ]
                    ]
                ]
            ]),
            default => array_merge($baseOptions, [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'max' => 4,
                        'ticks' => [
                            'stepSize' => 0.5
                        ]
                    ]
                ],
                'elements' => [
                    'point' => [
                        'hoverRadius' => 8,
                    ]
                ]
            ]),
        };
    }

    protected function getFilters(): ?array
    {
        return [
            'monthly_scores' => 'Monthly Average Scores',
            'category_performance' => 'Performance by Category',
            'completion_rate' => 'Weekly Completion Rate',
        ];
    }

    protected function getFooter(): ?string
    {
        return match ($this->filter) {
            'monthly_scores' => $this->getMonthlyFooter(),
            'category_performance' => $this->getCategoryFooter(),
            'completion_rate' => $this->getCompletionFooter(),
            default => null,
        };
    }

    private function getMonthlyFooter(): string
    {
        $currentMonth = SchoolAssessment::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->avg('total_score') ?? 0;
            
        $lastMonth = SchoolAssessment::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->avg('total_score') ?? 0;
            
        $trend = $currentMonth > $lastMonth ? '📈 Improving' : ($currentMonth < $lastMonth ? '📉 Declining' : '➡️ Stable');
        
        return "Current month: " . number_format($currentMonth, 2) . " | {$trend}";
    }

    private function getCategoryFooter(): string
    {
        $bestCategory = AssessmentScore::select('assessment_indicator_id')
            ->with('assessmentIndicator.category')
            ->get()
            ->groupBy('assessmentIndicator.category.nama_kategori')
            ->map(function($scores) {
                return $scores->avg('skor');
            })
            ->sortDesc()
            ->keys()
            ->first();
            
        return $bestCategory ? "Best performing: {$bestCategory}" : "No data available";
    }

    private function getCompletionFooter(): string
    {
        $thisWeek = SchoolAssessment::whereBetween('updated_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->where('status', 'approved')->count();
        
        return "This week: {$thisWeek} assessments completed";
    }
}
