<?php

namespace App\Filament\Resources\AssessmentIndicatorResource\Pages;

use App\Filament\Resources\AssessmentIndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAssessmentIndicator extends EditRecord
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Indikator Asesmen')
                ->icon('heroicon-o-eye')
                ->color('info'),

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Indikator Asesmen Diperbarui')
            ->body('Perubahan data indikator asesmen telah berhasil disimpan.')
            ->duration(3000);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Standardize data format
        $data['nama_indikator'] = trim($data['nama_indikator']);

        return $data;
    }
}
