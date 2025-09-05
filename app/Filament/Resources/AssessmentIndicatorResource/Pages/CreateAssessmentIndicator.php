<?php

namespace App\Filament\Resources\AssessmentIndicatorResource\Pages;

use App\Filament\Resources\AssessmentIndicatorResource;
use App\Models\AssessmentIndicator;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAssessmentIndicator extends CreateRecord
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Indikator Asesmen Berhasil Ditambahkan')
            ->body('Indikator asesmen baru telah berhasil disimpan ke sistem.')
            ->duration(3000);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Standardize data format
        $data['nama_indikator'] = trim($data['nama_indikator']);

        // Set default active status
        $data['is_active'] = $data['is_active'] ?? true;

        // Auto-set urutan if not provided
        if (!isset($data['urutan']) || $data['urutan'] == 0) {
            $lastOrder = AssessmentIndicator::where('assessment_category_id', $data['assessment_category_id'])
                ->max('urutan');
            $data['urutan'] = ($lastOrder ?? 0) + 1;
        }

        return $data;
    }
}
