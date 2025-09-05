<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->description('Data dasar pengguna sistem')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama lengkap'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('contoh@email.com'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+62812345678'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Keamanan Akun')
                    ->description('Pengaturan password dan verifikasi')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->placeholder('Minimal 8 karakter')
                            ->helperText('Kosongkan jika tidak ingin mengubah password'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->same('password')
                            ->dehydrated(false)
                            ->placeholder('Ulangi password'),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->placeholder('Belum diverifikasi')
                            ->displayFormat('d/m/Y H:i')
                            ->helperText('Kosongkan jika email belum diverifikasi'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Role & Izin')
                    ->description('Pengaturan hak akses pengguna')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->placeholder('Pilih role untuk pengguna')
                            ->helperText('Pengguna dapat memiliki lebih dari satu role'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Status Akun')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Pengguna aktif dapat mengakses sistem'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Nama telah disalin'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email telah disalin')
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-m-phone')
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'assessor' => 'info',
                        'school' => 'success',
                        default => 'gray',
                    })
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Terverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => $record->email_verified_at
                        ? 'Terverifikasi: ' . $record->email_verified_at->format('d/m/Y H:i')
                        : 'Belum terverifikasi'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Belum pernah'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->nullable()
                    ->trueLabel('Terverifikasi')
                    ->falseLabel('Belum Terverifikasi')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query,
                    ),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->nullable()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\Filter::make('last_login')
                    ->label('Login Terakhir')
                    ->form([
                        Forms\Components\DatePicker::make('last_login_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('last_login_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['last_login_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('last_login_at', '>=', $date),
                            )
                            ->when(
                                $data['last_login_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('last_login_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['last_login_from'] ?? null) {
                            $indicators['last_login_from'] = 'Login dari ' . Carbon::parse($data['last_login_from'])->format('d/m/Y');
                        }
                        if ($data['last_login_until'] ?? null) {
                            $indicators['last_login_until'] = 'Login sampai ' . Carbon::parse($data['last_login_until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->before(function (User $record) {
                        // Prevent deletion of the last super admin
                        if ($record->hasRole('super_admin')) {
                            $adminCount = User::role('super_admin')->count();
                            if ($adminCount <= 1) {
                                throw new \Exception('Tidak dapat menghapus super admin terakhir.');
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->before(function ($records) {
                            // Prevent bulk deletion if it includes super admins that would leave no super admins
                            $adminRecords = $records->filter(fn ($record) => $record->hasRole('super_admin'));
                            if ($adminRecords->count() > 0) {
                                $totalAdmins = User::role('super_admin')->count();
                                if ($totalAdmins - $adminRecords->count() < 1) {
                                    throw new \Exception('Tidak dapat menghapus super admin karena akan tidak ada administrator.');
                                }
                            }
                        }),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan pengguna')
                        ->modalDescription('Apakah Anda yakin ingin mengaktifkan pengguna yang dipilih?'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            // Prevent deactivating all super admins
                            $adminRecords = $records->filter(fn ($record) => $record->hasRole('super_admin'));
                            if ($adminRecords->count() > 0) {
                                $activeAdmins = User::role('super_admin')->where('is_active', true)->count();
                                if ($activeAdmins - $adminRecords->count() < 1) {
                                    throw new \Exception('Tidak dapat menonaktifkan semua super admin.');
                                }
                            }
                            $records->each->update(['is_active' => false]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan pengguna')
                        ->modalDescription('Apakah Anda yakin ingin menonaktifkan pengguna yang dipilih?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 50 ? 'warning' : 'primary';
    }
}
