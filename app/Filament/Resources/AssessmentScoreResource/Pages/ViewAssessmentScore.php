<?php

namespace App\Filament\Resources\AssessmentScoreResource\Pages;

use App\Filament\Resources\AssessmentScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ViewAssessmentScore extends ViewRecord
{
    protected static string $resource = AssessmentScoreResource::class;

    protected static ?string $title = 'Detail Skor Penilaian';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Skor')
                ->icon('heroicon-m-pencil-square'),
            Actions\DeleteAction::make()
                ->label('Hapus Skor')
                ->icon('heroicon-m-trash'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make('Informasi Penilaian')
                        ->icon('heroicon-m-information-circle')
                        ->description('Data umum penilaian')
                        ->schema([
                            TextEntry::make('schoolAssessment.school.nama_sekolah')
                                ->label('Sekolah')
                                ->weight(FontWeight::SemiBold)
                                ->icon('heroicon-m-building-office-2')
                                ->color('primary'),

                            TextEntry::make('schoolAssessment.period.nama_periode')
                                ->label('Periode Penilaian')
                                ->icon('heroicon-m-calendar-days'),

                            TextEntry::make('assessmentIndicator.nama_indikator')
                                ->label('Indikator Penilaian')
                                ->columnSpanFull()
                                ->weight(FontWeight::Medium),

                            TextEntry::make('tanggal_penilaian')
                                ->label('Tanggal Penilaian')
                                ->dateTime('d F Y, H:i')
                                ->icon('heroicon-m-calendar-days'),
                        ])
                        ->columns(2),

                    Section::make('Hasil Penilaian')
                        ->icon('heroicon-m-chart-bar-square')
                        ->description('Perhitungan dan grade')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('skor')
                                        ->label('Skor Diperoleh')
                                        ->numeric(2)
                                        ->badge()
                                        ->size('lg')
                                        ->weight(FontWeight::Bold)
                                        ->color(fn ($state): string => match (true) {
                                            $state >= 3.5 => 'success',
                                            $state >= 2.5 => 'info',
                                            $state >= 1.5 => 'warning',
                                            default => 'danger',
                                        }),

                                    TextEntry::make('persentase')
                                        ->label('Persentase')
                                        ->formatStateUsing(fn ($state) => $state . '%')
                                        ->badge()
                                        ->size('lg')
                                        ->weight(FontWeight::Bold)
                                        ->color(fn ($state): string => match (true) {
                                            $state >= 85 => 'success',
                                            $state >= 70 => 'info',
                                            $state >= 55 => 'warning',
                                            default => 'danger',
                                        }),
                                ]),

                            TextEntry::make('grade_label')
                                ->label('Grade Penilaian')
                                ->badge()
                                ->size('lg')
                                ->weight(FontWeight::Bold),
                        ])
                        ->columns(1),
                ])->from('lg'),

                Section::make('Bukti Pendukung')
                    ->icon('heroicon-m-document-text')
                    ->description('Dokumentasi dan catatan penilaian')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('bukti_dukung')
                            ->label('Deskripsi Bukti Pendukung')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada deskripsi bukti pendukung'),

                        TextEntry::make('catatan')
                            ->label('Catatan Evaluator')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada catatan dari evaluator'),

                        TextEntry::make('file_bukti')
                            ->label('File Bukti Terlampir')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                if (!is_array($state) || empty($state)) {
                                    return 'Tidak ada file bukti terlampir';
                                }
                                return count($state) . ' file terlampir';
                            })
                            ->badge(),
                    ]),

                Section::make('Riwayat Perubahan')
                    ->icon('heroicon-m-clock')
                    ->description('Informasi pembuatan dan modifikasi')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Tanggal Dibuat')
                                    ->dateTime('d F Y, H:i:s')
                                    ->icon('heroicon-m-plus-circle'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d F Y, H:i:s')
                                    ->icon('heroicon-m-pencil-square'),
                            ]),
                    ]),
            ]);
    }
}
