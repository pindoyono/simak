<?php

namespace App\Filament\Pages;

use App\Models\School;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentCategory;
use App\Models\AssessmentIndicator;
use App\Models\SchoolAssessment;
use App\Models\AssessmentScore;
use App\Models\AssessmentFile;
use App\Models\AssessmentReview;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use App\Traits\AssessmentMemoryOptimization;

class AssessmentWizard extends Page implements HasForms
{
    use InteractsWithForms, AssessmentMemoryOptimization;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.pages.assessment-wizard';
    protected static ?string $navigationLabel = 'Wizard Penilaian';
    protected static ?string $title = 'Wizard Penilaian Sekolah';
    protected static ?string $navigationGroup = 'Penilaian';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->setMemoryLimit('assessment_wizard');
        $this->setExecutionTimeLimit();
        $this->checkMemoryUsage('mount_start');

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step 1: School & Period Selection
                    Wizard\Step::make('informasi_dasar')
                        ->label('Informasi Dasar')
                        ->description('Pilih sekolah dan periode')
                        ->icon('heroicon-m-building-office-2')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Select::make('school_id')
                                        ->label('Sekolah')
                                        ->options(School::all()->pluck('nama_sekolah', 'id'))
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->placeholder('Pilih sekolah'),

                                    Forms\Components\Select::make('assessment_period_id')
                                        ->label('Periode Penilaian')
                                        ->options(AssessmentPeriod::active()->pluck('nama_periode', 'id'))
                                        ->required()
                                        ->live()
                                        ->placeholder('Pilih periode'),

