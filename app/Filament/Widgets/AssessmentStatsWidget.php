<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\SchoolAssessment;
use App\Models\AssessmentReview;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssessmentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Schools', School::count())
                ->description('Registered schools')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Total Assessments', SchoolAssessment::count())
                ->description('All assessments')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Pending Reviews', AssessmentReview::where('status', 'submitted')->count())
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Approved Assessments', AssessmentReview::where('status', 'approved')->count())
                ->description('Successfully approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Average Score', round(SchoolAssessment::avg('total_score') ?? 0, 2))
                ->description('Overall average score')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('This Month',
                SchoolAssessment::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            )
                ->description('Assessments this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}
