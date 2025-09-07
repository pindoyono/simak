<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\AssessmentPeriod;
use App\Models\School;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AssessmentProgressChart extends ChartWidget
{
    protected static ?string $heading = 'Assessment Progress Overview';
    protected static ?string $description = 'Track assessment completion across all schools and periods';
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = '60s';
    protected static string $color = 'info';
    
    public ?string $filter = 'current_period';
    
    protected function getData(): array
    {
        $period = $this->getPeriodFromFilter();
        
        // Get assessment status data
        $statusData = SchoolAssessment::when($period, function($q) use ($period) {
                return $q->where('assessment_period_id', $period->id);
            })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are represented
        $statuses = ['draft', 'submitted', 'reviewed', 'approved'];
        $counts = [];
        $colors = [];
        $labels = [];
        
        foreach ($statuses as $status) {
            $count = $statusData[$status] ?? 0;
            $counts[] = $count;
            
            // Set colors and labels
            switch ($status) {
                case 'draft':
                    $colors[] = '#ef4444'; // red-500
                    $labels[] = 'Draft (' . $count . ')';
                    break;
                case 'submitted':
                    $colors[] = '#f59e0b'; // amber-500
                    $labels[] = 'Submitted (' . $count . ')';
                    break;
                case 'reviewed':
                    $colors[] = '#3b82f6'; // blue-500
                    $labels[] = 'Under Review (' . $count . ')';
                    break;
                case 'approved':
                    $colors[] = '#10b981'; // emerald-500
                    $labels[] = 'Approved (' . $count . ')';
                    break;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Assessments',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 2,
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => [
                            'size' => 12
                        ]
                    ]
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed + " assessments (" + Math.round((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100) + "%)";
                        }'
                    ]
                ]
            ],
            'cutout' => '60%',
            'animation' => [
                'animateRotate' => true,
                'duration' => 1000
            ]
        ];
    }

    protected function getFilters(): ?array
    {
        $periods = AssessmentPeriod::orderBy('created_at', 'desc')->get();
        
        $filters = ['current_period' => 'Current Period'];
        
        foreach ($periods as $period) {
            $filters[$period->id] = $period->nama_periode;
        }
        
        $filters['all'] = 'All Periods';
        
        return $filters;
    }

    private function getPeriodFromFilter(): ?AssessmentPeriod
    {
        if ($this->filter === 'current_period') {
            return AssessmentPeriod::where('is_default', true)->first();
        } elseif ($this->filter === 'all') {
            return null;
        } else {
            return AssessmentPeriod::find($this->filter);
        }
    }

    protected function getFooter(): ?string
    {
        $period = $this->getPeriodFromFilter();
        $totalSchools = School::count();
        $assessedSchools = SchoolAssessment::when($period, function($q) use ($period) {
                return $q->where('assessment_period_id', $period->id);
            })
            ->distinct('school_id')
            ->count();
            
        $percentage = $totalSchools > 0 ? round(($assessedSchools / $totalSchools) * 100, 1) : 0;
        
        return "{$assessedSchools} of {$totalSchools} schools assessed ({$percentage}%)";
    }
}
