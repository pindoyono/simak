<?php

namespace App\Filament\Resources\AssessorResource\Pages;

use App\Filament\Resources\AssessorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;

class ViewAssessor extends ViewRecord
{
    protected static string $resource = AssessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Assessor')
                    ->icon('heroicon-o-user')
                    ->description('Detail informasi assessor dan status.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Nama Lengkap')
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('user.email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make('nomor_identitas')
                            ->label('Nomor Identitas')
                            ->icon('heroicon-o-identification')
                            ->copyable(),

                        TextEntry::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->icon('heroicon-o-phone')
                            ->url(fn ($state) => $state ? "tel:{$state}" : null),

                        TextEntry::make('institusi')
                            ->label('Institusi')
                            ->icon('heroicon-o-building-office'),

                        TextEntry::make('posisi_jabatan')
                            ->label('Posisi/Jabatan')
                            ->icon('heroicon-o-briefcase'),

                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('pengalaman_tahun')
                            ->label('Pengalaman')
                            ->suffix(' tahun')
                            ->numeric()
                            ->icon('heroicon-o-academic-cap'),
                    ]),

                Section::make('Sertifikasi & Keahlian')
                    ->icon('heroicon-o-document-check')
                    ->description('Informasi sertifikasi dan bidang keahlian assessor.')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('sertifikasi')
                            ->label('Sertifikasi')
                            ->html()
                            ->placeholder('Belum ada sertifikasi'),

                        TextEntry::make('bidang_keahlian')
                            ->label('Bidang Keahlian')
                            ->html()
                            ->placeholder('Belum ada bidang keahlian'),
                    ]),

                Section::make('Catatan')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->description('Catatan tambahan tentang assessor.')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->html()
                            ->placeholder('Tidak ada catatan'),
                    ]),

                Section::make('Informasi Sistem')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->description('Metadata dan informasi sistem.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime('d M Y, H:i')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui pada')
                            ->dateTime('d M Y, H:i')
                            ->icon('heroicon-o-clock'),
                    ]),
            ]);
    }
}
