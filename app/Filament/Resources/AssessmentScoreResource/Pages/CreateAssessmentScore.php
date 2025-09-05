<?php

namespace App\Filament\Resources\AssessmentScoreResource\Pages;

use App\Filament\Resources\AssessmentScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAssessmentScore extends CreateRecord
{
    protected static string $resource = AssessmentScoreResource::class;

    protected static ?string $title = 'Tambah Skor Penilaian';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Skor Penilaian Tersimpan!')
            ->body('Data skor penilaian berhasil ditambahkan ke sistem.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan tanggal_penilaian tersimpan dengan benar
        if (!isset($data['tanggal_penilaian'])) {
            $data['tanggal_penilaian'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali ke Daftar')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-m-arrow-left')
                ->color('gray'),
        ];
    }
}
