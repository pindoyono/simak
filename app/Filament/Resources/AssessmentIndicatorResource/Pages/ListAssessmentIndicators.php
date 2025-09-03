<?php

namespace App\Filament\Resources\AssessmentIndicatorResource\Pages;

use App\Filament\Resources\AssessmentIndicatorResource;
use App\Imports\AssessmentIndicatorImport;
use App\Exports\AssessmentIndicatorTemplateExport;
use App\Exports\AssessmentIndicatorExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ListAssessmentIndicators extends ListRecords
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportData')
                ->label('Export Data')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    return Excel::download(
                        new AssessmentIndicatorExport(),
                        'data-assessment-indicator-' . date('Y-m-d') . '.xlsx'
                    );
                }),

            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new AssessmentIndicatorTemplateExport(),
                        'template-assessment-indicator.xlsx'
                    );
                }),

            Actions\Action::make('import')
                ->label('Import Data')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel'
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->maxSize(5120), // 5MB
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = Storage::disk('local')->path($data['file']);
                        
                        Excel::import(new AssessmentIndicatorImport(), $filePath);
                        
                        // Hapus file setelah import
                        Storage::disk('local')->delete($data['file']);
                        
                        Notification::make()
                            ->title('Import Berhasil')
                            ->body('Data Assessment Indicator berhasil diimport.')
                            ->success()
                            ->send();
                            
                        // Refresh halaman
                        $this->redirect(static::getUrl());
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-o-plus'),
        ];
    }
}
