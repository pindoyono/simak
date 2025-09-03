<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentPeriodResource\Pages;
use App\Filament\Resources\AssessmentPeriodResource\RelationManagers;
use App\Models\AssessmentPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssessmentPeriodResource extends Resource
{
    protected static ?string $model = AssessmentPeriod::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Assessment Periods';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_periode')
                    ->label('Nama Periode')
                    ->required()
                    ->maxLength(255)
                    ->unique(AssessmentPeriod::class, 'nama_periode', ignoreRecord: true),
                Forms\Components\TextInput::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->required()
                    ->placeholder('contoh: 2024/2025')
                    ->maxLength(255),
                Forms\Components\Select::make('semester')
                    ->label('Semester')
                    ->options([
                        'Ganjil' => 'Ganjil',
                        'Genap' => 'Genap',
                        'Tahunan' => 'Tahunan',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->after('tanggal_mulai')
                    ->rule('after:tanggal_mulai'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'aktif' => 'Aktif',
                        'selesai' => 'Selesai',
                    ])
                    ->required()
                    ->default('draft')
                    ->native(false),
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_periode')
                    ->label('Nama Periode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),
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
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->date('d/m/Y')
                    ->sortable(),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAssessmentPeriods::route('/'),
            'create' => Pages\CreateAssessmentPeriod::route('/create'),
            'edit' => Pages\EditAssessmentPeriod::route('/{record}/edit'),
        ];
    }
}
