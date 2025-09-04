<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentIndicatorResource\Pages;
use App\Filament\Resources\AssessmentIndicatorResource\RelationManagers;
use App\Models\AssessmentIndicator;
use App\Models\AssessmentCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssessmentIndicatorResource extends Resource
{
    protected static ?string $model = AssessmentIndicator::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'Assessment Indicators';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('assessment_category_id')
                    ->label('Assessment Category')
                    ->options(AssessmentCategory::pluck('nama_kategori', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('nama_indikator')
                    ->label('Nama Indikator')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('bobot_indikator')
                    ->label('Bobot Indikator (%)')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(999.99)
                    ->default(0),
                Forms\Components\Textarea::make('kriteria_penilaian')
                    ->label('Kriteria Penilaian')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('skor_maksimal')
                    ->label('Skor Maksimal')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(4),
                Forms\Components\TextInput::make('urutan')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_indikator')
                    ->label('Nama Indikator')
                    ->searchable()
                    ->wrap()
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('bobot_indikator')
                    ->label('Bobot (%)')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('skor_maksimal')
                    ->label('Skor Max')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('assessment_category_id')
                    ->label('Kategori Assessment')
                    ->options(AssessmentCategory::pluck('nama_kategori', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
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
            'index' => Pages\ListAssessmentIndicators::route('/'),
            'create' => Pages\CreateAssessmentIndicator::route('/create'),
            'edit' => Pages\EditAssessmentIndicator::route('/{record}/edit'),
        ];
    }
}
