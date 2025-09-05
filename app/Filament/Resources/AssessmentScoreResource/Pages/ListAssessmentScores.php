<?php

namespace App\Filament\Resources\AssessmentScoreResource\Pages;

use App\Filament\Resources\AssessmentScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AssessmentScore;

class ListAssessmentScores extends ListRecords
{
    protected static string $resource = AssessmentScoreResource::class;

    protected static ?string $title = 'Daftar Skor Penilaian';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Skor Penilaian')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Skor')
                ->badge(AssessmentScore::count())
                ->icon('heroicon-m-list-bullet'),

            'sangat_baik' => Tab::make('Sangat Baik (A)')
                ->modifyQueryUsing(fn (Builder $query) => $query->byGrade('A'))
                ->badge(AssessmentScore::byGrade('A')->count())
                ->icon('heroicon-m-star')
                ->badgeColor('success'),

            'baik' => Tab::make('Baik (B)')
                ->modifyQueryUsing(fn (Builder $query) => $query->byGrade('B'))
                ->badge(AssessmentScore::byGrade('B')->count())
                ->icon('heroicon-m-hand-thumb-up')
                ->badgeColor('info'),

            'cukup' => Tab::make('Cukup (C)')
                ->modifyQueryUsing(fn (Builder $query) => $query->byGrade('C'))
                ->badge(AssessmentScore::byGrade('C')->count())
                ->icon('heroicon-m-minus-circle')
                ->badgeColor('warning'),

            'kurang' => Tab::make('Kurang (D)')
                ->modifyQueryUsing(fn (Builder $query) => $query->byGrade('D'))
                ->badge(AssessmentScore::byGrade('D')->count())
                ->icon('heroicon-m-x-circle')
                ->badgeColor('danger'),

            'dengan_file' => Tab::make('Dengan File Bukti')
                ->modifyQueryUsing(fn (Builder $query) => $query->withFiles())
                ->badge(AssessmentScore::withFiles()->count())
                ->icon('heroicon-m-document-arrow-up')
                ->badgeColor('primary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa ditambahkan widget statistik ringkas
        ];
    }
}
