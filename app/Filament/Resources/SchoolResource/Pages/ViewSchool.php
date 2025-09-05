<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class ViewSchool extends ViewRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Data')
                ->icon('heroicon-m-pencil')
                ->color('warning'),
            Actions\DeleteAction::make()
                ->label('Hapus Data')
                ->icon('heroicon-m-trash')
                ->requiresConfirmation()
                ->modalHeading('Hapus Data Sekolah')
                ->modalDescription('Apakah Anda yakin ingin menghapus data sekolah ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Dasar Sekolah')
                    ->description('Data identitas dan informasi dasar sekolah')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        TextEntry::make('nama_sekolah')
                            ->label('Nama Sekolah')
                            ->weight(FontWeight::Bold)
                            ->size(TextEntry\TextEntrySize::Large)
                            ->copyable()
                            ->copyMessage('Nama sekolah berhasil disalin')
                            ->icon('heroicon-m-building-office-2')
                            ->color('primary'),

                        TextEntry::make('npsn')
                            ->label('NPSN (Nomor Pokok Sekolah Nasional)')
                            ->copyable()
                            ->copyMessage('NPSN berhasil disalin')
                            ->icon('heroicon-m-identification')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull()
                            ->icon('heroicon-m-map-pin')
                            ->copyable()
                            ->copyMessage('Alamat berhasil disalin'),
                    ])
                    ->columns(2),

                Section::make('Lokasi Administratif')
                    ->description('Pembagian wilayah administratif sekolah')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextEntry::make('kecamatan')
                            ->label('Kecamatan')
                            ->icon('heroicon-m-map')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('kabupaten_kota')
                            ->label('Kabupaten/Kota')
                            ->icon('heroicon-m-building-office')
                            ->badge()
                            ->color('blue'),

                        TextEntry::make('provinsi')
                            ->label('Provinsi')
                            ->icon('heroicon-m-globe-alt')
                            ->badge()
                            ->color('green'),
                    ])
                    ->columns(3),

                Section::make('Karakteristik Sekolah')
                    ->description('Jenjang pendidikan dan status sekolah')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextEntry::make('jenjang')
                            ->label('Jenjang Pendidikan')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'PAUD' => 'PAUD (Pendidikan Anak Usia Dini)',
                                'TK' => 'TK (Taman Kanak-kanak)',
                                'SD' => 'SD (Sekolah Dasar)',
                                'SMP' => 'SMP (Sekolah Menengah Pertama)',
                                'SMA' => 'SMA (Sekolah Menengah Atas)',
                                'SMK' => 'SMK (Sekolah Menengah Kejuruan)',
                                default => $state,
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PAUD' => 'gray',
                                'TK' => 'yellow',
                                'SD' => 'blue',
                                'SMP' => 'green',
                                'SMA' => 'purple',
                                'SMK' => 'orange',
                                default => 'gray',
                            })
                            ->icon('heroicon-m-academic-cap'),

                        TextEntry::make('status')
                            ->label('Status Sekolah')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Negeri' => 'success',
                                'Swasta' => 'info',
                                default => 'gray',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'Negeri' => 'heroicon-m-building-library',
                                'Swasta' => 'heroicon-m-building-office',
                                default => 'heroicon-m-question-mark-circle',
                            }),

                        TextEntry::make('kepala_sekolah')
                            ->label('Nama Kepala Sekolah')
                            ->icon('heroicon-m-user')
                            ->iconPosition(IconPosition::Before)
                            ->copyable()
                            ->copyMessage('Nama kepala sekolah berhasil disalin')
                            ->weight(FontWeight::Medium),
                    ])
                    ->columns(3),

                Section::make('Informasi Kontak')
                    ->description('Data kontak dan komunikasi sekolah')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextEntry::make('telepon')
                            ->label('Nomor Telepon')
                            ->placeholder('Tidak tersedia')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->copyMessage('Nomor telepon berhasil disalin')
                            ->url(fn ($state) => $state ? "tel:{$state}" : null)
                            ->openUrlInNewTab(false),

                        TextEntry::make('email')
                            ->label('Alamat Email')
                            ->placeholder('Tidak tersedia')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->copyMessage('Email berhasil disalin')
                            ->url(fn ($state) => $state ? "mailto:{$state}" : null)
                            ->openUrlInNewTab(false),
                    ])
                    ->columns(2),

                Section::make('Status dan Timestamps')
                    ->description('Status operasional dan informasi pencatatan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Status Operasional')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif Beroperasi' : 'Tidak Aktif'),

                        TextEntry::make('created_at')
                            ->label('Waktu Dibuat')
                            ->dateTime('d F Y, H:i:s')
                            ->icon('heroicon-m-calendar-days')
                            ->color('gray'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d F Y, H:i:s')
                            ->icon('heroicon-m-arrow-path')
                            ->color('gray'),
                    ])
                    ->columns(3),

                Section::make('Ringkasan Data')
                    ->description('Informasi tambahan dan statistik')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        TextEntry::make('alamat_lengkap')
                            ->label('Alamat Lengkap (Gabungan)')
                            ->state(function ($record): string {
                                return collect([
                                    $record->alamat,
                                    $record->kecamatan,
                                    $record->kabupaten_kota,
                                    $record->provinsi
                                ])->filter()->join(', ') ?: 'Alamat tidak lengkap';
                            })
                            ->icon('heroicon-m-map-pin')
                            ->copyable()
                            ->copyMessage('Alamat lengkap berhasil disalin')
                            ->columnSpanFull(),

                        TextEntry::make('kontak_lengkap')
                            ->label('Informasi Kontak Lengkap')
                            ->state(function ($record): string {
                                $kontak = [];
                                if ($record->telepon) {
                                    $kontak[] = "Telepon: {$record->telepon}";
                                }
                                if ($record->email) {
                                    $kontak[] = "Email: {$record->email}";
                                }
                                return $kontak ? implode(' | ', $kontak) : 'Kontak tidak tersedia';
                            })
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->copyMessage('Informasi kontak berhasil disalin')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return "Detail: {$record->nama_sekolah}";
    }
}
