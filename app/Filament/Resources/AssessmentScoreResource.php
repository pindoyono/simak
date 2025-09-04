<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentScoreResource\Pages;
use App\Filament\Resources\AssessmentScoreResource\RelationManagers;
use App\Models\AssessmentScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssessmentScoreResource extends Resource
{
    protected static ?string $model = AssessmentScore::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Skor Penilaian';

    protected static ?string $modelLabel = 'Skor Penilaian';

    protected static ?string $pluralModelLabel = 'Skor Penilaian';

    protected static ?string $navigationGroup = 'Data Penilaian';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('school_assessment_id')
                    ->relationship('schoolAssessment', 'id', fn (Builder $query) => $query->with(['school', 'period']))
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->school->nama_sekolah} - {$record->period->name}")
                    ->required(),
                Forms\Components\Select::make('assessment_indicator_id')
                    ->relationship('assessmentIndicator', 'nama_indikator', fn (Builder $query) => $query->with('category'))
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->category->nama_kategori} - {$record->nama_indikator}")
                    ->required(),
                Forms\Components\TextInput::make('skor')
                    ->label('Skor')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(4),
                Forms\Components\Textarea::make('bukti_dukung')
                    ->label('Bukti Dukung')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('file_bukti')
                    ->label('File Bukti')
                    ->multiple()
                    ->directory('assessment-files')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolAssessment.school.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schoolAssessment.period.name')
                    ->label('Periode Penilaian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assessmentIndicator.category.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assessmentIndicator.nama_indikator')
                    ->label('Indikator')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 80) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('skor')
                    ->label('Skor')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->size('lg')
                    ->weight(FontWeight::Bold)
                    ->color(fn (string $state): string => match ((int) $state) {
                        4 => 'success',
                        3 => 'info',
                        2 => 'warning',
                        1 => 'danger',
                        0 => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ((int) $state) {
                        4 => '4 - Sangat Baik',
                        3 => '3 - Baik',
                        2 => '2 - Cukup',
                        1 => '1 - Kurang',
                        0 => '0 - Tidak Ada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\IconColumn::make('file_bukti')
                    ->label('File Bukti')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->file_bukti)),
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
                Tables\Filters\SelectFilter::make('schoolAssessment.school_id')
                    ->label('Sekolah')
                    ->relationship('schoolAssessment.school', 'nama_sekolah'),
                Tables\Filters\SelectFilter::make('schoolAssessment.assessment_period_id')
                    ->label('Periode Penilaian')
                    ->relationship('schoolAssessment.period', 'name'),
                Tables\Filters\SelectFilter::make('assessmentIndicator.assessment_category_id')
                    ->label('Kategori')
                    ->relationship('assessmentIndicator.category', 'nama_kategori'),
                Tables\Filters\SelectFilter::make('skor')
                    ->label('Skor')
                    ->options([
                        '4' => 'Sangat Baik (4)',
                        '3' => 'Baik (3)',
                        '2' => 'Cukup (2)',
                        '1' => 'Kurang (1)',
                        '0' => 'Tidak Ada (0)',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAssessmentScores::route('/'),
            'create' => Pages\CreateAssessmentScore::route('/create'),
            'view' => Pages\ViewAssessmentScore::route('/{record}'),
            'edit' => Pages\EditAssessmentScore::route('/{record}/edit'),
        ];
    }
}
