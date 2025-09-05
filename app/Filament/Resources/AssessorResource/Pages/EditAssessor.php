<?php

namespace App\Filament\Resources\AssessorResource\Pages;

use App\Filament\Resources\AssessorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAssessor extends EditRecord
{
    protected static string $resource = AssessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Asesor berhasil diperbarui')
            ->body('Perubahan data asesor telah berhasil disimpan.');
    }
}
