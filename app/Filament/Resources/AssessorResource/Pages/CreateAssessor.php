<?php

namespace App\Filament\Resources\AssessorResource\Pages;

use App\Filament\Resources\AssessorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAssessor extends CreateRecord
{
    protected static string $resource = AssessorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Asesor berhasil dibuat')
            ->body('Data asesor telah berhasil disimpan ke sistem.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }
}
