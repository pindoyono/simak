<?php

namespace App\Filament\Resources\AssessmentCategoryResource\Pages;

use App\Filament\Resources\AssessmentCategoryResource;
use App\Models\AssessmentCategory;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAssessmentCategories extends ListRecords
{
    protected static string $resource = AssessmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reorderComponents')
                ->label('Atur Urutan Komponen')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('warning')
                ->tooltip('Atur ulang urutan komponen asesmen')
                ->modalHeading('Atur Urutan Komponen Asesmen')
                ->modalDescription('Drag dan drop untuk mengatur urutan komponen dalam setiap kategori')
                ->action(function () {
                    Notification::make()
                        ->title('Fitur akan segera tersedia')
                        ->body('Fitur pengaturan urutan akan dikembangkan dalam versi mendatang.')
                        ->info()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Tambah Komponen Asesmen')
                ->icon('heroicon-s-plus')
                ->tooltip('Tambah komponen asesmen baru')
                ->modalWidth('2xl'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Komponen')
                ->icon('heroicon-o-list-bullet')
                ->badge(AssessmentCategory::count()),

            'siswa' => Tab::make('Siswa')
                ->icon('heroicon-o-academic-cap')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('komponen', 'SISWA'))
                ->badge(AssessmentCategory::where('komponen', 'SISWA')->count()),

            'guru' => Tab::make('Guru')
                ->icon('heroicon-o-user-group')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('komponen', 'GURU'))
                ->badge(AssessmentCategory::where('komponen', 'GURU')->count()),

            'kinerja_guru' => Tab::make('Kinerja Guru')
                ->icon('heroicon-o-chart-bar-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('komponen', 'KINERJA GURU'))
                ->badge(AssessmentCategory::where('komponen', 'KINERJA GURU')->count()),

            'management' => Tab::make('Management')
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('komponen', 'MANAGEMENT KEPALA SEKOLAH'))
                ->badge(AssessmentCategory::where('komponen', 'MANAGEMENT KEPALA SEKOLAH')->count()),

            'active' => Tab::make('Aktif')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'AKTIF'))
                ->badge(AssessmentCategory::where('status', 'AKTIF')->count()),

            'inactive' => Tab::make('Tidak Aktif')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'TIDAK AKTIF'))
                ->badge(AssessmentCategory::where('status', 'TIDAK AKTIF')->count()),
        ];
    }
}
