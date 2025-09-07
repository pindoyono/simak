<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentScoreResource\Pages;
use App\Models\AssessmentScore;
use App\Models\AssessmentIndicator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Split;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split as FormSplit;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class AssessmentScoreResource extends Resource
{
    protected static ?string $model = AssessmentScore::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Skor Penilaian';

    protected static ?string $modelLabel = 'Skor Penilaian';

    protected static ?string $pluralModelLabel = 'Skor Penilaian';

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSplit::make([
                    FormSection::make('Informasi Penilaian')
                        ->description('Data utama penilaian skor')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            Grid::make(2)
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
                                        ->afterStateUpdated(function (Forms\Set $set) {
                                            $set('assessment_indicator_id', null);
                                        })
                                        ->placeholder('Pilih penilaian sekolah'),

                                    Forms\Components\Select::make('assessment_indicator_id')
                                        ->label('Indikator Penilaian')
                                        ->relationship('assessmentIndicator', 'nama_indikator')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                            if ($state) {
                                                $indicator = AssessmentIndicator::find($state);
                                                if ($indicator) {
                                                    $set('skor_maksimal_info', $indicator->skor_maksimal ?? 4);
                                                    $set('bobot_info', $indicator->bobot_indikator ?? 0);
                                                }
                                            }
                                        })
                                        ->placeholder('Pilih indikator penilaian'),
                                ]),

                            Grid::make(3)
                                ->schema([
                                    Forms\Components\Placeholder::make('skor_maksimal_info')
                                        ->label('Skor Maksimal')
                                        ->content(fn (Forms\Get $get) => $get('skor_maksimal_info') ?? '-'),

                                    Forms\Components\Placeholder::make('bobot_info')
                                        ->label('Bobot (%)')
                                        ->content(fn (Forms\Get $get) => $get('bobot_info') ? $get('bobot_info') . '%' : '-'),

                                    Forms\Components\DateTimePicker::make('tanggal_penilaian')
                                        ->label('Tanggal Penilaian')
                                        ->required()
                                        ->default(now())
                                        ->displayFormat('d/m/Y H:i')
                                        ->seconds(false),
                                ]),
                        ]),

                    FormSection::make('Penilaian Skor')
                        ->description('Input skor dan perhitungan')
                        ->icon('heroicon-m-calculator')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('skor')
                                        ->label('Skor')
                                        ->numeric()
                                        ->required()
                                        ->step(0.01)
                                        ->minValue(0)
                                        ->maxValue(4)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                            if ($state && $get('skor_maksimal_info')) {
                                                $maxScore = $get('skor_maksimal_info');
                                                $percentage = ($state / $maxScore) * 100;
                                                $set('persentase_preview', round($percentage, 2));

                                                $grade = match (true) {
                                                    $percentage >= 85 => 'Sangat Baik',
                                                    $percentage >= 70 => 'Baik',
                                                    $percentage >= 55 => 'Cukup',
                                                    default => 'Kurang',
                                                };
                                                $set('grade_preview', $grade);
                                            }
                                        })
                                        ->placeholder('Masukkan skor'),

                                    Grid::make(1)
                                        ->schema([
                                            Forms\Components\Placeholder::make('persentase_preview')
                                                ->label('Persentase')
                                                ->content(fn (Forms\Get $get) =>
                                                    $get('persentase_preview') ? $get('persentase_preview') . '%' : '-'
                                                ),

                                            Forms\Components\Placeholder::make('grade_preview')
                                                ->label('Nilai')
                                                ->content(fn (Forms\Get $get) => $get('grade_preview') ?? '-'),
                                        ])
                                ]),
                        ]),
                ])
                ->from('lg'),

                FormSection::make('Bukti dan Dokumentasi')
                    ->description('Bukti pendukung dan catatan penilaian')
                    ->icon('heroicon-m-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('bukti_dukung')
                            ->label('Bukti Pendukung')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Deskripsikan bukti pendukung yang tersedia...'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Penilaian')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Tambahkan catatan atau komentar terkait penilaian...'),

                        Forms\Components\FileUpload::make('file_bukti')
                            ->label('File Bukti')
                            ->multiple()
                            ->directory('assessment-files')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240)
                            ->columnSpanFull()
                            ->helperText('Upload file bukti (PDF, gambar). Maksimal 10MB per file.'),
                    ]),
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

                TextColumn::make('assessmentIndicator.nama_indikator')
                    ->label('Indikator')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('skor')
                    ->label('Skor')
                    ->numeric(2)
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 3.5 => 'success',
                        $state >= 2.5 => 'info',
                        $state >= 1.5 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('persentase')
                    ->label('Persentase')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 85 => 'success',
                        $state >= 70 => 'info',
                        $state >= 55 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('grade')
                    ->label('Nilai')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
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
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Sangat Baik' => 'Sangat Baik',
                        'Baik' => 'Baik',
                        'Cukup' => 'Cukup',
                        'Kurang' => 'Kurang',
                        // Backward compatibility - convert old grades to new format
                        'A' => 'Sangat Baik',
                        'B' => 'Baik',
                        'C' => 'Cukup',
                        'D' => 'Kurang',
                        default => 'Tidak Dinilai',
                    }),

                TextColumn::make('file_count')
                    ->label('File')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state . ' file'),

                TextColumn::make('tanggal_penilaian')
                    ->label('Tanggal Penilaian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('grade')
                    ->label('Nilai')
                    ->options([
                        'Sangat Baik' => 'Sangat Baik (≥85%)',
                        'Baik' => 'Baik (70-84%)',
                        'Cukup' => 'Cukup (55-69%)',
                        'Kurang' => 'Kurang (<55%)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->byGrade($data['value']);
                    }),

                SelectFilter::make('school_assessment_id')
                    ->label('Penilaian Sekolah')
                    ->relationship('schoolAssessment', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->school->nama_sekolah . ' - ' . $record->period->nama_periode;
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('high_performing')
                    ->label('Performa Tinggi (≥85%)')
                    ->query(fn (Builder $query): Builder => $query->highPerforming())
                    ->toggle(),

                Filter::make('low_performing')
                    ->label('Performa Rendah (<55%)')
                    ->query(fn (Builder $query): Builder => $query->lowPerforming())
                    ->toggle(),

                Filter::make('tanggal_penilaian')
                    ->label('Tanggal Penilaian')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_penilaian', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_penilaian', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('export_selected')
                        ->label('Export Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            Notification::make()
                                ->title('Export berhasil!')
                                ->body('Data skor penilaian berhasil diekspor.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('tanggal_penilaian', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make('Informasi Penilaian')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            TextEntry::make('schoolAssessment.school.nama_sekolah')
                                ->label('Sekolah')
                                ->weight(FontWeight::SemiBold),

                            TextEntry::make('assessmentIndicator.nama_indikator')
                                ->label('Indikator')
                                ->columnSpanFull(),

                            TextEntry::make('tanggal_penilaian')
                                ->label('Tanggal Penilaian')
                                ->dateTime('d/m/Y H:i'),
                        ]),

                    Section::make('Hasil Penilaian')
                        ->icon('heroicon-m-chart-bar-square')
                        ->schema([
                            TextEntry::make('skor')
                                ->label('Skor')
                                ->numeric(2)
                                ->badge()
                                ->color('primary'),

                            TextEntry::make('persentase')
                                ->label('Persentase')
                                ->formatStateUsing(fn ($state) => $state . '%')
                                ->badge(),

                            TextEntry::make('grade_label')
                                ->label('Nilai')
                                ->badge(),
                        ]),
                ])->from('lg'),

                Section::make('Bukti dan Dokumentasi')
                    ->icon('heroicon-m-document-text')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('bukti_dukung')
                            ->label('Bukti Pendukung')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada bukti pendukung'),

                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada catatan'),

                        TextEntry::make('file_bukti')
                            ->label('File Bukti')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                if (!is_array($state) || empty($state)) {
                                    return 'Tidak ada file';
                                }
                                return count($state) . ' file terlampir';
                            })
                            ->badge(),
                    ]),
            ]);
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'schoolAssessment.school.nama_sekolah',
            'assessmentIndicator.nama_indikator',
            'bukti_dukung',
            'catatan',
        ];
    }
}
