<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengguna Baru')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Pengguna')
                ->badge(fn () => \App\Models\User::count())
                ->icon('heroicon-o-users'),

            'active' => Tab::make('Pengguna Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => \App\Models\User::where('is_active', true)->count())
                ->badgeColor('success')
                ->icon('heroicon-o-check-circle'),

            'inactive' => Tab::make('Pengguna Tidak Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => \App\Models\User::where('is_active', false)->count())
                ->badgeColor('danger')
                ->icon('heroicon-o-x-circle'),

            'verified' => Tab::make('Terverifikasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('email_verified_at'))
                ->badge(fn () => \App\Models\User::whereNotNull('email_verified_at')->count())
                ->badgeColor('success')
                ->icon('heroicon-o-check-badge'),

            'unverified' => Tab::make('Belum Terverifikasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('email_verified_at'))
                ->badge(fn () => \App\Models\User::whereNull('email_verified_at')->count())
                ->badgeColor('warning')
                ->icon('heroicon-o-exclamation-triangle'),

            'super_admins' => Tab::make('Super Admin')
                ->modifyQueryUsing(fn (Builder $query) => $query->role('super_admin'))
                ->badge(fn () => \App\Models\User::role('super_admin')->count())
                ->badgeColor('danger')
                ->icon('heroicon-o-shield-check'),

            'admins' => Tab::make('Administrator')
                ->modifyQueryUsing(fn (Builder $query) => $query->role('admin'))
                ->badge(fn () => \App\Models\User::role('admin')->count())
                ->badgeColor('warning')
                ->icon('heroicon-o-shield-exclamation'),

            'assessors' => Tab::make('Asesor')
                ->modifyQueryUsing(fn (Builder $query) => $query->role('assessor'))
                ->badge(fn () => \App\Models\User::role('assessor')->count())
                ->badgeColor('info')
                ->icon('heroicon-o-user-group'),

            'schools' => Tab::make('Sekolah')
                ->modifyQueryUsing(fn (Builder $query) => $query->role('school'))
                ->badge(fn () => \App\Models\User::role('school')->count())
                ->badgeColor('success')
                ->icon('heroicon-o-building-office-2'),
        ];
    }
}
