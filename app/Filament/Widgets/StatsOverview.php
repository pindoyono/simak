<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\AssessmentCategory;
use App\Models\SchoolAssessment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Sekolah', School::count())
                ->description('Jumlah sekolah terdaftar')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Kategori Asesmen', AssessmentCategory::count())
                ->description('Kategori penilaian aktif')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),

            Stat::make('Asesmen Selesai', SchoolAssessment::where('status', 'approved')->count())
                ->description('Asesmen yang telah disetujui')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('warning'),

            Stat::make('Asesmen Pending', SchoolAssessment::whereIn('status', ['draft', 'submitted', 'reviewed'])->count())
                ->description('Asesmen menunggu proses')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}
