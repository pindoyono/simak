<?php

namespace App\Filament\Resources\AssessmentCategoryResource\Pages;

use App\Filament\Resources\AssessmentCategoryResource;
use App\Models\AssessmentCategory;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewAssessmentCategory extends ViewRecord
{
    protected static string $resource = AssessmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Komponen')
                ->icon('heroicon-s-pencil')
                ->color('warning'),

            Actions\DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-s-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Komponen Asesmen')
                ->modalDescription('Apakah Anda yakin ingin menghapus komponen asesmen ini?')
                ->successRedirectUrl(AssessmentCategoryResource::getUrl('index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Komponen Asesmen')
                    ->description('Detail lengkap komponen asesmen dalam sistem')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('komponen')
                                    ->label('Komponen')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'SISWA' => 'success',
                                        'GURU' => 'info',
                                        'KINERJA GURU' => 'warning',
                                        'MANAGEMENT KEPALA SEKOLAH' => 'danger',
                                        default => 'gray',
                                    })
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('nama_kategori')
                                    ->label('Nama Kategori')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Medium)
                                    ->color('primary'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('bobot_penilaian')
                                    ->label('Bobot Penilaian')
                                    ->suffix('%')
                                    ->numeric()
                                    ->badge()
                                    ->color('success')
                                    ->weight(FontWeight::SemiBold),

                                Infolists\Components\TextEntry::make('urutan')
                                    ->label('Urutan')
                                    ->badge()
                                    ->color('info')
                                    ->weight(FontWeight::SemiBold),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => $state === 'AKTIF' ? 'success' : 'danger')
                                    ->weight(FontWeight::SemiBold),
                            ]),
                    ]),

                Infolists\Components\Section::make('Deskripsi')
                    ->schema([
                        Infolists\Components\TextEntry::make('deskripsi')
                            ->label('')
                            ->placeholder('Tidak ada deskripsi tersedia')
                            ->hiddenLabel(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record->deskripsi)),

                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d F Y, H:i')
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime('d F Y, H:i')
                                    ->color('gray'),
                            ]),

                        Infolists\Components\TextEntry::make('id')
                            ->label('ID Sistem')
                            ->badge()
                            ->color('gray'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Statistik Penggunaan')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('indicators_count')
                                    ->label('Jumlah Indikator')
                                    ->state(fn (AssessmentCategory $record): int => $record->assessmentIndicators()->count())
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('active_indicators_count')
                                    ->label('Indikator Aktif')
                                    ->state(fn (AssessmentCategory $record): int => $record->assessmentIndicators()->where('status', 'AKTIF')->count())
                                    ->badge()
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('assessments_count')
                                    ->label('Jumlah Asesmen')
                                    ->state(fn (AssessmentCategory $record): int => $record->assessmentScores()->distinct('school_assessment_id')->count())
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
