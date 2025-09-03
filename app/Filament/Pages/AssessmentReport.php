<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AssessmentScore;
use App\Models\School;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentCategory;
use Illuminate\Database\Eloquent\Builder;

class AssessmentReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penilaian';
    protected static ?string $title = 'Laporan Penilaian';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament-panels::pages.dashboard';

    public ?array $data = [];
    public ?int $selectedSchool = null;
    public ?int $selectedPeriod = null;
    public ?int $selectedCategory = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->description('Pilih filter untuk menampilkan data penilaian')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('selectedSchool')
                                    ->label('Sekolah')
                                    ->placeholder('Semua Sekolah')
                                    ->options(School::pluck('nama_sekolah', 'id'))
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),

                                Select::make('selectedPeriod')
                                    ->label('Periode Penilaian')
                                    ->placeholder('Semua Periode')
                                    ->options(AssessmentPeriod::pluck('name', 'id'))
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),

                                Select::make('selectedCategory')
                                    ->label('Kategori')
                                    ->placeholder('Semua Kategori')
                                    ->options(AssessmentCategory::pluck('nama_kategori', 'id'))
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),
                            ])
                    ])
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('schoolAssessment.school.nama_sekolah')
                    ->label('Sekolah')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('schoolAssessment.period.name')
                    ->label('Periode')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('assessmentIndicator.category.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('info'),

                TextColumn::make('assessmentIndicator.nama_indikator')
                    ->label('Indikator')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('skor')
                    ->label('Skor')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('school')
                    ->relationship('schoolAssessment.school', 'nama_sekolah')
                    ->label('Sekolah'),

                SelectFilter::make('period')
                    ->relationship('schoolAssessment.period', 'name')
                    ->label('Periode'),

                SelectFilter::make('category')
                    ->relationship('assessmentIndicator.category', 'nama_kategori')
                    ->label('Kategori'),
            ])
            ->headerActions([
                // Add export actions here if needed
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $query = AssessmentScore::query()
            ->with([
                'schoolAssessment.school',
                'schoolAssessment.period',
                'assessmentIndicator.category'
            ]);

        if ($this->selectedSchool) {
            $query->whereHas('schoolAssessment.school', function (Builder $q) {
                $q->where('id', $this->selectedSchool);
            });
        }

        if ($this->selectedPeriod) {
            $query->whereHas('schoolAssessment.period', function (Builder $q) {
                $q->where('id', $this->selectedPeriod);
            });
        }

        if ($this->selectedCategory) {
            $query->whereHas('assessmentIndicator.category', function (Builder $q) {
                $q->where('id', $this->selectedCategory);
            });
        }

        return $query;
    }

    protected function resetTable(): void
    {
        $this->resetPage();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssessmentStatsWidget::class,
        ];
    }
}

class AssessmentStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Get the parent page instance to access filters
        $livewire = $this->getLivewire();

        $query = AssessmentScore::query()
            ->with([
                'schoolAssessment.school',
                'schoolAssessment.period',
                'assessmentIndicator.category'
            ]);

        // Apply filters if they exist
        if (isset($livewire->selectedSchool) && $livewire->selectedSchool) {
            $query->whereHas('schoolAssessment.school', function (Builder $q) use ($livewire) {
                $q->where('id', $livewire->selectedSchool);
            });
        }

        if (isset($livewire->selectedPeriod) && $livewire->selectedPeriod) {
            $query->whereHas('schoolAssessment.period', function (Builder $q) use ($livewire) {
                $q->where('id', $livewire->selectedPeriod);
            });
        }

        if (isset($livewire->selectedCategory) && $livewire->selectedCategory) {
            $query->whereHas('assessmentIndicator.category', function (Builder $q) use ($livewire) {
                $q->where('id', $livewire->selectedCategory);
            });
        }

        $total = $query->count();
        $average = round($query->avg('skor') ?? 0, 2);
        $highest = $query->max('skor') ?? 0;
        $lowest = $query->min('skor') ?? 0;

        return [
            Stat::make('Total Penilaian', number_format($total))
                ->description('Total data penilaian')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Rata-rata Skor', $average)
                ->description('Skor rata-rata keseluruhan')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make('Skor Tertinggi', $highest)
                ->description('Nilai skor tertinggi')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),

            Stat::make('Skor Terendah', $lowest)
                ->description('Nilai skor terendah')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
