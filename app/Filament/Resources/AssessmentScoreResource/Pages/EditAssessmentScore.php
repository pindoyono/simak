<?php

namespace App\Filament\Resources\AssessmentScoreResource\Pages;

use App\Filament\Resources\AssessmentScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAssessmentScore extends EditRecord
{
    protected static string $resource = AssessmentScoreResource::class;

    protected static ?string $title = 'Edit Skor Penilaian';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Skor Penilaian Diperbarui!')
            ->body('Perubahan data skor penilaian berhasil disimpan.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-m-eye'),
            Actions\DeleteAction::make()
                ->label('Hapus Skor')
                ->icon('heroicon-m-trash')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Skor Penilaian Dihapus!')
                        ->body('Data skor penilaian berhasil dihapus dari sistem.')
                ),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update timestamp untuk tracking perubahan
        $data['updated_at'] = now();

        return $data;
    }
}
