<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Filament\Resources\SchoolResource\RelationManagers;
use App\Models\School;
use App\Imports\SchoolImport;
use App\Exports\SchoolTemplateExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Schools';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sekolah')
                    ->schema([
                        Forms\Components\TextInput::make('nama_sekolah')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('npsn')
                            ->label('NPSN')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\TextInput::make('kecamatan')
                            ->label('Kecamatan')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('kabupaten_kota')
                            ->label('Kabupaten/Kota')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('provinsi')
                            ->label('Provinsi')
                            ->required()
                            ->maxLength(100),
                    ])->columns(3),

                Forms\Components\Section::make('Detail Sekolah')
                    ->schema([
                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang')
                            ->required()
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                                'TK' => 'TK',
                                'PAUD' => 'PAUD',
                            ]),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'Negeri' => 'Negeri',
                                'Swasta' => 'Swasta',
                            ]),
                        Forms\Components\TextInput::make('kepala_sekolah')
                            ->label('Kepala Sekolah')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('telepon')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_sekolah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('npsn')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kecamatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabupaten_kota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provinsi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenjang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kepala_sekolah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->options([
                        'SD' => 'SD',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                        'SMK' => 'SMK',
                        'TK' => 'TK',
                        'PAUD' => 'PAUD',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Negeri' => 'Negeri',
                        'Swasta' => 'Swasta',
                    ]),
                Tables\Filters\SelectFilter::make('provinsi')
                    ->options(School::distinct()->pluck('provinsi', 'provinsi')),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Download Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(new SchoolTemplateExport, 'template-data-sekolah.xlsx');
                    }),

                Tables\Actions\Action::make('importExcel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('warning')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('File Excel')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->directory('imports')
                            ->storeFileNamesIn('original_filename')
                            ->required()
                            ->helperText('Upload file Excel dengan format .xlsx atau .xls. Gunakan template yang tersedia.')
                    ])
                    ->action(function (array $data) {
                        $filePath = $data['file'];

                        if (!$filePath) {
                            Notification::make()
                                ->danger()
                                ->title('Import gagal')
                                ->body('File tidak ditemukan. Silakan upload file kembali.')
                                ->send();
                            return;
                        }

                        // Get full path from storage
                        $fullPath = storage_path('app/public/' . $filePath);

                        if (!file_exists($fullPath)) {
                            Notification::make()
                                ->danger()
                                ->title('Import gagal')
                                ->body('File tidak dapat diakses: ' . $fullPath)
                                ->send();
                            return;
                        }

                        try {
                            $import = new SchoolImport();
                            Excel::import($import, $fullPath);

                            $errors = $import->failures();
                            $errorCount = $errors->count();

                            if ($errorCount > 0) {
                                $errorMessages = $errors->map(function ($failure) {
                                    return "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                                })->take(5)->implode('\n'); // Show max 5 errors

                                $moreErrors = $errorCount > 5 ? "\n... dan " . ($errorCount - 5) . " error lainnya" : "";
                                $importedCount = $import->getImportedCount();
                                $skippedCount = $import->getSkippedCount();

                                $summary = "Berhasil: {$importedCount} data";
                                if ($skippedCount > 0) {
                                    $summary .= " | Dilewati: {$skippedCount} baris kosong";
                                }
                                $summary .= " | Error: {$errorCount} baris";

                                Notification::make()
                                    ->warning()
                                    ->title('Import selesai dengan peringatan')
                                    ->body("{$summary}\n\nContoh error:\n{$errorMessages}{$moreErrors}")
                                    ->persistent()
                                    ->send();
                            } else {
                                $importedCount = $import->getImportedCount();
                                $skippedCount = $import->getSkippedCount();

                                $summary = "Berhasil mengimport {$importedCount} data sekolah";
                                if ($skippedCount > 0) {
                                    $summary .= " ({$skippedCount} baris kosong dilewati)";
                                }

                                Notification::make()
                                    ->success()
                                    ->title('Import berhasil')
                                    ->body($summary)
                                    ->send();
                            }

                            // Clean up uploaded file
                            if (file_exists($fullPath)) {
                                unlink($fullPath);
                            }

                        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                            $failures = $e->failures();
                            $errorCount = count($failures);
                            $importedCount = $import->getImportedCount() ?? 0;
                            $skippedCount = $import->getSkippedCount() ?? 0;

                            $errorMessages = collect($failures)->map(function ($failure) {
                                return "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                            })->take(5)->implode('\n');

                            $moreErrors = $errorCount > 5 ? "\n... dan " . ($errorCount - 5) . " error lainnya" : "";

                            $summary = "Diproses: {$importedCount} berhasil";
                            if ($skippedCount > 0) {
                                $summary .= " | {$skippedCount} dilewati";
                            }
                            $summary .= " | {$errorCount} error";

                            Notification::make()
                                ->danger()
                                ->title('Import dengan validasi error')
                                ->body("{$summary}\n\nContoh error:\n{$errorMessages}{$moreErrors}")
                                ->persistent()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Import gagal')
                                ->body('Terjadi error: ' . $e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
