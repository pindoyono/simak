<?php

namespace App\Filament\Resources\AssessmentReportResource\Pages;

use App\Filament\Resources\AssessmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditAssessmentReport extends EditRecord
{
    protected static string $resource = AssessmentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label('Publikasikan')
                ->icon('heroicon-m-globe-alt')
                ->color('success')
                ->visible(fn () => $this->record->can_be_published)
                ->requiresConfirmation()
                ->modalHeading('Publikasikan Laporan')
                ->modalDescription('Apakah Anda yakin ingin mempublikasikan laporan ini?')
                ->action(function () {
                    $this->record->publish();
                    Notification::make()
                        ->title('Laporan Berhasil Dipublikasikan!')
                        ->success()
                        ->send();
                }),

            Actions\ViewAction::make()
                ->label('Lihat'),

            Actions\DeleteAction::make()
                ->label('Hapus')
                ->visible(fn () => $this->record->canDelete()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Laporan Penilaian Berhasil Diperbarui!';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
}
