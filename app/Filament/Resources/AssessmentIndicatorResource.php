<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentIndicatorResource\Pages;
use App\Filament\Resources\AssessmentIndicatorResource\RelationManagers;
use App\Models\AssessmentIndicator;
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
                Forms\Components\TextInput::make('assessment_category_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nama_indikator')
                    ->required(),
                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('bobot_indikator')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('kriteria_penilaian')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('skor_maksimal')
                    ->required()
                    ->numeric()
                    ->default(4),
                Forms\Components\TextInput::make('urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assessment_category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_indikator')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bobot_indikator')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('skor_maksimal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('urutan')
                    ->numeric()
                    ->sortable(),
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
                //
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
            'index' => Pages\ListAssessmentIndicators::route('/'),
            'create' => Pages\CreateAssessmentIndicator::route('/create'),
            'edit' => Pages\EditAssessmentIndicator::route('/{record}/edit'),
        ];
    }
}
