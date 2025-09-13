<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentCategoryResource\Pages;
use App\Filament\Resources\AssessmentCategoryResource\RelationManagers;
use App\Models\AssessmentCategory;
use App\Exports\AssessmentCategoryTemplateExport;
use App\Exports\AssessmentCategoryExport;
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
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Komponen Asesmen';
    protected static ?string $modelLabel = 'Komponen Asesmen';
    protected static ?string $pluralModelLabel = 'Komponen Asesmen';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Komponen')
                    ->description('Data identitas dan informasi dasar komponen asesmen')
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        Forms\Components\Select::make('komponen')
                            ->label('Komponen Utama')
                            ->required()
                            ->options([
                                'KEPALA SEKOLAH' => 'KEPALA SEKOLAH - Kepemimpinan dan Pengelolaan Sekolah',
                                'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)' => 'PELANGGAN - Siswa, Orang Tua dan Masyarakat',
                                'PENGUKURAN, ANALISIS DAN MANAGAMEN PENGETAHUAN' => 'PENGUKURAN & ANALISIS - Managemen Pengetahuan',
                                'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)' => 'TENAGA KERJA - Pendidik dan Kependidikan',
                                'PROSES' => 'PROSES - Pembelajaran dan Operasional',
                                'HASIL PRODUK DAN/ATAU LAYANAN' => 'HASIL PRODUK - Layanan Pendidikan',
                            ])
                            ->native(false)
                            ->placeholder('Pilih komponen utama asesmen')
                            ->helperText('Komponen utama SIMAK-PM yang akan dinilai dalam asesmen'),

                        Forms\Components\TextInput::make('nama_kategori')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Standar Isi dan Kurikulum')
                            ->helperText('Nama spesifik kategori dalam komponen'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Kategori')
                            ->rows(4)
                            ->placeholder('Jelaskan secara detail tentang kategori asesmen ini...')
                            ->helperText('Deskripsi mendalam mengenai kategori dan kriteria penilaiannya')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan Penilaian')
                    ->description('Konfigurasi bobot dan urutan penilaian')
                    ->icon('heroicon-o-scale')
                    ->schema([
                        Forms\Components\TextInput::make('bobot_penilaian')
                            ->label('Bobot Penilaian (%)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->placeholder('25.50')
                            ->helperText('Bobot kategori dalam total penilaian (maksimal 100%)')
                            ->suffix('%'),

                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->placeholder('1')
                            ->helperText('Urutan tampil kategori dalam formulir asesmen'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Kategori aktif akan tampil dalam formulir asesmen')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('komponen')
                    ->label('Komponen Utama')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'KEPALA SEKOLAH' => 'purple',
                        'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)' => 'blue',
                        'PENGUKURAN, ANALISIS DAN MANAGAMEN PENGETAHUAN' => 'orange',
                        'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)' => 'green',
                        'PROSES' => 'yellow',
                        'HASIL PRODUK DAN/ATAU LAYANAN' => 'red',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'KEPALA SEKOLAH' => 'KEPALA SEKOLAH',
                            'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)' => 'PELANGGAN',
                            'PENGUKURAN, ANALISIS DAN MANAGAMEN PENGETAHUAN' => 'PENGUKURAN & ANALISIS',
                            'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)' => 'TENAGA KERJA',
                            'PROSES' => 'PROSES',
                            'HASIL PRODUK DAN/ATAU LAYANAN' => 'HASIL PRODUK',
                            default => $state,
                        };
                    }),

                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Nama kategori disalin!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->placeholder('Belum ada deskripsi')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bobot_penilaian')
                    ->label('Bobot (%)')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),

                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('indicators_count')
                    ->label('Jumlah Indikator')
                    ->counts('indicators')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state . ' Indikator'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

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
                    ->label('Komponen Utama')
                    ->options([
                        'KEPALA SEKOLAH' => 'KEPALA SEKOLAH',
                        'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)' => 'PELANGGAN',
                        'PENGUKURAN, ANALISIS DAN MANAGAMEN PENGETAHUAN' => 'PENGUKURAN & ANALISIS',
                        'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)' => 'TENAGA KERJA',
                        'PROSES' => 'PROSES',
                        'HASIL PRODUK DAN/ATAU LAYANAN' => 'HASIL PRODUK',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Tables\Filters\Filter::make('bobot_tinggi')
                    ->label('Bobot Tinggi (≥20%)')
                    ->query(fn (Builder $query): Builder => $query->where('bobot_penilaian', '>=', 20))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Unduh Template Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->tooltip('Unduh template untuk import komponen asesmen')
                    ->action(function () {
                        $fileName = 'template-komponen-asesmen-' . now()->format('Y-m-d') . '.xlsx';
                        return Excel::download(new AssessmentCategoryTemplateExport, $fileName);
                    }),

                Tables\Actions\Action::make('exportData')
                    ->label('Export Data Komponen')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->tooltip('Export semua data komponen asesmen ke Excel')
                    ->action(function () {
                        $fileName = 'data-komponen-asesmen-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
                        return Excel::download(new AssessmentCategoryExport, $fileName);
                    }),

                Tables\Actions\Action::make('importExcel')
                    ->label('Import Data Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('warning')
                    ->tooltip('Import komponen asesmen dari file Excel')
                    ->form([
                        Forms\Components\Section::make('Import Komponen Asesmen')
                            ->description('Upload file Excel untuk mengimport data komponen asesmen')
                            ->schema([
                                Forms\Components\FileUpload::make('file')
                                    ->label('File Excel')
                                    ->required()
                                    ->acceptedFileTypes([
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-excel'
                                    ])
                                    ->maxSize(5120) // 5MB
                                    ->disk('private')
                                    ->directory('imports/categories')
                                    ->visibility('private')
                                    ->helperText('Format yang didukung: .xlsx, .xls (Maksimal 5MB)')
                                    ->uploadingMessage('Mengupload file...')
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('import_info')
                                    ->label('Informasi Import')
                                    ->content('
                                        <div class="text-sm text-gray-600">
                                            <p><strong>Petunjuk Import:</strong></p>
                                            <ul class="list-disc list-inside mt-2 space-y-1">
                                                <li>Gunakan template Excel yang telah disediakan</li>
                                                <li>Pastikan komponen sesuai dengan pilihan yang tersedia</li>
                                                <li>Bobot penilaian dalam format decimal (0-100)</li>
                                                <li>Data yang error akan dilewati secara otomatis</li>
                                            </ul>
                                        </div>
                                    ')
                                    ->columnSpanFull(),
                            ])
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
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->icon('heroicon-s-eye')
                    ->color('info')
                    ->tooltip('Lihat detail komponen asesmen'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-s-pencil')
                    ->color('warning')
                    ->tooltip('Edit komponen asesmen'),

                Tables\Actions\ReplicateAction::make()
                    ->label('Duplikasi')
                    ->icon('heroicon-s-document-duplicate')
                    ->color('gray')
                    ->tooltip('Duplikasi komponen asesmen')
                    ->beforeReplicaSaved(function (AssessmentCategory $replica): void {
                        $replica->komponen = $replica->komponen . ' (Copy)';
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-s-trash')
                    ->color('danger')
                    ->tooltip('Hapus komponen asesmen')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalHeading('Hapus Komponen Asesmen')
                    ->modalDescription('Apakah Anda yakin ingin menghapus komponen asesmen ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportSelected')
                        ->label('Export Terpilih')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            $fileName = 'komponen-asesmen-terpilih-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
                            return Excel::download(new AssessmentCategoryExport($records), $fileName);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Export Komponen Terpilih')
                        ->modalDescription('Export komponen asesmen yang dipilih ke file Excel?'),

                    Tables\Actions\BulkAction::make('bulkChangeKomponen')
                        ->label('Ubah Komponen')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('komponen')
                                ->label('Komponen Baru')
                                ->options([
                                    'SISWA' => 'Siswa',
                                    'GURU' => 'Guru',
                                    'KINERJA GURU' => 'Kinerja Guru',
                                    'MANAGEMENT KEPALA SEKOLAH' => 'Management Kepala Sekolah',
                                ])
                                ->required()
                                ->placeholder('Pilih komponen baru')
                        ])
                        ->action(function ($records, $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['komponen' => $data['komponen']]);
                            });

                            Notification::make()
                                ->title('Berhasil!')
                                ->body('Komponen berhasil diubah untuk ' . count($records) . ' kategori asesmen.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ubah Komponen')
                        ->modalDescription('Ubah komponen untuk semua kategori asesmen yang dipilih?'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalHeading('Hapus Komponen Asesmen')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua komponen asesmen yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
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
            'view' => Pages\ViewAssessmentCategory::route('/{record}'),
            'edit' => Pages\EditAssessmentCategory::route('/{record}/edit'),
        ];
    }
}
