<?php

namespace App\Filament\Resources\AssessmentReportResource\Pages;

use App\Filament\Resources\AssessmentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAssessmentReport extends CreateRecord
{
    protected static string $resource = AssessmentReportResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Laporan Penilaian Berhasil Dibuat!';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat Laporan'),
            $this->getCreateAnotherFormAction()
                ->label('Buat & Buat Lagi'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dibuat_oleh'] = \Filament\Facades\Filament::auth()->id();

        return $data;
    }
}
