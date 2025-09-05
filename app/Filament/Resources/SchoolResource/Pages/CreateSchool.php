<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSchool extends CreateRecord
{
    protected static string $resource = SchoolResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data sekolah berhasil ditambahkan';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data Sekolah Berhasil Ditambahkan')
            ->body('Data sekolah baru telah berhasil disimpan ke dalam sistem.')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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

    public function getTitle(): string
    {
        return 'Tambah Data Sekolah Baru';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Data Sekolah')
                ->icon('heroicon-m-check'),
            $this->getCancelFormAction()
                ->label('Batal')
                ->color('gray'),
        ];
    }
}
