<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentReportResource\Pages;
use App\Models\AssessmentReport;
use App\Models\SchoolAssessment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\Split as FormSplit;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;

class AssessmentReportResource extends Resource
{
    protected static ?string $model = AssessmentReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Penilaian';

    protected static ?string $modelLabel = 'Laporan Penilaian';

    protected static ?string $pluralModelLabel = 'Laporan Penilaian';

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSplit::make([
                    FormSection::make('Informasi Laporan')
                        ->description('Data dasar laporan penilaian')
                        ->icon('heroicon-m-document-text')
                        ->schema([
                            FormGrid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('school_assessment_id')
                                        ->label('Penilaian Sekolah')
                                        ->relationship('schoolAssessment', 'id')
                                        ->getOptionLabelFromRecordUsing(function ($record) {
                                            return $record->school->nama_sekolah . ' - ' . $record->period->nama_periode;
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                                            if ($state) {
                                                $assessment = SchoolAssessment::with(['school', 'period'])->find($state);
                                                if ($assessment) {
                                                    $set('judul_laporan', 'Laporan Penilaian ' . $assessment->school->nama_sekolah . ' - ' . $assessment->period->nama_periode);
                                                    $set('skor_total', $assessment->total_score ?? 0);
                                                    $set('grade_akhir', $assessment->grade);
                                                }
                                            }
                                        })
                                        ->placeholder('Pilih penilaian sekolah'),

                                    Forms\Components\Hidden::make('dibuat_oleh')
                                        ->default(\Filament\Facades\Filament::auth()->id()),
                                ]),

                            Forms\Components\TextInput::make('judul_laporan')
                                ->label('Judul Laporan')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Masukkan judul laporan penilaian'),
                        ]),

                    FormSection::make('Status dan Persetujuan')
                        ->description('Informasi status dan persetujuan')
                        ->icon('heroicon-m-check-badge')
                        ->schema([
                            FormGrid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('status_laporan')
                                        ->label('Status Laporan')
                                        ->options([
                                            'draft' => 'Draft',
                                            'review' => 'Sedang Review',
                                            'final' => 'Final',
                                            'published' => 'Dipublikasikan',
                                        ])
                                        ->default('draft')
                                        ->required(),

                                    Forms\Components\Toggle::make('is_public')
                                        ->label('Publik')
                                        ->helperText('Centang jika laporan dapat diakses publik')
                                        ->default(false),
                                ]),

