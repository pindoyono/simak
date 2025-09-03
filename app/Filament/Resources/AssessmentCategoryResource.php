<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentCategoryResource\Pages;
use App\Filament\Resources\AssessmentCategoryResource\RelationManagers;
use App\Models\AssessmentCategory;
use App\Exports\AssessmentCategoryTemplateExport;
use App\Imports\AssessmentCategoryImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;

class AssessmentCategoryResource extends Resource
{
    protected static ?string $model = AssessmentCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Assessment Categories';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori Asesmen')
                    ->description('Masukkan detail kategori asesmen')
                    ->schema([
                        Forms\Components\TextInput::make('komponen')
                            ->label('Komponen')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Aspek Kepemimpinan'),

                        Forms\Components\TextInput::make('nama_kategori')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Visi dan Misi Sekolah'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi detail tentang kategori asesmen ini')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan Penilaian')
                    ->description('Atur bobot dan urutan kategori')
                    ->schema([
                        Forms\Components\TextInput::make('bobot_penilaian')
                            ->label('Bobot Penilaian (%)')
                            ->required()
                            ->numeric()
                            ->min(0.01)
                            ->max(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->placeholder('0.00'),

                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan')
                            ->required()
                            ->numeric()
                            ->min(1)
                            ->default(1)
                            ->placeholder('1'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Aktifkan kategori ini untuk digunakan dalam asesmen'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('komponen')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bobot_penilaian')
                    ->label('Bobot (%)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('komponen')
                    ->label('Komponen')
                    ->options(function () {
                        return AssessmentCategory::distinct()->pluck('komponen', 'komponen')->toArray();
                    }),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Download Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $fileName = 'template-kategori-asesmen-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

                        return Excel::download(new AssessmentCategoryTemplateExport, $fileName);
                    }),

                Tables\Actions\Action::make('importExcel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('primary')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('File Excel')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->maxSize(5120) // 5MB
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private')
                            ->helperText('Format yang didukung: .xlsx, .xls (Maksimal 5MB)')
                            ->uploadingMessage('Mengupload file...')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        // Get the uploaded file path
                        $uploadedFile = $data['file'];
                        $fullPath = storage_path('app/private/' . $uploadedFile);
                        
                        // Verify file exists
                        if (!file_exists($fullPath)) {
                            Notification::make()
                                ->danger()
                                ->title('Import gagal')
                                ->body('File tidak dapat diakses: ' . $fullPath)
                                ->send();
                            return;
                        }

                        try {
                            $import = new AssessmentCategoryImport();
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
                                    ->body($summary . "\n\nError details:\n" . $errorMessages . $moreErrors)
                                    ->send();
                            } else {
                                $importedCount = $import->getImportedCount();
                                $skippedCount = $import->getSkippedCount();

                                if ($importedCount > 0) {
                                    $summary = "Berhasil mengimport {$importedCount} kategori asesmen";
                                    if ($skippedCount > 0) {
                                        $summary .= " | Dilewati: {$skippedCount} baris kosong";
                                    }

                                    Notification::make()
                                        ->success()
                                        ->title('Import berhasil!')
                                        ->body($summary)
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->warning()
                                        ->title('Tidak ada data yang diimport')
                                        ->body("Semua baris dilewati ({$skippedCount} baris kosong)")
                                        ->send();
                                }
                            }

                            // Clean up the uploaded file
                            if (file_exists($fullPath)) {
                                unlink($fullPath);
                            }

                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Import gagal')
                                ->body('Error: ' . $e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('urutan', 'asc');
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
            'index' => Pages\ListAssessmentCategories::route('/'),
            'create' => Pages\CreateAssessmentCategory::route('/create'),
            'edit' => Pages\EditAssessmentCategory::route('/{record}/edit'),
        ];
    }
}
