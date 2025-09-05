<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel')
                ->label('Batal')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-x-mark'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;

        // Set email verification timestamp if email is provided
        if (!empty($data['email']) && !isset($data['email_verified_at'])) {
            $data['email_verified_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        Notification::make()
            ->title('Pengguna Berhasil Dibuat')
            ->body("Pengguna '{$user->name}' telah dibuat dengan email '{$user->email}'.")
            ->success()
            ->duration(5000)
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