                            FormGrid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('skor_total')
                                        ->label('Skor Total')
                                        ->numeric()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                                            if ($state) {
                                                $grade = match (true) {
                                                    $state >= 85 => 'Sangat Baik',
                                                    $state >= 70 => 'Baik',
                                                    $state >= 55 => 'Cukup',
                                                    default => 'Kurang',
                                                };
                                                $set('grade_akhir', $grade);
                                            }
                                        }),

                                    Forms\Components\Select::make('grade_akhir')
                                        ->label('Nilai Akhir')
                                        ->options([
                                            'Sangat Baik' => 'Sangat Baik',
                                            'Baik' => 'Baik',
                                            'Cukup' => 'Cukup',
                                            'Kurang' => 'Kurang',
                                        ])
                                        ->placeholder('Pilih nilai'),
                                ]),
                        ]),
                ])
                ->from('lg'),

                Tabs::make('Konten Laporan')
                    ->tabs([
                        Tabs\Tab::make('Ringkasan Eksekutif')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Forms\Components\RichEditor::make('ringkasan_eksekutif')
                                    ->label('Ringkasan Eksekutif')
                                    ->required()
                                    ->columnSpanFull()
                                    ->placeholder('Tulis ringkasan eksekutif laporan penilaian...')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline',
                                        'bulletList', 'orderedList',
                                        'redo', 'undo',
                                    ]),
                            ]),

                        Tabs\Tab::make('Temuan dan Analisis')
                            ->icon('heroicon-m-magnifying-glass')
                            ->schema([
                                Forms\Components\RichEditor::make('temuan_utama')
                                    ->label('Temuan Utama')
                                    ->columnSpanFull()
                                    ->placeholder('Deskripsikan temuan-temuan utama dari penilaian...')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline',
                                        'bulletList', 'orderedList',
                                        'redo', 'undo',
                                    ]),
                            ]),

                        Tabs\Tab::make('Rekomendasi')
                            ->icon('heroicon-m-light-bulb')
                            ->schema([
                                Forms\Components\RichEditor::make('rekomendasi')
                                    ->label('Rekomendasi')
                                    ->columnSpanFull()
                                    ->placeholder('Berikan rekomendasi berdasarkan hasil penilaian...')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline',
                                        'bulletList', 'orderedList',
                                        'redo', 'undo',
                                    ]),
                            ]),

                        Tabs\Tab::make('Kesimpulan')
                            ->icon('heroicon-m-check-circle')
                            ->schema([
                                Forms\Components\RichEditor::make('kesimpulan')
                                    ->label('Kesimpulan')
                                    ->columnSpanFull()
                                    ->placeholder('Tulis kesimpulan dari laporan penilaian...')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline',
                                        'bulletList', 'orderedList',
                                        'redo', 'undo',
                                    ]),
                            ]),

                        Tabs\Tab::make('Lampiran')
                            ->icon('heroicon-m-paper-clip')
                            ->schema([
                                Forms\Components\FileUpload::make('file_lampiran')
                                    ->label('File Lampiran')
                                    ->multiple()
                                    ->directory('assessment-reports')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(25600)
                                    ->columnSpanFull()
                                    ->helperText('Upload file lampiran (PDF, gambar). Maksimal 25MB per file.'),

                                Forms\Components\Textarea::make('catatan_reviewer')
                                    ->label('Catatan Reviewer')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->placeholder('Catatan dari reviewer atau catatan internal...'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolAssessment.school.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('judul_laporan')
                    ->label('Judul Laporan')
                    ->searchable()
                    ->limit(40)
                    ->weight(FontWeight::Medium),

                TextColumn::make('status_laporan')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'final' => 'info',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'final' => 'Final',
                        'published' => 'Dipublikasikan',
                        default => 'Tidak Diketahui',
                    }),

                TextColumn::make('skor_total')
                    ->label('Skor Total')
                    ->numeric(2)
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 85 => 'success',
                        $state >= 70 => 'info',
                        $state >= 55 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('grade_akhir')
                    ->label('Nilai')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (?string $state): string => match ($state) {
                        'Sangat Baik' => 'success',
                        'Baik' => 'info',
                        'Cukup' => 'warning',
                        'Kurang' => 'danger',
                        // Backward compatibility
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'Sangat Baik' => 'Sangat Baik',
                        'Baik' => 'Baik',
                        'Cukup' => 'Cukup',
                        'Kurang' => 'Kurang',
                        // Backward compatibility
                        'A' => 'Sangat Baik',
                        'B' => 'Baik',
                        'C' => 'Cukup',
                        'D' => 'Kurang',
                        default => 'Belum Dinilai',
                    }),

                TextColumn::make('pembuatLaporan.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('file_count')
                    ->label('Lampiran')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state . ' file'),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Publik')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_laporan')
                    ->label('Status Laporan')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Sedang Review',
                        'final' => 'Final',
                        'published' => 'Dipublikasikan',
                    ]),

                SelectFilter::make('grade_akhir')
                    ->label('Nilai')
                    ->options([
                        'Sangat Baik' => 'Sangat Baik (≥85%)',
                        'Baik' => 'Baik (70-84%)',
                        'Cukup' => 'Cukup (55-69%)',
                        'Kurang' => 'Kurang (<55%)',
                        // Backward compatibility
                        'A' => 'A - Sangat Baik (≥85%)',
                        'B' => 'B - Baik (70-84%)',
                        'C' => 'C - Cukup (55-69%)',
                        'D' => 'D - Kurang (<55%)',
                    ]),

                Filter::make('published')
                    ->label('Sudah Dipublikasikan')
                    ->query(fn (Builder $query): Builder => $query->published())
                    ->toggle(),

                Filter::make('with_files')
                    ->label('Memiliki Lampiran')
                    ->query(fn (Builder $query): Builder => $query->withFiles())
                    ->toggle(),
            ])
            ->actions([
                Action::make('publish')
                    ->label('Publikasikan')
                    ->icon('heroicon-m-globe-alt')
                    ->color('success')
                    ->visible(fn (AssessmentReport $record): bool => $record->can_be_published)
                    ->requiresConfirmation()
                    ->action(function (AssessmentReport $record) {
                        $record->publish();
                        Notification::make()
                            ->title('Laporan Dipublikasikan!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->visible(fn (AssessmentReport $record): bool => $record->canEdit()),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (AssessmentReport $record): bool => $record->canDelete()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
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
                Split::make([
                    Section::make('Informasi Laporan')
                        ->icon('heroicon-m-document-text')
                        ->schema([
                            TextEntry::make('schoolAssessment.school.nama_sekolah')
                                ->label('Sekolah')
                                ->weight(FontWeight::SemiBold),

                            TextEntry::make('judul_laporan')
                                ->label('Judul Laporan')
                                ->columnSpanFull()
                                ->weight(FontWeight::Medium),
                        ]),

                    Section::make('Status dan Penilaian')
                        ->icon('heroicon-m-chart-bar-square')
                        ->schema([
                            TextEntry::make('status_label')
                                ->label('Status Laporan')
                                ->badge(),

                            TextEntry::make('skor_total')
                                ->label('Skor Total')
                                ->numeric(2)
                                ->badge(),

                            TextEntry::make('grade_label')
                                ->label('Grade Akhir')
                                ->badge(),
                        ]),
                ])->from('lg'),

                Section::make('Konten Laporan')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextEntry::make('ringkasan_eksekutif')
                            ->label('Ringkasan Eksekutif')
                            ->columnSpanFull()
                            ->html(),

                        TextEntry::make('temuan_utama')
                            ->label('Temuan Utama')
                            ->columnSpanFull()
                            ->html(),

                        TextEntry::make('rekomendasi')
                            ->label('Rekomendasi')
                            ->columnSpanFull()
                            ->html(),

                        TextEntry::make('kesimpulan')
                            ->label('Kesimpulan')
                            ->columnSpanFull()
                            ->html(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssessmentReports::route('/'),
            'create' => Pages\CreateAssessmentReport::route('/create'),
            'view' => Pages\ViewAssessmentReport::route('/{record}'),
            'edit' => Pages\EditAssessmentReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'judul_laporan',
            'schoolAssessment.school.nama_sekolah',
            'ringkasan_eksekutif',
        ];
    }
}
