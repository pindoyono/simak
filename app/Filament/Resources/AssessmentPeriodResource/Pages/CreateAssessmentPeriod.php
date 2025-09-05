<?php

namespace App\Filament\Resources\AssessmentPeriodResource\Pages;

use App\Filament\Resources\AssessmentPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssessmentPeriod extends CreateRecord
{
    protected static string $resource = AssessmentPeriodResource::class;

    public function getTitle(): string
    {
        return 'Buat Periode Asesmen';
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan Periode');
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Simpan & Buat Lagi');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Periode asesmen berhasil dibuat';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate nama_periode if not provided
        if (empty($data['nama_periode'])) {
            $data['nama_periode'] = "Periode {$data['semester']} {$data['tahun_ajaran']}";
        }

        return $data;
    }
}
