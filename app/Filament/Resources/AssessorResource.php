<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessorResource\Pages;
use App\Filament\Resources\AssessorResource\RelationManagers;
use App\Models\Assessor;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssessorResource extends Resource
{
    protected static ?string $model = Assessor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Asesor';
    protected static ?string $modelLabel = 'Asesor';
    protected static ?string $pluralModelLabel = 'Asesor';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->description('Pilih pengguna yang akan dijadikan asesor')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(User::class, 'email')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                            ])
                            ->placeholder('Pilih pengguna yang ada atau buat baru'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Data Asesor')
                    ->description('Informasi detail asesor')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_identitas')
                            ->label('NIP/Nomor Identitas')
                            ->maxLength(50)
                            ->placeholder('Nomor Induk Pegawai atau NIK'),

                        Forms\Components\TextInput::make('posisi_jabatan')
                            ->label('Posisi/Jabatan')
                            ->maxLength(100)
                            ->placeholder('Contoh: Pengawas Sekolah'),

                        Forms\Components\TextInput::make('institusi')
                            ->label('Institusi')
                            ->maxLength(255)
                            ->placeholder('Contoh: Dinas Pendidikan Kab. XYZ'),

                        Forms\Components\TextInput::make('nomor_telepon')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+62812345678'),

                        Forms\Components\TextInput::make('pengalaman_tahun')
                            ->label('Pengalaman (Tahun)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->placeholder('Contoh: 5'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Sertifikasi & Keahlian')
                    ->description('Informasi sertifikasi dan bidang keahlian')
                    ->schema([
                        Forms\Components\RichEditor::make('sertifikasi')
                            ->label('Sertifikasi')
                            ->placeholder('Daftar sertifikasi yang dimiliki')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('bidang_keahlian')
                            ->label('Bidang Keahlian')
                            ->placeholder('Bidang keahlian dan kompetensi')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Catatan & Status')
                    ->schema([
                        Forms\Components\RichEditor::make('catatan')
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan tentang asesor')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Asesor aktif dapat melakukan penilaian'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Akun Pengguna')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nomor_identitas')
                    ->label('NIP')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIP disalin!')
                    ->copyMessageDuration(1500)
                    ->placeholder('Belum diisi'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('posisi_jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->placeholder('Belum diisi'),

                Tables\Columns\TextColumn::make('institusi')
                    ->label('Institusi')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->placeholder('Belum diisi'),

                Tables\Columns\TextColumn::make('pengalaman_tahun')
                    ->label('Pengalaman')
                    ->suffix(' tahun')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Belum diisi'),

                Tables\Columns\TextColumn::make('nomor_telepon')
                    ->label('Telepon')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Belum diisi'),

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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Tables\Filters\SelectFilter::make('institusi')
                    ->label('Institusi')
                    ->options(function () {
                        return Assessor::distinct()
                            ->pluck('institusi', 'institusi')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable(),

                Tables\Filters\SelectFilter::make('pengalaman_tahun')
                    ->label('Pengalaman')
                    ->options([
                        '1-2' => '1-2 Tahun',
                        '3-5' => '3-5 Tahun',
                        '5-10' => '5-10 Tahun',
                        '10+' => '10+ Tahun',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $value): Builder {
                                return match($value) {
                                    '1-2' => $query->whereBetween('pengalaman_tahun', [1, 2]),
                                    '3-5' => $query->whereBetween('pengalaman_tahun', [3, 5]),
                                    '5-10' => $query->whereBetween('pengalaman_tahun', [5, 10]),
                                    '10+' => $query->where('pengalaman_tahun', '>=', 10),
                                    default => $query
                                };
                            }
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin menghapus asesor ini?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => true]));
                        })
                        ->requiresConfirmation()
                        ->modalDescription('Aktifkan asesor yang dipilih?'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['is_active' => false]));
                        })
                        ->requiresConfirmation()
                        ->modalDescription('Nonaktifkan asesor yang dipilih?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListAssessors::route('/'),
            'create' => Pages\CreateAssessor::route('/create'),
            'view' => Pages\ViewAssessor::route('/{record}'),
            'edit' => Pages\EditAssessor::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
