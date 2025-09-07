<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentIndicatorResource\Pages;
use App\Filament\Resources\AssessmentIndicatorResource\RelationManagers;
use App\Models\AssessmentIndicator;
use App\Models\AssessmentCategory;
use App\Exports\AssessmentIndicatorTemplateExport;
use App\Exports\AssessmentIndicatorExport;
use App\Imports\AssessmentIndicatorImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class AssessmentIndicatorResource extends Resource
{
    protected static ?string $model = AssessmentIndicator::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'Indikator Asesmen';
    protected static ?string $modelLabel = 'Indikator Asesmen';
    protected static ?string $pluralModelLabel = 'Indikator Asesmen';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Komponen & Kategori')
                    ->description('Pilih kategori komponen asesmen untuk indikator')
                    ->icon('heroicon-o-folder-open')
                    ->schema([
                        Forms\Components\Select::make('assessment_category_id')
                            ->label('Kategori Asesmen')
                            ->relationship('category', 'nama_kategori')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih kategori asesmen')
                            ->helperText('Kategori komponen yang akan dinilai dalam indikator ini')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                return "{$record->komponen} - {$record->nama_kategori}";
                            }),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Informasi Indikator')
                    ->description('Data detail indikator asesmen')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('nama_indikator')
                            ->label('Nama Indikator')
                            ->required()
                            ->rows(3)
                            ->placeholder('Contoh: Kelengkapan dokumen kurikulum dan silabus pembelajaran')
                            ->helperText('Nama lengkap dan jelas dari indikator yang akan dinilai')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Indikator')
                            ->rows(4)
                            ->placeholder('Jelaskan secara detail tentang indikator ini, apa saja yang dinilai, dan bagaimana cara penilaiannya...')
                            ->helperText('Penjelasan mendalam mengenai indikator dan cara penilaiannya')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('kriteria_penilaian')
                            ->label('Kriteria Penilaian')
                            ->placeholder('Tuliskan kriteria penilaian detail untuk setiap skor...')
                            ->helperText('Kriteria penilaian untuk setiap level skor (0-4)')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                            ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Pengaturan Skor & Bobot')
                    ->description('Konfigurasi skor maksimal dan bobot indikator')
                    ->icon('heroicon-o-scale')
                    ->schema([
                        Forms\Components\TextInput::make('skor_maksimal')
                            ->label('Skor Maksimal')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(4)
                            ->placeholder('4')
                            ->helperText('Skor maksimal yang dapat diperoleh (umumnya 4)'),

                        Forms\Components\TextInput::make('bobot_indikator')
                            ->label('Bobot Indikator (%)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->placeholder('25.50')
                            ->helperText('Bobot indikator dalam kategori (maksimal 100%)')
                            ->suffix('%'),

                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->placeholder('1')
                            ->helperText('Urutan tampil indikator dalam kategori'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Status Indikator')
                    ->description('Pengaturan status aktif indikator')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Indikator aktif akan tampil dalam formulir asesmen')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.komponen')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SISWA' => 'blue',
                        'GURU' => 'green',
                        'KINERJA GURU' => 'yellow',
                        'MANAGEMENT KEPALA SEKOLAH' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'SISWA' => 'SISWA',
                            'GURU' => 'GURU',
                            'KINERJA GURU' => 'KINERJA GURU',
                            'MANAGEMENT KEPALA SEKOLAH' => 'KEPALA SEKOLAH',
                            default => $state,
                        };
                    }),

                Tables\Columns\TextColumn::make('category.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('nama_indikator')
                    ->label('Nama Indikator')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 80 ? $state : null;
                    })
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Nama indikator disalin!')
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

                Tables\Columns\TextColumn::make('bobot_indikator')
                    ->label('Bobot (%)')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),

                Tables\Columns\TextColumn::make('skor_maksimal')
                    ->label('Skor Max')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('scores_count')
                    ->label('Jumlah Penilaian')
                    ->counts('scores')
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => $state . ' Penilaian'),

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
                Tables\Filters\SelectFilter::make('assessment_category_id')
                    ->label('Kategori Asesmen')
                    ->relationship('category', 'nama_kategori')
                    ->searchable()
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('komponen')
                    ->label('Komponen Utama')
                    ->options([
                        'SISWA' => 'SISWA',
                        'GURU' => 'GURU',
                        'KINERJA GURU' => 'KINERJA GURU',
                        'MANAGEMENT KEPALA SEKOLAH' => 'KEPALA SEKOLAH',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas(
                                'category',
                                fn (Builder $query) => $query->where('komponen', $value)
                            )
                        );
                    }),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Tables\Filters\Filter::make('bobot_tinggi')
                    ->label('Bobot Tinggi (≥10%)')
                    ->query(fn (Builder $query): Builder => $query->where('bobot_indikator', '>=', 10))
                    ->toggle(),

                Tables\Filters\Filter::make('banyak_penilaian')
                    ->label('Banyak Penilaian (≥5)')
                    ->query(fn (Builder $query): Builder => $query->withCount('scores')->having('scores_count', '>=', 5))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Unduh Template Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $fileName = 'template-indikator-asesmen-' . now()->format('Y-m-d') . '.xlsx';
                        return Excel::download(new AssessmentIndicatorTemplateExport, $fileName);
                    })
                    ->tooltip('Unduh template untuk import indikator asesmen'),

                Tables\Actions\Action::make('exportData')
                    ->label('Export Data Indikator')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function () {
                        $fileName = 'data-indikator-asesmen-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
                        return Excel::download(new AssessmentIndicatorExport, $fileName);
                    })
                    ->tooltip('Export semua data indikator asesmen ke Excel'),

                Tables\Actions\Action::make('importExcel')
                    ->label('Import Data Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('warning')
                    ->form([
                        Forms\Components\Section::make('Import Indikator Asesmen')
                            ->description('Upload file Excel untuk mengimport data indikator asesmen')
                            ->schema([
                                Forms\Components\FileUpload::make('file')
                                    ->label('File Excel')
                                    ->required()
                                    ->acceptedFileTypes([
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-excel'
                                    ])
                                    ->maxSize(10240) // 10MB
                                    ->disk('private')
                                    ->directory('imports/indicators')
                                    ->visibility('private')
                                    ->helperText('Format yang didukung: .xlsx, .xls (Maksimal 10MB)')
                                    ->uploadingMessage('Mengupload file...')
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('import_info')
                                    ->label('Informasi Import')
                                    ->content('
                                        <div class="text-sm text-gray-600">
                                            <p><strong>Petunjuk Import:</strong></p>
                                            <ul class="list-disc list-inside mt-2 space-y-1">
                                                <li>Gunakan template Excel yang telah disediakan</li>
                                                <li>Pastikan ID kategori sesuai dengan yang ada di sistem</li>
                                                <li>Bobot indikator dalam format decimal (0-100)</li>
                                                <li>Skor maksimal antara 1-10</li>
                                                <li>Data yang error akan dilewati secara otomatis</li>
                                            </ul>
                                        </div>
                                    ')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->action(function (array $data) {
                        $filePath = $data['file'];

                        if (!$filePath) {
                            Notification::make()
                                ->danger()
                                ->title('Import Gagal')
                                ->body('File tidak ditemukan. Silakan upload file kembali.')
                                ->send();
                            return;
                        }

                        try {
                            // Get the full path to the uploaded file
                            $fullPath = Storage::disk('private')->path($filePath);

                            // Check if file exists
                            if (!file_exists($fullPath)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Import Gagal')
                                    ->body('File tidak dapat diakses. Path: ' . $fullPath . '. Silakan coba upload kembali.')
                                    ->send();
                                return;
                            }

                            // Check file permissions
                            if (!is_readable($fullPath)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Import Gagal')
                                    ->body('File tidak dapat dibaca. Periksa permissions file.')
                                    ->send();
                                return;
                            }

                            $import = new AssessmentIndicatorImport();
                            Excel::import($import, $fullPath);

                            $summary = "✅ Import berhasil!\n";
                            $summary .= "Total indikator asesmen berhasil diimport";

                            Notification::make()
                                ->success()
                                ->title('Import Berhasil')
                                ->body($summary)
                                ->send();

                            // Clean up uploaded file
                            Storage::disk('private')->delete($filePath);

                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Import Gagal')
                                ->body('Terjadi error: ' . $e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    })
                    ->modalWidth('lg')
                    ->tooltip('Import indikator asesmen dari file Excel'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->color('info'),

                Tables\Actions\EditAction::make()
                    ->label('Edit'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Indikator Asesmen')
                    ->modalDescription('Apakah Anda yakin ingin menghapus indikator asesmen ini? Tindakan ini akan menghapus semua penilaian terkait.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Indikator Asesmen Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua indikator asesmen yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->modalCancelActionLabel('Batal'),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => true]));

                            Notification::make()
                                ->success()
                                ->title('Status Diperbarui')
                                ->body(count($records) . ' indikator asesmen berhasil diaktifkan.')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Indikator Terpilih')
                        ->modalDescription('Mengaktifkan semua indikator asesmen yang dipilih.')
                        ->modalSubmitActionLabel('Ya, Aktifkan')
                        ->modalCancelActionLabel('Batal'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => false]));

                            Notification::make()
                                ->success()
                                ->title('Status Diperbarui')
                                ->body(count($records) . ' indikator asesmen berhasil dinonaktifkan.')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Indikator Terpilih')
                        ->modalDescription('Menonaktifkan semua indikator asesmen yang dipilih.')
                        ->modalSubmitActionLabel('Ya, Nonaktifkan')
                        ->modalCancelActionLabel('Batal'),

                    Tables\Actions\BulkAction::make('changeCategory')
                        ->label('Ubah Kategori')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('assessment_category_id')
                                ->label('Kategori Baru')
                                ->relationship('category', 'nama_kategori')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->placeholder('Pilih kategori baru')
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    return "{$record->komponen} - {$record->nama_kategori}";
                                }),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['assessment_category_id' => $data['assessment_category_id']]);
                            });

                            Notification::make()
                                ->success()
                                ->title('Kategori Diperbarui')
                                ->body(count($records) . ' indikator berhasil dipindah ke kategori baru.')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ubah Kategori Indikator')
                        ->modalDescription('Memindahkan semua indikator yang dipilih ke kategori baru.')
                        ->modalSubmitActionLabel('Ya, Ubah')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ])
            ->defaultSort('urutan', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListAssessmentIndicators::route('/'),
            'create' => Pages\CreateAssessmentIndicator::route('/create'),
            'view' => Pages\ViewAssessmentIndicator::route('/{record}'),
            'edit' => Pages\EditAssessmentIndicator::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('is_active', true)->count();
        return $count >= 20 ? 'success' : ($count >= 10 ? 'warning' : 'danger');
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['category']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_indikator', 'deskripsi', 'category.nama_kategori'];
    }
}
