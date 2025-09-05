<?php

namespace App\Filament\Pages;

use App\Models\AssessmentScore;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentCategory;
use App\Models\School;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Pages\Page;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class AssessmentReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.assessment-report-new';
    protected static ?string $title = 'Laporan Penilaian';
    protected static ?string $navigationLabel = 'Laporan Penilaian';
    protected static ?string $navigationGroup = 'Penilaian';
    protected static ?int $navigationSort = 4;

    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->description('Pilih kriteria untuk menampilkan data penilaian')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('assessment_period_id')
                                    ->label('Periode Penilaian')
                                    ->options(AssessmentPeriod::all()->pluck('nama_periode', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),

                                Select::make('assessment_category_id')
                                    ->label('Kategori Penilaian')
                                    ->options(AssessmentCategory::all()->pluck('nama_kategori', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),

                                Select::make('school_id')
                                    ->label('Sekolah')
                                    ->options(School::all()->pluck('nama_sekolah', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),
                            ]),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AssessmentScore::query()->with(['schoolAssessment.school', 'assessmentIndicator.category', 'schoolAssessment.period']))
            ->columns([
                TextColumn::make('schoolAssessment.school.nama_sekolah')
                    ->label('Sekolah')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('schoolAssessment.period.nama_periode')
                    ->label('Periode')
                    ->sortable(),

                TextColumn::make('assessmentIndicator.category.nama_kategori')
                    ->label('Kategori')
                    ->sortable(),

                TextColumn::make('assessmentIndicator.nama_indikator')
                    ->label('Indikator')
                    ->wrap()
                    ->limit(50),

                TextColumn::make('nilai')
                    ->label('Nilai')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '4' => 'success',
                        '3' => 'info',
                        '2' => 'warning',
                        '1' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '4' => 'Sangat Baik (4)',
                        '3' => 'Baik (3)',
                        '2' => 'Cukup (2)',
                        '1' => 'Kurang (1)',
                        default => 'Tidak Ada (0)',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('assessment_period_id')
                    ->label('Periode')
                    ->relationship('schoolAssessment.period', 'nama_periode'),

                SelectFilter::make('assessment_category_id')
                    ->label('Kategori')
                    ->relationship('assessmentIndicator.category', 'nama_kategori'),

                SelectFilter::make('school_id')
                    ->label('Sekolah')
                    ->relationship('schoolAssessment.school', 'nama_sekolah'),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $data = $this->data;

                if (!empty($data['assessment_period_id'])) {
                    $query->whereHas('schoolAssessment', function ($q) use ($data) {
                        $q->where('assessment_period_id', $data['assessment_period_id']);
                    });
                }

                if (!empty($data['assessment_category_id'])) {
                    $query->whereHas('assessmentIndicator', function ($q) use ($data) {
                        $q->where('assessment_category_id', $data['assessment_category_id']);
                    });
                }

                if (!empty($data['school_id'])) {
                    $query->whereHas('schoolAssessment', function ($q) use ($data) {
                        $q->where('school_id', $data['school_id']);
                    });
                }

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->extremePaginationLinks()
            ->actions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (AssessmentScore $record): string => 
                        route('filament.admin.resources.assessment-scores.view', ['record' => $record])
                    ),
            ]);
    }

    // Helper method to reset table when form changes
    protected function resetTable(): void
    {
        $this->resetPage();
    }

    public function getHeading(): string
    {
        return 'Laporan Penilaian';
    }
}
