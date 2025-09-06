<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolAssessmentResource\Pages;
use App\Models\SchoolAssessment;
use App\Models\School;
use App\Models\AssessmentPeriod;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;

class SchoolAssessmentResource extends Resource
{
    protected static ?string $model = SchoolAssessment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Penilaian Sekolah';

    protected static ?string $modelLabel = 'Penilaian Sekolah';

    protected static ?string $pluralModelLabel = 'Penilaian Sekolah';

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penilaian')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('school_id')
                                    ->label('Sekolah')
                                    ->relationship('school', 'nama_sekolah')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('assessment_period_id')
                                    ->label('Periode Penilaian')
                                    ->relationship('period', 'nama_periode')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('assessor_id')
                                    ->label('Asesor')
                                    ->relationship('assessor', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\DatePicker::make('tanggal_asesmen')
                                    ->label('Tanggal Penilaian')
                                    ->required()
                                    ->default(now()),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('total_score')
                                    ->label('Total Skor')
                                    ->numeric()
                                    ->step(0.01),

                                Forms\Components\Select::make('grade')
                                    ->label('Grade')
                                    ->options([
                                        'A' => 'A - Sangat Baik',
                                        'B' => 'B - Baik',
                                        'C' => 'C - Cukup',
                                        'D' => 'D - Kurang',
                                    ]),
                            ]),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'reviewed' => 'Reviewed',
                                'approved' => 'Approved',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->placeholder('Catatan atau komentar untuk penilaian ini...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('period.nama_periode')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assessor.name')
                    ->label('Asesor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_asesmen')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_score')
                    ->label('Skor')
                    ->numeric(2)
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'info' => 'submitted',
                        'warning' => 'reviewed',
                        'success' => 'approved',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('Sekolah')
                    ->relationship('school', 'nama_sekolah')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('assessment_period_id')
                    ->label('Periode')
                    ->relationship('period', 'nama_periode')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                    ]),

                Tables\Filters\Filter::make('tanggal_asesmen')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_asesmen', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_asesmen', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
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
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function canCreate(): bool
    {
        return false; // Disable create - assessments should only be created via Assessment Wizard
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Penilaian')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('school.nama_sekolah')
                                    ->label('Sekolah'),

                                Infolists\Components\TextEntry::make('period.nama_periode')
                                    ->label('Periode Penilaian'),

                                Infolists\Components\TextEntry::make('assessor.name')
                                    ->label('Asesor'),

                                Infolists\Components\TextEntry::make('tanggal_asesmen')
                                    ->label('Tanggal Penilaian')
                                    ->date('d F Y'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Hasil Penilaian')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_score')
                                    ->label('Total Skor')
                                    ->numeric(2),

                                Infolists\Components\TextEntry::make('grade')
                                    ->label('Grade')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'A' => 'success',
                                        'B' => 'info',
                                        'C' => 'warning',
                                        'D' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Status & Catatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'draft' => 'secondary',
                                'submitted' => 'info',
                                'reviewed' => 'warning',
                                'approved' => 'success',
                                default => 'secondary',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'reviewed' => 'Reviewed',
                                'approved' => 'Approved',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull(),
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
            'index' => Pages\ListSchoolAssessments::route('/'),
            'view' => Pages\ViewSchoolAssessment::route('/{record}'),
            'edit' => Pages\EditSchoolAssessment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'warning' : 'primary';
    }
}
