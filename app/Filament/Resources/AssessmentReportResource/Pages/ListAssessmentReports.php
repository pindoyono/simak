<?php

namespace App\Filament\Resources\AssessmentReportResource\Pages;

use App\Filament\Resources\AssessmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssessmentReports extends ListRecords
{
    protected static string $resource = AssessmentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Laporan Baru')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-m-list-bullet')
                ->badge(fn () => static::getResource()::getModel()::count()),

            'draft' => Tab::make('Draft')
                ->icon('heroicon-m-document')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_laporan', 'draft'))
                ->badge(fn () => static::getResource()::getModel()::where('status_laporan', 'draft')->count())
                ->badgeColor('gray'),

            'review' => Tab::make('Review')
                ->icon('heroicon-m-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_laporan', 'review'))
                ->badge(fn () => static::getResource()::getModel()::where('status_laporan', 'review')->count())
                ->badgeColor('warning'),

            'final' => Tab::make('Final')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_laporan', 'final'))
                ->badge(fn () => static::getResource()::getModel()::where('status_laporan', 'final')->count())
                ->badgeColor('info'),

            'published' => Tab::make('Dipublikasikan')
                ->icon('heroicon-m-globe-alt')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_laporan', 'published'))
                ->badge(fn () => static::getResource()::getModel()::where('status_laporan', 'published')->count())
                ->badgeColor('success'),
        ];
    }
}
