<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\School;

class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Sekolah')
                ->icon('heroicon-m-plus')
                ->tooltip('Tambah data sekolah baru'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Sekolah')
                ->icon('heroicon-m-building-office-2')
                ->badge(School::count()),

            'active' => Tab::make('Aktif')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(School::where('is_active', true)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make('Tidak Aktif')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(School::where('is_active', false)->count())
                ->badgeColor('danger'),

            'sd' => Tab::make('Sekolah Dasar')
                ->icon('heroicon-m-academic-cap')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SD'))
                ->badge(School::where('jenjang', 'SD')->count())
                ->badgeColor('blue'),

            'smp' => Tab::make('SMP')
                ->icon('heroicon-m-book-open')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SMP'))
                ->badge(School::where('jenjang', 'SMP')->count())
                ->badgeColor('green'),

            'sma' => Tab::make('SMA')
                ->icon('heroicon-m-academic-cap')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SMA'))
                ->badge(School::where('jenjang', 'SMA')->count())
                ->badgeColor('purple'),

            'smk' => Tab::make('SMK')
                ->icon('heroicon-m-wrench-screwdriver')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('jenjang', 'SMK'))
                ->badge(School::where('jenjang', 'SMK')->count())
                ->badgeColor('orange'),

            'negeri' => Tab::make('Negeri')
                ->icon('heroicon-m-building-library')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Negeri'))
                ->badge(School::where('status', 'Negeri')->count())
                ->badgeColor('success'),

            'swasta' => Tab::make('Swasta')
                ->icon('heroicon-m-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Swasta'))
                ->badge(School::where('status', 'Swasta')->count())
                ->badgeColor('info'),
        ];
    }

    public function getTitle(): string
    {
        return 'Data Sekolah';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add custom widgets here if needed
        ];
    }
}
