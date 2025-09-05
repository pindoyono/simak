<?php

namespace App\Filament\Resources\AssessmentIndicatorResource\Pages;

use App\Filament\Resources\AssessmentIndicatorResource;
use App\Models\AssessmentIndicator;
use App\Models\AssessmentCategory;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssessmentIndicators extends ListRecords
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Indikator Asesmen')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Indikator')
                ->icon('heroicon-o-list-bullet')
                ->badge(AssessmentIndicator::count()),

            'active' => Tab::make('Aktif')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(AssessmentIndicator::where('is_active', true)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('Tidak Aktif')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(AssessmentIndicator::where('is_active', false)->count())
                ->badgeColor('danger'),

            'siswa' => Tab::make('Komponen Siswa')
                ->icon('heroicon-o-academic-cap')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('komponen', 'SISWA')))
                ->badge(AssessmentIndicator::whereHas('category', fn (Builder $q) => $q->where('komponen', 'SISWA'))->count())
                ->badgeColor('blue'),

            'guru' => Tab::make('Komponen Guru')
                ->icon('heroicon-o-user-group')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('komponen', 'GURU')))
                ->badge(AssessmentIndicator::whereHas('category', fn (Builder $q) => $q->where('komponen', 'GURU'))->count())
                ->badgeColor('green'),

            'kinerja' => Tab::make('Kinerja Guru')
                ->icon('heroicon-o-clipboard-document-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('komponen', 'KINERJA GURU')))
                ->badge(AssessmentIndicator::whereHas('category', fn (Builder $q) => $q->where('komponen', 'KINERJA GURU'))->count())
                ->badgeColor('yellow'),

            'kepala' => Tab::make('Kepala Sekolah')
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('komponen', 'MANAGEMENT KEPALA SEKOLAH')))
                ->badge(AssessmentIndicator::whereHas('category', fn (Builder $q) => $q->where('komponen', 'MANAGEMENT KEPALA SEKOLAH'))->count())
                ->badgeColor('purple'),

            'high_weight' => Tab::make('Bobot Tinggi (≥10%)')
                ->icon('heroicon-o-scale')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('bobot_indikator', '>=', 10))
                ->badge(AssessmentIndicator::where('bobot_indikator', '>=', 10)->count())
                ->badgeColor('warning'),
        ];
    }
}
