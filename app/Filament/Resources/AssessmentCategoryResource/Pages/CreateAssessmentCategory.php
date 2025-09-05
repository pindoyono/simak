<?php

namespace App\Filament\Resources\AssessmentCategoryResource\Pages;

use App\Filament\Resources\AssessmentCategoryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAssessmentCategory extends CreateRecord
{
    protected static string $resource = AssessmentCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Komponen Asesmen Berhasil Dibuat!')
            ->body('Komponen asesmen "' . $this->getRecord()->nama_kategori . '" telah berhasil ditambahkan ke sistem.')
            ->duration(5000);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'AKTIF';
        }

        // Auto-generate urutan if not provided
        if (!isset($data['urutan']) || empty($data['urutan'])) {
            $maxUrutan = \App\Models\AssessmentCategory::where('komponen', $data['komponen'])
                ->max('urutan');
            $data['urutan'] = ($maxUrutan ?? 0) + 1;
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Komponen')
                ->icon('heroicon-s-check'),

            $this->getCreateAnotherFormAction()
                ->label('Simpan & Buat Lagi')
                ->icon('heroicon-s-plus'),

            $this->getCancelFormAction()
                ->label('Batal')
                ->icon('heroicon-s-x-mark')
                ->color('gray'),
        ];
    }
}