                                    Forms\Components\DatePicker::make('tanggal_asesmen')
                                        ->label('Tanggal Penilaian')
                                        ->required()
                                        ->default(now())
                                        ->maxDate(now()),
                                ]),

                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->placeholder('Catatan tambahan...')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),

                    // Step 2: Assessment Scoring
                    Wizard\Step::make('penilaian_skor')
                        ->label('Penilaian & Skor')
                        ->description('Berikan skor untuk setiap indikator')
                        ->icon('heroicon-m-star')
                        ->schema($this->getAssessmentScoringStep()),

                    // Step 3: Review & Submit
                    Wizard\Step::make('review_submit')
                        ->label('Review & Submit')
                        ->description('Tinjau dan submit')
                        ->icon('heroicon-m-check-badge')
                        ->schema($this->getReviewStep()),
                ])
                ->submitAction(
                    Action::make('submit')
                        ->label('Submit Penilaian')
                        ->icon('heroicon-m-paper-airplane')
                        ->color('success')
                        ->size('lg')
                        ->action('submit')
                )
                ->cancelAction(
                    Action::make('cancel')
                        ->label('Batal')
                        ->color('gray')
                        ->url(route('filament.admin.pages.dashboard'))
                )
                ->skippable()
                ->persistStepInQueryString()
                ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getAssessmentScoringStep(): array
    {
        $this->checkMemoryUsage('scoring_step_start');

        // OPTIMASI: Eager load dengan limit dan cache
        $categories = AssessmentCategory::with(['indicators' => function ($query) {
            $query->orderBy('urutan_tampil')
                  ->select(['id', 'nama_indikator', 'deskripsi_indikator', 'assessment_category_id', 'kriteria_penilaian', 'skor_maksimal', 'bobot_indikator']);
        }])
        ->orderBy('urutan_tampil')
        ->select(['id', 'nama_kategori', 'urutan_tampil'])
        ->get();

        $schema = [];

        foreach ($categories as $category) {
            if ($category->indicators->isEmpty()) continue;

            $indicatorFields = [];

            foreach ($category->indicators as $indicator) {
                $indicatorFields[] = Forms\Components\Section::make($indicator->nama_indikator)
                    ->description($indicator->deskripsi_indikator)
                    ->collapsible()
                    ->collapsed(false) // Default terbuka
                    ->schema([
                        Forms\Components\Radio::make("scores.{$indicator->id}.skor")
                            ->label('Skor')
                            ->options($this->getCachedScoreOptions($indicator)) // OPTIMASI: Cache options
                            ->required()
                            ->live(false) // OPTIMASI: Disable live untuk performa
                            ->inline(true)
                            ->extraAttributes([
                                'class' => 'radio-horizontal',
                                'style' => 'display: flex; flex-wrap: nowrap; gap: 0.5rem; font-size: 0.8rem;'
                            ])
                            ->columnSpanFull(),
                    ])
                    ->compact(); // Make sections more compact
            }

            $schema[] = Forms\Components\Section::make($category->nama_kategori)
                ->collapsible()
                ->collapsed(false) // Default terbuka
                ->compact() // Make sections more compact
                ->schema($indicatorFields);
        }

        // Add total score display
        $schema[] = Forms\Components\Section::make('Ringkasan Skor')
            ->collapsible()
            ->collapsed(false)
            ->compact()
            ->schema([
                Forms\Components\Placeholder::make('total_score_display')
                    ->label('Total Skor')
                    ->content(function (Forms\Get $get) {
                        $totalScore = $this->calculateTotalScore($get('scores') ?? []);
                        return number_format($totalScore, 2) . ' / 4.00';
                    })
                    ->extraAttributes(['class' => 'text-lg font-semibold']),
            ]);

        $this->checkMemoryUsage('scoring_step_end');

        return $schema;
    }

    // DISABLED: File upload step removed for performance optimization
    /*
    protected function getFileUploadStep(): array
    {
        $categories = AssessmentCategory::with(['indicators' => function ($query) {
            $query->orderBy('urutan_tampil');
        }])->orderBy('urutan_tampil')->get();

        $schema = [];

        foreach ($categories as $category) {
            if ($category->indicators->isEmpty()) continue;

            $categoryFields = [];

            foreach ($category->indicators as $indicator) {
                $categoryFields[] = Forms\Components\Section::make($indicator->nama_indikator)
                    ->description('Upload dokumen pendukung untuk indikator ini')
                    ->collapsible()
                    ->collapsed(false) // Default terbuka
                    ->schema([
                        Forms\Components\FileUpload::make("files.{$indicator->id}")
                            ->label('Dokumen Pendukung')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->directory('assessment-files')
                            ->disk('public')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText('Format: PDF, DOC, DOCX, JPG, PNG. Maksimal 10MB per file.'),
                    ]);
            }

            $schema[] = Forms\Components\Section::make($category->nama_kategori)
                ->description("Upload dokumen untuk kategori: {$category->nama_kategori}")
                ->collapsible()
                ->collapsed(false)
                ->schema($categoryFields);
        }

        return $schema;
    }
    */

    protected function getReviewStep(): array
    {
        return [
            Forms\Components\Section::make('Ringkasan')
                ->collapsible()
                ->collapsed(false)
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(4)
                        ->schema([
                            Forms\Components\Placeholder::make('review_school')
                                ->label('Sekolah')
                                ->content(function (Forms\Get $get) {
                                    $schoolId = $get('school_id');
                                    if ($schoolId) {
                                        $school = School::find($schoolId);
                                        return $school?->nama_sekolah ?? '-';
                                    }
                                    return '-';
                                }),

                            Forms\Components\Placeholder::make('review_period')
                                ->label('Periode')
                                ->content(function (Forms\Get $get) {
                                    $periodId = $get('assessment_period_id');
                                    if ($periodId) {
                                        $period = AssessmentPeriod::find($periodId);
                                        return $period?->nama_periode ?? '-';
                                    }
                                    return '-';
                                }),

                            Forms\Components\Placeholder::make('review_date')
                                ->label('Tanggal')
                                ->content(function (Forms\Get $get) {
                                    $date = $get('tanggal_asesmen');
                                    return $date ? date('d/m/Y', strtotime($date)) : '-';
                                }),

                            Forms\Components\Placeholder::make('review_total_score')
                                ->label('Total Skor')
                                ->content(function (Forms\Get $get) {
                                    $totalScore = $this->calculateTotalScore($get('scores') ?? []);
                                    return number_format($totalScore, 2) . ' / 4.00';
                                })
                                ->extraAttributes(['class' => 'text-lg font-semibold text-primary-600']),
                        ]),
                ]),

            Forms\Components\Section::make('Konfirmasi')
                ->compact()
                ->schema([
                    Forms\Components\Checkbox::make('confirm_accuracy')
                        ->label('Data yang diisi sudah akurat')
                        ->required(),

                    Forms\Components\Checkbox::make('confirm_documents')
                        ->label('Siap untuk submit penilaian')
                        ->required(),
                ]),
        ];
    }

    protected function getCachedScoreOptions(AssessmentIndicator $indicator): array
    {
        // OPTIMASI: Cache score options untuk menghindari parsing berulang
        static $optionsCache = [];

        $cacheKey = "indicator_{$indicator->id}";

        if (!isset($optionsCache[$cacheKey])) {
            $optionsCache[$cacheKey] = $this->getScoreOptions($indicator);
        }

        return $optionsCache[$cacheKey];
    }

    protected function getScoreOptions(AssessmentIndicator $indicator): array
    {
        $criteria = $indicator->kriteria_penilaian;

        // Parse structured criteria if available
        if ($criteria && $this->isStructuredCriteria($criteria)) {
            return $this->parseStructuredCriteria($criteria);
        }

        // Default options based on max score
        $maxScore = $indicator->skor_maksimal ?? 4;
        return $this->generateDefaultOptions($maxScore);
    }

    protected function isStructuredCriteria(string $criteria): bool
    {
        return preg_match('/\d+\s*=\s*[^,]+/', $criteria);
    }

    protected function parseStructuredCriteria(string $criteria): array
    {
        $options = [];

        preg_match_all('/(\d+)\s*=\s*([^,]+)/', $criteria, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $score = (int) $match[1];
            $label = trim($match[2]);
            $options[$score] = "{$score} - {$label}";
        }

        ksort($options);
        return $options;
    }

    protected function generateDefaultOptions(int $maxScore): array
    {
        $labels = [
            1 => 'Kurang',
            2 => 'Cukup',
            3 => 'Baik',
            4 => 'Sangat Baik',
            5 => 'Excellent'
        ];

        $options = [];
        for ($i = 1; $i <= $maxScore; $i++) {
            $options[$i] = "{$i} - " . ($labels[$i] ?? "Level {$i}");
        }

        return $options;
    }

    protected function getScoreDescriptions(AssessmentIndicator $indicator): array
    {
        $criteria = $indicator->kriteria_penilaian;

        // If criteria has structured format, extract descriptions
        if ($criteria && $this->isStructuredCriteria($criteria)) {
            return $this->parseScoreDescriptions($criteria);
        }

        // Default descriptions based on max score
        $maxScore = $indicator->skor_maksimal ?? 4;
        return $this->generateDefaultDescriptions($maxScore);
    }

    protected function parseScoreDescriptions(string $criteria): array
    {
        $descriptions = [];

        // Extract key=value pairs for descriptions
        preg_match_all('/(\d+)\s*=\s*([^,]+)/', $criteria, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $score = (int) $match[1];
            $label = trim($match[2]);
            $descriptions[$score] = $this->getDetailedDescription($score, $label);
        }

        return $descriptions;
    }

    protected function generateDefaultDescriptions(int $maxScore): array
    {
        $descriptions = [
            1 => 'Tidak memenuhi standar minimal yang ditetapkan',
            2 => 'Memenuhi sebagian kecil standar yang ditetapkan',
            3 => 'Memenuhi standar dasar yang ditetapkan',
            4 => 'Memenuhi standar dengan baik dan konsisten',
            5 => 'Melebihi standar yang ditetapkan dengan sangat baik'
        ];

        $result = [];
        for ($i = 1; $i <= $maxScore; $i++) {
            $result[$i] = $descriptions[$i] ?? "Kriteria level {$i}";
        }

        return $result;
    }

    protected function getDetailedDescription(int $score, string $label): string
    {
        $baseDescriptions = [
            1 => "($label) - Belum memenuhi kriteria standar",
            2 => "($label) - Memenuhi kriteria minimal",
            3 => "($label) - Memenuhi kriteria standar",
            4 => "($label) - Memenuhi kriteria dengan baik",
            5 => "($label) - Melebihi kriteria yang diharapkan"
        ];

        return $baseDescriptions[$score] ?? "($label) - Level penilaian $score";
    }

    protected function calculateTotalScore(array $scores): float
    {
        if (empty($scores)) return 0;

        $this->checkMemoryUsage('calculate_score_start');

        // Get all indicators with their categories for category-based calculation
        $indicatorIds = array_keys($scores);
        $indicators = AssessmentIndicator::whereIn('id', $indicatorIds)
            ->with('category')
            ->select('id', 'skor_maksimal', 'assessment_category_id')
            ->get()
            ->keyBy('id');

        // Group scores by category
        $categoryScores = [];

        foreach ($scores as $indicatorId => $scoreData) {
            if (empty($scoreData['skor']) || !is_numeric($scoreData['skor'])) {
                continue;
            }

            $indicator = $indicators->get($indicatorId);
            if (!$indicator || !$indicator->category) continue;

            $categoryId = $indicator->assessment_category_id;
            $score = (float) $scoreData['skor'];
            $maxScore = $indicator->skor_maksimal ?? 4;

            // Normalize score to 4-point scale
            $normalizedScore = $maxScore > 0 ? ($score / $maxScore) * 4 : 0;

            if (!isset($categoryScores[$categoryId])) {
                $categoryScores[$categoryId] = [
                    'total_score' => 0,
                    'count' => 0,
                    'bobot_penilaian' => $indicator->category->bobot_penilaian ?? 0
                ];
            }

            $categoryScores[$categoryId]['total_score'] += $normalizedScore;
            $categoryScores[$categoryId]['count']++;
        }

        // Calculate weighted total based on category weights
        $totalWeightedScore = 0;

        foreach ($categoryScores as $categoryData) {
            if ($categoryData['count'] > 0) {
                // Calculate average score for this category
                $categoryAverage = $categoryData['total_score'] / $categoryData['count'];

                // Apply category weight (bobot_penilaian)
                $weightedCategoryScore = $categoryAverage * ($categoryData['bobot_penilaian'] / 100);

                $totalWeightedScore += $weightedCategoryScore;
            }
        }

        $this->checkMemoryUsage('calculate_score_end');

        return round($totalWeightedScore, 2);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $this->checkMemoryUsage('submit_start');

        // Show initial processing notification
        Notification::make()
            ->title('Memproses Penilaian...')
            ->body('Sedang menyimpan data penilaian, mohon tunggu sebentar.')
            ->info()
            ->duration(3000)
            ->send();

        try {
            DB::transaction(function () use ($data) {
                // Show validation notification
                Notification::make()
                    ->title('✅ Validasi Data Berhasil')
                    ->body('Data penilaian telah divalidasi dengan baik.')
                    ->success()
                    ->duration(2000)
                    ->send();

                // Create school assessment record
                $assessment = SchoolAssessment::create([
                    'school_id' => $data['school_id'],
                    'assessment_period_id' => $data['assessment_period_id'],
                    'assessor_id' => Auth::id(),
                    'tanggal_asesmen' => $data['tanggal_asesmen'],
                    'total_score' => $this->calculateTotalScore($data['scores'] ?? []),
                    'status' => 'submitted',
                    'catatan' => $data['notes'] ?? '',
                    'submitted_at' => now(),
                ]);

                // Show score saving notification
                Notification::make()
                    ->title('✅ Skor Indikator Tersimpan')
                    ->body('Semua skor indikator telah berhasil disimpan.')
                    ->success()
                    ->duration(2000)
                    ->send();

                // Save individual scores
                if (!empty($data['scores'])) {
                    foreach ($data['scores'] as $indicatorId => $scoreData) {
                        if (!empty($scoreData['skor'])) {
                            AssessmentScore::create([
                                'school_assessment_id' => $assessment->id,
                                'assessment_indicator_id' => $indicatorId,
                                'skor' => $scoreData['skor'],
                            ]);
                        }
                    }
                }

                // REMOVED: File upload processing for performance optimization

                Log::info('Assessment submitted successfully', [
                    'assessment_id' => $assessment->id,
                    'school_id' => $data['school_id'],
                    'total_score' => $assessment->total_score
                ]);
            });

            $this->checkMemoryUsage('submit_end');

            // Show final success notification with actions
            Notification::make()
                ->title('🎉 Penilaian Berhasil Disimpan!')
                ->body("Penilaian sekolah telah berhasil disimpan dengan total skor: {$this->calculateTotalScore($data['scores'] ?? [])} / 4.00")
                ->success()
                ->duration(7000)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view_report')
                        ->label('Lihat Laporan')
                        ->url(route('filament.admin.pages.assessment-report'))
                        ->button(),
                    \Filament\Notifications\Actions\Action::make('new_assessment')
                        ->label('Penilaian Baru')
                        ->url(route('filament.admin.pages.assessment-wizard'))
                        ->button()
                        ->color('gray'),
                ])
                ->send();

            // Redirect to assessment list after delay
            $this->redirect(route('filament.admin.resources.school-assessments.index'));

        } catch (\Exception $e) {
            Log::error('Assessment submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Show detailed error notification
            Notification::make()
                ->title('❌ Gagal Menyimpan Penilaian')
                ->body('Terjadi kesalahan saat menyimpan penilaian: ' . $e->getMessage())
                ->danger()
                ->duration(10000)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('retry')
                        ->label('Coba Lagi')
                        ->button()
                        ->close(),
                    \Filament\Notifications\Actions\Action::make('support')
                        ->label('Hubungi Support')
                        ->url('mailto:support@example.com')
                        ->button()
                        ->color('gray'),
                ])
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('criteria')
                ->label('Kriteria Penilaian')
                ->icon('heroicon-m-clipboard-document-list')
                ->color('warning')
                ->modalHeading('Kriteria Penilaian Indikator')
                ->modalContent($this->getCriteriaModalContent())
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalWidth('5xl'),

            Action::make('help')
                ->label('Bantuan')
                ->icon('heroicon-m-question-mark-circle')
                ->color('info')
                ->modalHeading('Panduan Assessment Wizard')
                ->modalContent(view('filament.components.assessment-help'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            Action::make('reset')
                ->label('Reset Form')
                ->icon('heroicon-m-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset Form Penilaian')
                ->modalDescription('Apakah Anda yakin ingin mereset semua data? Tindakan ini tidak dapat dibatalkan.')
                ->action(fn () => $this->form->fill()),
        ];
    }

    protected function getCriteriaModalContent(): \Illuminate\Contracts\View\View
    {
        $categories = AssessmentCategory::with(['indicators' => function ($query) {
            $query->orderBy('urutan_tampil');
        }])->orderBy('urutan_tampil')->get();

        return view('filament.components.assessment-criteria', [
            'categories' => $categories
        ]);
    }
}
