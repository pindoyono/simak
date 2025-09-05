<?php

namespace App\Filament\Resources\AssessmentPeriodResource\Pages;

use App\Filament\Resources\AssessmentPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAssessmentPeriod extends ViewRecord
{
    protected static string $resource = AssessmentPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('setDefault')
                ->label('Jadikan Default')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->visible(fn (): bool => !$this->record->is_default)
                ->requiresConfirmation()
                ->modalHeading('Jadikan Periode Default')
                ->modalDescription('Apakah Anda yakin ingin menjadikan periode ini sebagai default?')
                ->action(function () {
                    \App\Models\AssessmentPeriod::where('is_default', true)->update(['is_default' => false]);
                    $this->record->update(['is_default' => true]);
                    $this->refreshFormData(['is_default']);
                })
                ->successNotificationTitle('Periode berhasil dijadikan default'),
            Actions\Action::make('activate')
                ->label('Aktifkan')
                ->icon('heroicon-o-play')
                ->color('success')
                ->visible(fn (): bool => $this->record->status !== 'aktif')
                ->requiresConfirmation()
                ->modalHeading('Aktifkan Periode')
                ->modalDescription('Apakah Anda yakin ingin mengaktifkan periode ini?')
                ->action(function () {
                    $this->record->update(['status' => 'aktif']);
                    $this->refreshFormData(['status']);
                })
                ->successNotificationTitle('Periode berhasil diaktifkan'),
            Actions\EditAction::make()
                ->label('Edit'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Detail Periode: ' . $this->record->nama_periode;
    }
}
