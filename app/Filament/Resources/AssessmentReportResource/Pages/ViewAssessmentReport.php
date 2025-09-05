<?php

namespace App\Filament\Resources\AssessmentReportResource\Pages;

use App\Filament\Resources\AssessmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ViewAssessmentReport extends ViewRecord
{
    protected static string $resource = AssessmentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label('Publikasikan Laporan')
                ->icon('heroicon-m-globe-alt')
                ->color('success')
                ->visible(fn () => $this->record->can_be_published)
                ->requiresConfirmation()
                ->modalHeading('Publikasikan Laporan')
                ->modalDescription('Apakah Anda yakin ingin mempublikasikan laporan ini? Laporan yang dipublikasikan dapat diakses oleh publik.')
                ->action(function () {
                    $this->record->publish();
                    Notification::make()
                        ->title('Laporan Berhasil Dipublikasikan!')
                        ->success()
                        ->send();
                }),

            Actions\EditAction::make()
                ->label('Edit Laporan')
                ->visible(fn () => $this->record->canEdit()),

            Actions\DeleteAction::make()
                ->label('Hapus Laporan')
                ->visible(fn () => $this->record->canDelete()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make('Informasi Dasar')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('schoolAssessment.school.nama_sekolah')
                                        ->label('Sekolah')
                                        ->weight(FontWeight::SemiBold)
                                        ->color('primary'),

                                    TextEntry::make('schoolAssessment.period.nama_periode')
                                        ->label('Periode Penilaian')
                                        ->weight(FontWeight::Medium),
                                ]),

                            TextEntry::make('judul_laporan')
                                ->label('Judul Laporan')
                                ->columnSpanFull()
                                ->weight(FontWeight::Bold)
                                ->size(TextEntry\TextEntrySize::Large),
                        ]),

                    Section::make('Status dan Hasil')
                        ->icon('heroicon-m-chart-bar-square')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('status_label')
                                        ->label('Status Laporan')
                                        ->badge()
                                        ->color(fn ($state) => match ($this->record->status_laporan) {
                                            'draft' => 'gray',
                                            'review' => 'warning',
                                            'final' => 'info',
                                            'published' => 'success',
                                            default => 'gray',
                                        }),

                                    TextEntry::make('is_public')
                                        ->label('Status Publikasi')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => $state ? 'Publik' : 'Internal')
                                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                                ]),

                            Grid::make(3)
                                ->schema([
                                    TextEntry::make('skor_total')
                                        ->label('Skor Total')
                                        ->numeric(2)
                                        ->suffix('%')
                                        ->weight(FontWeight::Bold)
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->color(fn ($state) => match (true) {
                                            $state >= 85 => 'success',
                                            $state >= 70 => 'info',
                                            $state >= 55 => 'warning',
                                            default => 'danger',
                                        }),

                                    TextEntry::make('grade_label')
                                        ->label('Grade Akhir')
                                        ->badge()
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->color(fn () => match ($this->record->grade_akhir) {
                                            'A' => 'success',
                                            'B' => 'info',
                                            'C' => 'warning',
                                            'D' => 'danger',
                                            default => 'gray',
                                        }),

                                    TextEntry::make('file_count')
                                        ->label('Jumlah Lampiran')
                                        ->suffix(' file')
                                        ->badge()
                                        ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                                ]),
                        ]),
                ])->from('lg'),

                Tabs::make('Konten Laporan')
                    ->tabs([
                        Tabs\Tab::make('Ringkasan Eksekutif')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Section::make('Ringkasan Eksekutif')
                                    ->description('Ringkasan eksekutif dari laporan penilaian')
                                    ->schema([
                                        TextEntry::make('ringkasan_eksekutif')
                                            ->label('')
                                            ->columnSpanFull()
                                            ->html()
                                            ->placeholder('Belum ada ringkasan eksekutif'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Temuan Utama')
                            ->icon('heroicon-m-magnifying-glass')
                            ->schema([
                                Section::make('Temuan dan Analisis')
                                    ->description('Temuan-temuan utama dari hasil penilaian')
                                    ->schema([
                                        TextEntry::make('temuan_utama')
                                            ->label('')
                                            ->columnSpanFull()
                                            ->html()
                                            ->placeholder('Belum ada temuan utama'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Rekomendasi')
                            ->icon('heroicon-m-light-bulb')
                            ->schema([
                                Section::make('Rekomendasi')
                                    ->description('Rekomendasi berdasarkan hasil penilaian')
                                    ->schema([
                                        TextEntry::make('rekomendasi')
                                            ->label('')
                                            ->columnSpanFull()
                                            ->html()
                                            ->placeholder('Belum ada rekomendasi'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Kesimpulan')
                            ->icon('heroicon-m-check-circle')
                            ->schema([
                                Section::make('Kesimpulan')
                                    ->description('Kesimpulan akhir dari laporan penilaian')
                                    ->schema([
                                        TextEntry::make('kesimpulan')
                                            ->label('')
                                            ->columnSpanFull()
                                            ->html()
                                            ->placeholder('Belum ada kesimpulan'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Informasi Tambahan')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Section::make('Detail Laporan')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('pembuatLaporan.name')
                                                    ->label('Dibuat Oleh')
                                                    ->weight(FontWeight::Medium),

                                                TextEntry::make('created_at')
                                                    ->label('Tanggal Dibuat')
                                                    ->dateTime('d F Y, H:i')
                                                    ->weight(FontWeight::Medium),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('reviewerLaporan.name')
                                                    ->label('Direview Oleh')
                                                    ->placeholder('Belum ada reviewer'),

                                                TextEntry::make('tanggal_review')
                                                    ->label('Tanggal Review')
                                                    ->dateTime('d F Y, H:i')
                                                    ->placeholder('Belum direview'),
                                            ]),

                                        TextEntry::make('catatan_reviewer')
                                            ->label('Catatan Reviewer')
                                            ->columnSpanFull()
                                            ->placeholder('Belum ada catatan reviewer'),
                                    ]),

                                Section::make('File Lampiran')
                                    ->schema([
                                        TextEntry::make('file_lampiran_list')
                                            ->label('Daftar File')
                                            ->columnSpanFull()
                                            ->listWithLineBreaks()
                                            ->placeholder('Tidak ada file lampiran'),
                                    ])
                                    ->visible(fn () => $this->record->file_count > 0),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
