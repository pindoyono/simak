<?php

namespace App\Filament\Resources\AssessmentCategoryResource\Pages;

use App\Filament\Resources\AssessmentCategoryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentCategory extends EditRecord
{
    protected static string $resource = AssessmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-s-eye')
                ->color('info'),

            Actions\DeleteAction::make()
                ->label('Hapus Komponen')
                ->icon('heroicon-s-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalHeading('Hapus Komponen Asesmen')
                ->modalDescription('Apakah Anda yakin ingin menghapus komponen asesmen ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Komponen Asesmen Berhasil Dihapus!')
                        ->body('Komponen asesmen telah berhasil dihapus dari sistem.')
                        ->duration(5000)
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Komponen Asesmen Berhasil Diperbarui!')
            ->body('Perubahan pada komponen asesmen "' . $this->getRecord()->nama_kategori . '" telah berhasil disimpan.')
            ->duration(5000);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-adjust urutan if komponen changed
        if (isset($data['komponen']) && $data['komponen'] !== $this->getRecord()->komponen) {
            $maxUrutan = \App\Models\AssessmentCategory::where('komponen', $data['komponen'])
                ->where('id', '!=', $this->getRecord()->id)
                ->max('urutan');
            $data['urutan'] = ($maxUrutan ?? 0) + 1;
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan')
                ->icon('heroicon-s-check'),

            $this->getCancelFormAction()
                ->label('Batal')
                ->icon('heroicon-s-x-mark')
                ->color('gray'),
        ];
    }
}
