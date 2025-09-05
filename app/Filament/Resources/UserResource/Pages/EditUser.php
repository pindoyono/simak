<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Pengguna')
                ->icon('heroicon-o-eye')
                ->color('info'),
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
            Actions\Action::make('cancel')
                ->label('Batal')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record]))
                ->color('gray')
                ->icon('heroicon-o-x-mark'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove password confirmation from data
        unset($data['password_confirmation']);

        // If password is empty, remove it from data to prevent overwriting
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->record;

        Notification::make()
            ->title('Pengguna Berhasil Diperbarui')
            ->body("Pengguna '{$user->name}' telah diperbarui.")
            ->success()
            ->duration(3000)
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
