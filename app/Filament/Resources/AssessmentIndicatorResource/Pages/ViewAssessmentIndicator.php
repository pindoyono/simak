<?php

namespace App\Filament\Resources\AssessmentIndicatorResource\Pages;

use App\Filament\Resources\AssessmentIndicatorResource;
use App\Models\AssessmentIndicator;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;

class ViewAssessmentIndicator extends ViewRecord
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Indikator Asesmen')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),

            Actions\DeleteAction::make()
                ->label('Hapus Indikator')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Indikator Asesmen')
                ->modalDescription('Apakah Anda yakin ingin menghapus indikator asesmen ini?')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Komponen & Kategori')
                    ->icon('heroicon-o-folder-open')
                    ->description('Informasi komponen dan kategori asesmen.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('category.komponen')
                                    ->label('Komponen Utama')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'SISWA' => 'blue',
                                        'GURU' => 'green',
                                        'KINERJA GURU' => 'yellow',
                                        'MANAGEMENT KEPALA SEKOLAH' => 'purple',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(function (string $state): string {
                                        return match ($state) {
                                            'SISWA' => 'SISWA - Standar Pencapaian Siswa',
                                            'GURU' => 'GURU - Standar Kualitas Guru',
                                            'KINERJA GURU' => 'KINERJA GURU - Evaluasi Proses Pembelajaran',
                                            'MANAGEMENT KEPALA SEKOLAH' => 'KEPALA SEKOLAH - Kepemimpinan dan Pengelolaan',
                                            default => $state,
                                        };
                                    })
                                    ->icon('heroicon-o-folder'),

                                TextEntry::make('category.nama_kategori')
                                    ->label('Kategori Asesmen')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-tag'),
                            ]),
                    ]),

                Section::make('Informasi Indikator')
                    ->icon('heroicon-o-document-text')
                    ->description('Detail lengkap indikator asesmen.')
                    ->schema([
                        TextEntry::make('nama_indikator')
                            ->label('Nama Indikator')
                            ->weight('bold')
                            ->size('lg')
                            ->copyable()
                            ->icon('heroicon-o-list-bullet')
                            ->columnSpanFull(),

                        TextEntry::make('deskripsi')
                            ->label('Deskripsi Indikator')
                            ->placeholder('Belum ada deskripsi')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull(),

                        TextEntry::make('kriteria_penilaian')
                            ->label('Kriteria Penilaian')
                            ->html()
                            ->placeholder('Belum ada kriteria penilaian')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->columnSpanFull(),
                    ]),

                Section::make('Pengaturan Skor & Bobot')
                    ->icon('heroicon-o-scale')
                    ->description('Konfigurasi skor dan bobot indikator.')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('skor_maksimal')
                                    ->label('Skor Maksimal')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-star'),

                                TextEntry::make('bobot_indikator')
                                    ->label('Bobot Indikator')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-scale'),

                                TextEntry::make('urutan')
                                    ->label('Urutan Tampil')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-list-bullet'),

                                IconEntry::make('is_active')
                                    ->label('Status Aktif')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),
                    ]),

                Section::make('Statistik Penggunaan')
                    ->icon('heroicon-o-chart-bar')
                    ->description('Statistik penilaian dan penggunaan indikator.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('scores_count')
                                    ->label('Jumlah Penilaian')
                                    ->getStateUsing(fn ($record) => $record->scores()->count())
                                    ->formatStateUsing(fn ($state) => $state . ' Penilaian')
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-chart-bar'),

                                TextEntry::make('average_score')
                                    ->label('Rata-rata Skor')
                                    ->getStateUsing(fn ($record) => number_format($record->scores()->avg('skor') ?? 0, 2))
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-star'),

                                TextEntry::make('category.indicators_count')
                                    ->label('Total Indikator dalam Kategori')
                                    ->getStateUsing(fn ($record) => $record->category->indicators()->count())
                                    ->formatStateUsing(fn ($state) => $state . ' Indikator')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-list-bullet'),
                            ]),
                    ]),

                Section::make('Informasi Sistem')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->description('Metadata dan riwayat perubahan data.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Data Dibuat')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
