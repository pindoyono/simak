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
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Data Sekolah';
    protected static ?string $modelLabel = 'Sekolah';
    protected static ?string $pluralModelLabel = 'Data Sekolah';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Sekolah')
                    ->description('Data identitas dan informasi dasar sekolah')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Forms\Components\TextInput::make('nama_sekolah')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: SDN 01 Jakarta Selatan')
                            ->helperText('Nama lengkap sekolah sesuai dengan dokumen resmi'),

                        Forms\Components\TextInput::make('npsn')
                            ->label('NPSN (Nomor Pokok Sekolah Nasional)')
                            ->required()
                            ->unique(School::class, 'npsn', ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('Contoh: 20109879')
                            ->helperText('NPSN harus unik dan sesuai dengan data Kemendikbud')
                            ->rule('regex:/^[0-9]{8}$/')
                            ->validationMessages([
                                'regex' => 'NPSN harus terdiri dari 8 digit angka',
                            ]),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jalan, Nomor, RT/RW, Kelurahan')
                            ->columnSpanFull()
                            ->helperText('Alamat lengkap sekolah termasuk jalan, nomor, RT/RW'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Lokasi Administratif')
                    ->description('Pembagian wilayah administratif sekolah')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('kecamatan')
                            ->label('Kecamatan')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Kebayoran Baru'),

                        Forms\Components\TextInput::make('kabupaten_kota')
                            ->label('Kabupaten/Kota')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Jakarta Selatan'),

                        Forms\Components\TextInput::make('provinsi')
                            ->label('Provinsi')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: DKI Jakarta'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Karakteristik Sekolah')
                    ->description('Jenjang pendidikan dan status sekolah')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang Pendidikan')
                            ->required()
                            ->options([
                                'PAUD' => 'PAUD (Pendidikan Anak Usia Dini)',
                                'TK' => 'TK (Taman Kanak-kanak)',
                                'SD' => 'SD (Sekolah Dasar)',
                                'SMP' => 'SMP (Sekolah Menengah Pertama)',
                                'SMA' => 'SMA (Sekolah Menengah Atas)',
                                'SMK' => 'SMK (Sekolah Menengah Kejuruan)',
                            ])
                            ->native(false)
                            ->placeholder('Pilih jenjang pendidikan'),

                        Forms\Components\Select::make('status')
                            ->label('Status Sekolah')
                            ->required()
                            ->options([
                                'Negeri' => 'Negeri',
                                'Swasta' => 'Swasta',
                            ])
                            ->native(false)
                            ->placeholder('Pilih status sekolah'),

                        Forms\Components\TextInput::make('kepala_sekolah')
                            ->label('Nama Kepala Sekolah')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Dr. Ahmad Suryadi, M.Pd'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Informasi Kontak')
                    ->description('Data kontak dan komunikasi sekolah')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Forms\Components\TextInput::make('telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Contoh: (021) 12345678 atau 0812-3456-7890')
                            ->helperText('Nomor telepon sekolah yang dapat dihubungi'),

                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Contoh: sdn01@jakarta.sch.id')
                            ->helperText('Email resmi sekolah'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Operasional')
                    ->description('Status aktif dan operasional sekolah')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Aktifkan jika sekolah masih beroperasi')
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
                Tables\Columns\TextColumn::make('nama_sekolah')
                    ->label('Nama Sekolah')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable()
                    ->tooltip('Klik untuk menyalin nama sekolah')
                    ->description(fn (School $record): string => $record->npsn ? "NPSN: {$record->npsn}" : 'NPSN tidak tersedia')
                    ->wrap(),

                Tables\Columns\TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PAUD' => 'gray',
                        'TK' => 'yellow',
                        'SD' => 'blue',
                        'SMP' => 'green',
                        'SMA' => 'purple',
                        'SMK' => 'orange',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Negeri' => 'success',
                        'Swasta' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat_lengkap')
                    ->label('Alamat')
                    ->state(function (School $record): string {
                        $alamat = collect([
                            $record->alamat,
                            $record->kecamatan,
                            $record->kabupaten_kota,
                            $record->provinsi
                        ])->filter()->join(', ');

                        return $alamat ?: 'Alamat tidak lengkap';
                    })
                    ->searchable(['alamat', 'kecamatan', 'kabupaten_kota', 'provinsi'])
                    ->limit(50)
                    ->tooltip(function (School $record): string {
                        return collect([
                            $record->alamat,
                            $record->kecamatan,
                            $record->kabupaten_kota,
                            $record->provinsi
                        ])->filter()->join(', ') ?: 'Alamat tidak lengkap';
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('kepala_sekolah')
                    ->label('Kepala Sekolah')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->iconColor('primary')
                    ->copyable()
                    ->limit(30)
                    ->tooltip(fn (School $record): string => $record->kepala_sekolah ?: 'Nama kepala sekolah tidak tersedia'),

                Tables\Columns\TextColumn::make('kontak')
                    ->label('Kontak')
                    ->state(function (School $record): string {
                        $kontak = [];
                        if ($record->telepon) {
                            $kontak[] = "📞 {$record->telepon}";
                        }
                        if ($record->email) {
                            $kontak[] = "✉️ {$record->email}";
                        }
                        return $kontak ? implode(' | ', $kontak) : 'Kontak tidak tersedia';
                    })
                    ->searchable(['telepon', 'email'])
                    ->html()
                    ->copyable()
                    ->limit(40)
                    ->tooltip(function (School $record): string {
                        $kontak = [];
                        if ($record->telepon) {
                            $kontak[] = "Telepon: {$record->telepon}";
                        }
                        if ($record->email) {
                            $kontak[] = "Email: {$record->email}";
                        }
                        return $kontak ? implode("\n", $kontak) : 'Kontak tidak tersedia';
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->tooltip(fn (School $record): string => $record->is_active ? 'Sekolah aktif beroperasi' : 'Sekolah tidak aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray')
                    ->tooltip(fn (School $record): string => "Dibuat pada: " . $record->created_at->format('d F Y, H:i:s')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray')
                    ->tooltip(fn (School $record): string => "Terakhir diperbarui: " . $record->updated_at->format('d F Y, H:i:s')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->label('Jenjang Pendidikan')
                    ->options([
                        'PAUD' => 'PAUD (Pendidikan Anak Usia Dini)',
                        'TK' => 'TK (Taman Kanak-kanak)',
                        'SD' => 'SD (Sekolah Dasar)',
                        'SMP' => 'SMP (Sekolah Menengah Pertama)',
                        'SMA' => 'SMA (Sekolah Menengah Atas)',
                        'SMK' => 'SMK (Sekolah Menengah Kejuruan)',
                    ])
                    ->multiple()
                    ->placeholder('Semua Jenjang'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Sekolah')
                    ->options([
                        'Negeri' => 'Negeri',
                        'Swasta' => 'Swasta',
                    ])
                    ->placeholder('Semua Status'),

                Tables\Filters\SelectFilter::make('provinsi')
                    ->label('Provinsi')
                    ->options(function (): array {
                        return School::whereNotNull('provinsi')
                            ->distinct()
                            ->pluck('provinsi', 'provinsi')
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('Semua Provinsi'),

                Tables\Filters\SelectFilter::make('kabupaten_kota')
                    ->label('Kabupaten/Kota')
                    ->options(function (): array {
                        return School::whereNotNull('kabupaten_kota')
                            ->distinct()
                            ->pluck('kabupaten_kota', 'kabupaten_kota')
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('Semua Kabupaten/Kota'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Operasional')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->tooltip('Lihat detail sekolah'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->tooltip('Edit data sekolah'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->tooltip('Hapus data sekolah')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Sekolah')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data sekolah ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Sekolah Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data sekolah yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->modalCancelActionLabel('Batal'),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Sekolah Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin mengaktifkan semua sekolah yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Aktifkan')
                        ->modalCancelActionLabel('Batal')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Sekolah Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menonaktifkan semua sekolah yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Nonaktifkan')
                        ->modalCancelActionLabel('Batal')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('downloadTemplate')
                    ->label('Unduh Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->tooltip('Unduh template Excel untuk import data sekolah')
                    ->action(function () {
                        return Excel::download(new SchoolTemplateExport, 'template-data-sekolah.xlsx');
                    }),

                Tables\Actions\Action::make('importExcel')
                    ->label('Impor Data')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('warning')
                    ->tooltip('Impor data sekolah dari file Excel')
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
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->tooltip('Lihat detail sekolah'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->tooltip('Edit data sekolah'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->tooltip('Hapus data sekolah')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Sekolah')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data sekolah ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Sekolah Pertama')
                    ->tooltip('Mulai dengan menambahkan data sekolah pertama'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->groups([
                Tables\Grouping\Group::make('provinsi')
                    ->label('Provinsi')
                    ->collapsible(),
                Tables\Grouping\Group::make('jenjang')
                    ->label('Jenjang Pendidikan')
                    ->collapsible(),
                Tables\Grouping\Group::make('status')
                    ->label('Status Sekolah')
                    ->collapsible(),
            ])
            ->paginated([10, 25, 50, 100, 'all'])
            ->poll('30s')
            ->searchOnBlur()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->deferLoading()
            ->extremePaginationLinks();
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
            'view' => Pages\ViewSchool::route('/{record}'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
