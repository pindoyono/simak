<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentPeriodResource\Pages;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssessmentPeriodResource extends Resource
{
    protected static ?string $model = AssessmentPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Periode Asesmen';

    protected static ?string $modelLabel = 'Periode Asesmen';

    protected static ?string $pluralModelLabel = 'Periode Asesmen';

    protected static ?string $navigationGroup = 'Manajemen Asesmen';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Periode')
                    ->description('Atur detail periode asesmen')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nama_periode')
                                    ->label('Nama Periode')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Asesmen Tengah Semester')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('tahun_ajaran')
                                    ->label('Tahun Ajaran')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: 2024/2025')
                                    ->mask('9999/9999')
                                    ->rule('regex:/^\d{4}\/\d{4}$/')
                                    ->helperText('Format: YYYY/YYYY'),
                                Forms\Components\Select::make('semester')
                                    ->label('Semester')
                                    ->options([
                                        'Ganjil' => 'Ganjil',
                                        'Genap' => 'Genap',
                                        'Tahunan' => 'Tahunan',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->searchable(),
                            ]),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi periode asesmen...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Jadwal Periode')
                    ->description('Tentukan tanggal mulai dan selesai periode')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                        if ($state) {
                                            $tanggalMulai = Carbon::parse($state);
                                            $tanggalSelesai = $tanggalMulai->copy()->addDays(30);
                                            $set('tanggal_selesai', $tanggalSelesai->format('Y-m-d'));
                                        }
                                    }),
                                Forms\Components\DatePicker::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->after('tanggal_mulai')
                                    ->rule('after:tanggal_mulai'),
                            ]),
                        Forms\Components\Placeholder::make('durasi_info')
                            ->label('Durasi Periode')
                            ->content(function (Forms\Get $get): string {
                                $tanggalMulai = $get('tanggal_mulai');
                                $tanggalSelesai = $get('tanggal_selesai');

                                if ($tanggalMulai && $tanggalSelesai) {
                                    $mulai = Carbon::parse($tanggalMulai);
                                    $selesai = Carbon::parse($tanggalSelesai);
                                    $durasi = $mulai->diffInDays($selesai) + 1;

                                    return "Durasi: {$durasi} hari";
                                }

                                return 'Pilih tanggal untuk melihat durasi';
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pengaturan Status')
                    ->description('Atur status dan pengaturan periode')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'aktif' => 'Aktif',
                                        'selesai' => 'Selesai',
                                    ])
                                    ->required()
                                    ->default('draft')
                                    ->native(false)
                                    ->live()
                                    ->helperText(function (Forms\Get $get): string {
                                        return match ($get('status')) {
                                            'draft' => 'Periode dalam tahap persiapan',
                                            'aktif' => 'Periode sedang berlangsung',
                                            'selesai' => 'Periode telah berakhir',
                                            default => 'Pilih status periode',
                                        };
                                    }),
                                Forms\Components\Toggle::make('is_default')
                                    ->label('Periode Default')
                                    ->helperText('Jadikan sebagai periode default untuk asesmen baru')
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_periode')
                    ->label('Nama Periode')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                Tables\Columns\TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Ganjil' => 'info',
                        'Genap' => 'success',
                        'Tahunan' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('durasi')
                    ->label('Durasi')
                    ->state(function (AssessmentPeriod $record): string {
                        return $record->durasi . ' hari';
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status_waktu_label')
                    ->label('Status Waktu')
                    ->badge()
                    ->color(function (AssessmentPeriod $record): string {
                        return match ($record->status_waktu) {
                            'akan_datang' => 'warning',
                            'berlangsung' => 'success',
                            'berakhir' => 'gray',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'aktif' => 'success',
                        'selesai' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'aktif' => 'Aktif',
                        'selesai' => 'Selesai',
                    }),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn (bool $state): string => $state ? 'Periode Default' : 'Bukan Default'),
                Tables\Columns\TextColumn::make('school_assessments_count')
                    ->label('Jumlah Asesmen')
                    ->counts('schoolAssessments')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
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
                Tables\Filters\SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(function (): array {
                        return AssessmentPeriod::distinct()
                            ->pluck('tahun_ajaran', 'tahun_ajaran')
                            ->toArray();
                    })
                    ->searchable(),
                Tables\Filters\SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        'Ganjil' => 'Ganjil',
                        'Genap' => 'Genap',
                        'Tahunan' => 'Tahunan',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'aktif' => 'Aktif',
                        'selesai' => 'Selesai',
                    ]),
                Tables\Filters\SelectFilter::make('status_waktu')
                    ->label('Status Waktu')
                    ->options([
                        'akan_datang' => 'Akan Datang',
                        'berlangsung' => 'Sedang Berlangsung',
                        'berakhir' => 'Sudah Berakhir',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        $today = Carbon::today();

                        return match ($data['value']) {
                            'akan_datang' => $query->where('tanggal_mulai', '>', $today),
                            'berlangsung' => $query->where('tanggal_mulai', '<=', $today)
                                                  ->where('tanggal_selesai', '>=', $today),
                            'berakhir' => $query->where('tanggal_selesai', '<', $today),
                            default => $query,
                        };
                    }),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Periode Default')
                    ->placeholder('Semua Periode')
                    ->trueLabel('Hanya Default')
                    ->falseLabel('Bukan Default'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\Action::make('setDefault')
                        ->label('Jadikan Default')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->visible(fn (AssessmentPeriod $record): bool => !$record->is_default)
                        ->requiresConfirmation()
                        ->modalHeading('Jadikan Periode Default')
                        ->modalDescription('Apakah Anda yakin ingin menjadikan periode ini sebagai default?')
                        ->action(function (AssessmentPeriod $record) {
                            AssessmentPeriod::where('is_default', true)->update(['is_default' => false]);
                            $record->update(['is_default' => true]);
                        })
                        ->successNotificationTitle('Periode berhasil dijadikan default'),
                    Tables\Actions\Action::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn (AssessmentPeriod $record): bool => $record->status !== 'aktif')
                        ->requiresConfirmation()
                        ->action(fn (AssessmentPeriod $record) => $record->update(['status' => 'aktif']))
                        ->successNotificationTitle('Periode berhasil diaktifkan'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn (AssessmentPeriod $record) =>
                                $record->update(['status' => 'aktif'])
                            );
                        })
                        ->successNotificationTitle('Periode yang dipilih berhasil diaktifkan'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Periode')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('nama_periode')
                                    ->label('Nama Periode')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('tahun_ajaran')
                                    ->label('Tahun Ajaran')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('semester')
                                    ->label('Semester')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Ganjil' => 'info',
                                        'Genap' => 'success',
                                        'Tahunan' => 'warning',
                                    }),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'aktif' => 'success',
                                        'selesai' => 'danger',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'draft' => 'Draft',
                                        'aktif' => 'Aktif',
                                        'selesai' => 'Selesai',
                                    }),
                            ]),
                        Infolists\Components\TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Tidak ada deskripsi')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Jadwal dan Durasi')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar'),
                                Infolists\Components\TextEntry::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->date('d F Y')
                                    ->icon('heroicon-o-calendar'),
                                Infolists\Components\TextEntry::make('durasi')
                                    ->label('Durasi')
                                    ->formatStateUsing(fn (AssessmentPeriod $record): string =>
                                        $record->durasi . ' hari'
                                    )
                                    ->icon('heroicon-o-clock'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('status_waktu_label')
                                    ->label('Status Waktu')
                                    ->badge()
                                    ->color(function (AssessmentPeriod $record): string {
                                        return match ($record->status_waktu) {
                                            'akan_datang' => 'warning',
                                            'berlangsung' => 'success',
                                            'berakhir' => 'gray',
                                            default => 'gray',
                                        };
                                    }),
                                Infolists\Components\TextEntry::make('hari_tersisa')
                                    ->label('Hari Tersisa')
                                    ->formatStateUsing(function (AssessmentPeriod $record): string {
                                        $hari = $record->hari_tersisa;
                                        if ($record->status_waktu === 'akan_datang') {
                                            return $hari . ' hari lagi dimulai';
                                        } elseif ($record->status_waktu === 'berlangsung') {
                                            return $hari . ' hari lagi berakhir';
                                        }
                                        return 'Sudah berakhir';
                                    })
                                    ->color(function (AssessmentPeriod $record): string {
                                        return match ($record->status_waktu) {
                                            'akan_datang' => 'warning',
                                            'berlangsung' => 'success',
                                            'berakhir' => 'gray',
                                            default => 'gray',
                                        };
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Pengaturan')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\IconEntry::make('is_default')
                                    ->label('Periode Default')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-star')
                                    ->falseIcon('heroicon-o-star')
                                    ->trueColor('warning')
                                    ->falseColor('gray'),
                                Infolists\Components\TextEntry::make('school_assessments_count')
                                    ->label('Jumlah Asesmen')
                                    ->state(fn (AssessmentPeriod $record): int =>
                                        $record->schoolAssessments()->count()
                                    )
                                    ->badge()
                                    ->color('primary'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-plus'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Diperbarui')
                                    ->dateTime('d F Y, H:i')
                                    ->icon('heroicon-o-pencil'),
                            ]),
                    ])
                    ->collapsible(),
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
            'index' => Pages\ListAssessmentPeriods::route('/'),
            'create' => Pages\CreateAssessmentPeriod::route('/create'),
            'view' => Pages\ViewAssessmentPeriod::route('/{record}'),
            'edit' => Pages\EditAssessmentPeriod::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_periode', 'tahun_ajaran', 'semester'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tahun Ajaran' => $record->tahun_ajaran,
            'Semester' => $record->semester,
            'Status' => match ($record->status) {
                'draft' => 'Draft',
                'aktif' => 'Aktif',
                'selesai' => 'Selesai',
            },
        ];
    }
}
