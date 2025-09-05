<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSchool extends EditRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-m-eye')
                ->color('info'),
            Actions\DeleteAction::make()
                ->label('Hapus Data')
                ->icon('heroicon-m-trash')
                ->requiresConfirmation()
                ->modalHeading('Hapus Data Sekolah')
                ->modalDescription('Apakah Anda yakin ingin menghapus data sekolah ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data sekolah berhasil diperbarui';
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data Sekolah Berhasil Diperbarui')
            ->body('Perubahan data sekolah telah berhasil disimpan.')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Normalize NPSN (remove any non-numeric characters)
        if (isset($data['npsn'])) {
            $data['npsn'] = preg_replace('/[^0-9]/', '', $data['npsn']);
        }

        // Normalize phone number
        if (isset($data['telepon'])) {
            $data['telepon'] = preg_replace('/[^0-9+()-\s]/', '', $data['telepon']);
        }

        // Ensure email is lowercase
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Trim whitespace from text fields
        $textFields = ['nama_sekolah', 'alamat', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kepala_sekolah'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        return $data;
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();
        return "Edit: {$record->nama_sekolah}";
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan')
                ->icon('heroicon-m-check'),
            $this->getCancelFormAction()
                ->label('Batal')
                ->color('gray'),
        ];
    }
}
