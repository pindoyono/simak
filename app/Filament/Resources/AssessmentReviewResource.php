<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentReviewResource\Pages;
use App\Filament\Resources\AssessmentReviewResource\RelationManagers;
use App\Models\AssessmentReview;
use App\Models\User;
use App\Models\SchoolAssessment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssessmentReviewResource extends Resource
{
    protected static ?string $model = AssessmentReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Assessment Reviews';
    protected static ?string $navigationGroup = 'Assessment Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('school_assessment_id')
                    ->relationship('schoolAssessment', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->school->nama_sekolah . ' - ' . $record->period->nama_periode),

                Forms\Components\Select::make('reviewer_id')
                    ->relationship('reviewer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Menunggu Review',
                        'in_progress' => 'Sedang Direview',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision_needed' => 'Perlu Revisi',
                    ])
                    ->required()
                    ->default('pending'),

                Forms\Components\Select::make('grade_recommendation')
                    ->label('Grade Recommendation')
                    ->options([
                        'A' => 'A - Sangat Baik (≥85%)',
                        'B' => 'B - Baik (70-84%)',
                        'C' => 'C - Cukup (55-69%)',
                        'D' => 'D - Kurang (<55%)',
                    ])
                    ->visible(fn ($get) => in_array($get('status'), ['approved', 'revision_needed'])),

                Forms\Components\TextInput::make('score_adjustment')
                    ->label('Score Adjustment')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(-100)
                    ->maxValue(100)
                    ->suffix('%')
                    ->helperText('Adjustment in percentage (-100% to +100%)')
                    ->visible(fn ($get) => $get('status') === 'revision_needed'),

                Forms\Components\Textarea::make('review_notes')
                    ->label('Review Notes')
                    ->placeholder('Detailed review notes and recommendations...')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('comments')
                    ->label('Comments')
                    ->placeholder('Additional comments...')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('reviewed_at')
                    ->label('Review Date')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolAssessment.school.nama_sekolah')
                    ->label('School')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('schoolAssessment.period.nama_periode')
                    ->label('Period')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'info' => ['in_progress', 'submitted'],
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'warning' => 'revision_needed',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Menunggu Review',
                        'in_progress' => 'Sedang Direview',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision_needed' => 'Perlu Revisi',
                        'submitted' => 'Telah Disubmit',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('grade_recommendation')
                    ->label('Grade')
                    ->colors([
                        'success' => 'A',
                        'info' => 'B',
                        'warning' => 'C',
                        'danger' => 'D',
                    ])
                    ->default('N/A'),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Review Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Review',
                        'in_progress' => 'Sedang Direview',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision_needed' => 'Perlu Revisi',
                    ]),

                Tables\Filters\SelectFilter::make('grade_recommendation')
                    ->label('Grade')
                    ->options([
                        'A' => 'A - Sangat Baik',
                        'B' => 'B - Baik',
                        'C' => 'C - Cukup',
                        'D' => 'D - Kurang',
                    ]),

                Tables\Filters\Filter::make('reviewed_this_month')
                    ->label('Reviewed This Month')
                    ->query(fn ($query) => $query->whereBetween('reviewed_at', [
                        now()->startOfMonth(),
                        now()->endOfMonth(),
                    ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_at' => now(),
                        ]);
                    })
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'in_progress'])),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_at' => now(),
                        ]);
                    })
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'in_progress'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Bulk Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'approved',
                                    'reviewed_at' => now(),
                                ]);
                            });
                        }),
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
            'index' => Pages\ListAssessmentReviews::route('/'),
            'edit' => Pages\EditAssessmentReview::route('/{record}/edit'),
        ];
    }
}
