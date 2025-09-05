<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Pengguna')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),
            Actions\DeleteAction::make()
                ->label('Hapus Pengguna')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->before(function ($record) {
                    // Prevent deletion of the last super admin
                    if ($record->hasRole('super_admin')) {
                        $adminCount = \App\Models\User::role('super_admin')->count();
                        if ($adminCount <= 1) {
                            throw new \Exception('Tidak dapat menghapus super admin terakhir.');
                        }
                    }
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pengguna')
                    ->description('Detail lengkap profil pengguna')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Nama Lengkap')
                                            ->icon('heroicon-o-user')
                                            ->copyable()
                                            ->weight('bold')
                                            ->size('lg'),

                                        Infolists\Components\TextEntry::make('email')
                                            ->label('Alamat Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable()
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('phone')
                                            ->label('Nomor Telepon')
                                            ->icon('heroicon-o-phone')
                                            ->copyable()
                                            ->placeholder('Tidak tersedia'),
                                    ]),

                                    Infolists\Components\Group::make([
                                        Infolists\Components\ImageEntry::make('avatar')
                                            ->label('Foto Profil')
                                            ->circular()
                                            ->size(120)
                                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF&size=240'),
                                    ]),
                                ]),
                        ]),
                    ])
                    ->icon('heroicon-o-identification'),

                Infolists\Components\Section::make('Keamanan Akun')
                    ->description('Status keamanan dan verifikasi')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\IconEntry::make('email_verified_at')
                                    ->label('Verifikasi Email')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-badge')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                Infolists\Components\IconEntry::make('is_active')
                                    ->label('Status Akun')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                Infolists\Components\TextEntry::make('email_verified_at')
                                    ->label('Tanggal Terverifikasi')
                                    ->dateTime('d F Y, H:i')
                                    ->placeholder('Belum terverifikasi')
                                    ->icon('heroicon-o-calendar-days'),

                                Infolists\Components\TextEntry::make('last_login_at')
                                    ->label('Login Terakhir')
                                    ->dateTime('d F Y, H:i')
                                    ->placeholder('Belum pernah login')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ])
                    ->icon('heroicon-o-shield-check'),

                Infolists\Components\Section::make('Role & Izin')
                    ->description('Role pengguna dan tingkat akses')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('roles')
                            ->label('Role yang Diberikan')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nama Role')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'super_admin' => 'danger',
                                        'admin' => 'warning',
                                        'assessor' => 'info',
                                        'school' => 'success',
                                        default => 'gray',
                                    }),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-key'),

                Infolists\Components\Section::make('Informasi Terkait')
                    ->description('Catatan dan hubungan terkait')
                    ->schema([
                        Infolists\Components\TextEntry::make('assessor.full_name')
                            ->label('Profil Asesor')
                            ->placeholder('Tidak ada profil asesor terkait')
                            ->icon('heroicon-o-user-group')
                            ->visible(fn ($record) => $record->assessor !== null),

                        Infolists\Components\TextEntry::make('assessment_reviews_count')
                            ->label('Ulasan Penilaian')
                            ->numeric()
                            ->suffix(' ulasan')
                            ->icon('heroicon-o-document-text'),
                    ])
                    ->icon('heroicon-o-link'),

                Infolists\Components\Section::make('Informasi Sistem')
                    ->description('Detail pembuatan dan modifikasi akun')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Akun Dibuat')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-calendar-days'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-calendar-days'),
                            ]),
                    ])
                    ->icon('heroicon-o-information-circle')
                    ->collapsible(),
            ]);
    }
}
