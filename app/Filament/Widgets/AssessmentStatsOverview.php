<?php

namespace App\Filament\Widgets;

use App\Models\AssessmentScore;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssessmentStatsOverview extends BaseWidget
{
    public ?int $selectedSchool = null;
    public ?int $selectedPeriod = null;
    public ?int $selectedCategory = null;

    protected function getStats(): array
    {
        $query = $this->getFilteredQuery();

        $total = $query->count();
        $average = round($query->avg('skor') ?? 0, 2);
        $highest = $query->max('skor') ?? 0;
        $lowest = $query->min('skor') ?? 0;

        return [
            Stat::make('Total Penilaian', number_format($total))
                ->description('Total data penilaian')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Rata-rata Skor', $average)
                ->description('Skor rata-rata keseluruhan')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make('Skor Tertinggi', $highest)
                ->description('Nilai skor tertinggi')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),

            Stat::make('Skor Terendah', $lowest)
                ->description('Nilai skor terendah')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }

    protected function getFilteredQuery()
    {
        $query = AssessmentScore::query()
            ->with([
                'schoolAssessment.school',
                'schoolAssessment.period',
                'assessmentIndicator.category'
            ]);

        if ($this->selectedSchool) {
            $query->whereHas('schoolAssessment.school', function ($q) {
                $q->where('id', $this->selectedSchool);
            });
        }

        if ($this->selectedPeriod) {
            $query->whereHas('schoolAssessment.period', function ($q) {
                $q->where('id', $this->selectedPeriod);
            });
        }

        if ($this->selectedCategory) {
            $query->whereHas('assessmentIndicator.category', function ($q) {
                $q->where('id', $this->selectedCategory);
            });
        }

        return $query;
    }
}
