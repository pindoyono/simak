<?php

namespace App\Filament\Resources\AssessmentPeriodResource\Pages;

use App\Filament\Resources\AssessmentPeriodResource;
use App\Models\AssessmentPeriod;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssessmentPeriods extends ListRecords
{
    protected static string $resource = AssessmentPeriodResource::class;

    public function getTitle(): string
    {
        return 'Periode Asesmen';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Periode Baru')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Periode'),
            'aktif' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'aktif'))
                ->badge(AssessmentPeriod::query()->where('status', 'aktif')->count())
                ->badgeColor('success'),
            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(AssessmentPeriod::query()->where('status', 'draft')->count())
                ->badgeColor('gray'),
            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'selesai'))
                ->badge(AssessmentPeriod::query()->where('status', 'selesai')->count())
                ->badgeColor('danger'),
            'default' => Tab::make('Default')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default', true))
                ->badge(AssessmentPeriod::query()->where('is_default', true)->count())
                ->badgeColor('warning'),
        ];
    }
}
