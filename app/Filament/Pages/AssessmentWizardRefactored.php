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
use App\Traits\AssessmentMemoryOptimization;

class AssessmentWizardRefactored extends Page implements HasForms
{
    use InteractsWithForms, AssessmentMemoryOptimization;

    protected static ?string $navigationIcon = null; // Hidden from navigation
    protected static string $view = 'filament.pages.assessment-wizard-refactored';
    protected static ?string $navigationLabel = null; // Hidden from navigation
    protected static ?string $title = 'Wizard Penilaian Sekolah (Refactored)';
    protected static ?string $navigationGroup = null; // Hidden from navigation
    protected static ?int $navigationSort = null; // Hidden from navigation
    protected static bool $shouldRegisterNavigation = false; // Hidden from navigation

    public ?array $data = [];

    public function mount(): void
    {
        // Set memory and execution limits for assessment operations
        $this->setMemoryLimit('assessment_wizard');
        $this->setExecutionTimeLimit();

        // Monitor initial memory usage
        $this->checkMemoryUsage('mount_start');

        $this->form->fill();

        $this->checkMemoryUsage('mount_end');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step 1: School & Period Selection
                    Wizard\Step::make('informasi_dasar')
                        ->label('Informasi Dasar')
                        ->description('Pilih sekolah dan periode penilaian')
                        ->icon('heroicon-m-building-office-2')
                        ->schema([
                            Forms\Components\Section::make('Informasi Penilaian')
                                ->description('Pilih sekolah dan periode yang akan dinilai')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('school_id')
                                                ->label('Sekolah')
                                                ->options(School::all()->pluck('nama_sekolah', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Pilih sekolah yang akan dinilai'),

                                            Forms\Components\Select::make('assessment_period_id')
                                                ->label('Periode Penilaian')
                                                ->options(AssessmentPeriod::where('status', 'aktif')->pluck('nama_periode', 'id'))
                                                ->required()
                                                ->placeholder('Pilih periode penilaian'),
                                        ]),

                                    Forms\Components\DatePicker::make('tanggal_asesmen')
                                        ->label('Tanggal Penilaian')
                                        ->required()
                                        ->default(now())
                                        ->maxDate(now()),

                                    Forms\Components\Textarea::make('catatan_awal')
                                        ->label('Catatan Awal')
                                        ->placeholder('Catatan atau informasi tambahan untuk penilaian ini...')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // Step 2: Assessment Scoring
                    Wizard\Step::make('penilaian_skor')
                        ->label('Penilaian Skor')
                        ->description('Berikan skor untuk setiap indikator')
                        ->icon('heroicon-m-star')
                        ->schema($this->getScoringSchema()),

                    // Step 3: File Upload
                    Wizard\Step::make('upload_dokumen')
                        ->label('Upload Dokumen')
                        ->description('Upload dokumen pendukung')
                        ->icon('heroicon-m-document-arrow-up')
                        ->schema($this->getFileUploadSchema()),

                    // Step 4: Review & Submit
                    Wizard\Step::make('review_submit')
                        ->label('Review & Submit')
                        ->description('Tinjau dan submit penilaian')
                        ->icon('heroicon-m-check-badge')
                        ->schema($this->getReviewSchema()),
                ])
                ->submitAction(
                    Action::make('submit')
                        ->label('Submit Penilaian')
                        ->icon('heroicon-m-paper-airplane')
                        ->color('success')
                        ->size('lg')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Submit Penilaian')
                        ->modalDescription('Apakah Anda yakin ingin mengirim penilaian ini? Setelah dikirim, penilaian tidak dapat diubah.')
                        ->action('submitAssessment')
                )
                ->skippable()
                ->persistStepInQueryString()
                ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getScoringSchema(): array
    {
        $categories = AssessmentCategory::with(['indicators' => function ($query) {
            $query->orderBy('urutan_tampil');
        }])->orderBy('urutan_tampil')->get();

        $schema = [];

        foreach ($categories as $category) {
            if ($category->indicators->isEmpty()) continue;

            $indicatorFields = [];

            foreach ($category->indicators as $indicator) {
                $indicatorFields[] = Forms\Components\Section::make($indicator->nama_indikator)
                    ->description($indicator->deskripsi_indikator)
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Radio::make("scores.{$indicator->id}.skor")
                                    ->label('Skor')
                                    ->options($this->getScoreOptions($indicator))
                                    ->descriptions($this->getScoreDescriptions($indicator))
                                    ->required()
                                    ->live()
                                    ->inline(false)
                                    ->afterStateUpdated(function ($state, Forms\Set $set) use ($indicator) {
                                        if ($state && $indicator->skor_maksimal) {
                                            $percentage = ($state / $indicator->skor_maksimal) * 100;
                                            $set("scores.{$indicator->id}.persentase", round($percentage, 2));
                                        }
                                    }),

                                Forms\Components\Placeholder::make("scores.{$indicator->id}.persentase_display")
                                    ->label('Persentase')
                                    ->content(function (Forms\Get $get) use ($indicator) {
                                        $score = $get("scores.{$indicator->id}.skor");
                                        if ($score && $indicator->skor_maksimal) {
                                            $percentage = ($score / $indicator->skor_maksimal) * 100;
                                            return $percentage . '%';
                                        }
                                        return '-';
                                    }),
                            ]),

                        Forms\Components\Textarea::make("scores.{$indicator->id}.bukti_dukung")
                            ->label('Bukti Pendukung')
                            ->placeholder('Jelaskan bukti atau dasar pemberian skor...')
                            ->rows(2),

                        Forms\Components\Textarea::make("scores.{$indicator->id}.catatan")
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan untuk indikator ini...')
                            ->rows(2),
                    ])
                    ->collapsible()
                    ->collapsed(true);
            }

            $schema[] = Forms\Components\Section::make($category->nama_kategori)
                ->description($category->deskripsi_kategori)
                ->icon('heroicon-m-folder')
                ->schema($indicatorFields)
                ->collapsible()
                ->collapsed(false);
        }

        return $schema;
    }

    protected function getFileUploadSchema(): array
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
                    ->schema([
                        Forms\Components\FileUpload::make("files.{$indicator->id}")
                            ->label('Dokumen Pendukung')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxFiles(5)
                            ->maxSize(10240) // 10MB
                            ->directory('assessment-files/' . date('Y/m'))
                            ->visibility('private')
                            ->downloadable()
                            ->previewable()
                            ->reorderable()
                            ->deletable(),

                        Forms\Components\Textarea::make("file_descriptions.{$indicator->id}")
                            ->label('Deskripsi Dokumen')
                            ->placeholder('Jelaskan dokumen yang diupload dan relevansinya...')
                            ->rows(2),
                    ])
                    ->collapsible()
                    ->collapsed(true);
            }

            $schema[] = Forms\Components\Section::make($category->nama_kategori)
                ->icon('heroicon-m-document-text')
                ->schema($categoryFields)
                ->collapsible()
                ->collapsed(true);
        }

        return $schema;
    }

    protected function getReviewSchema(): array
    {
        return [
            Forms\Components\Section::make('Ringkasan Penilaian')
                ->description('Tinjau kembali data penilaian sebelum submit')
                ->icon('heroicon-m-clipboard-document-check')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Placeholder::make('review_school')
                                ->label('Sekolah')
                                ->content(function (Forms\Get $get): string {
                                    $schoolId = $get('school_id');
                                    if (!$schoolId) return 'Belum dipilih';
                                    $school = School::find($schoolId);
                                    return $school?->nama_sekolah ?? 'Sekolah tidak ditemukan';
                                }),

                            Forms\Components\Placeholder::make('review_period')
                                ->label('Periode Penilaian')
                                ->content(function (Forms\Get $get): string {
                                    $periodId = $get('assessment_period_id');
                                    if (!$periodId) return 'Belum dipilih';
                                    $period = AssessmentPeriod::find($periodId);
                                    return $period?->nama_periode ?? 'Periode tidak ditemukan';
                                }),

                            Forms\Components\Placeholder::make('review_date')
                                ->label('Tanggal Penilaian')
                                ->content(function (Forms\Get $get): string {
                                    $date = $get('tanggal_asesmen');
                                    return $date ? \Carbon\Carbon::parse($date)->format('d F Y') : 'Belum diset';
                                }),

                            Forms\Components\Placeholder::make('review_total_score')
                                ->label('Skor Total')
                                ->content(function (Forms\Get $get): string {
                                    $totalScore = $this->calculateTotalScore($get('scores') ?? []);
                                    $grade = $this->calculateGrade($totalScore);
                                    return sprintf('%.2f/4.00 (%s)', $totalScore, $grade);
                                }),
                        ]),

                    Forms\Components\RichEditor::make('final_comments')
                        ->label('Komentar Akhir')
                        ->placeholder('Tambahkan komentar atau catatan akhir untuk penilaian ini...')
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'bold', 'italic', 'bulletList', 'orderedList'
                        ]),
                ]),
        ];
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
        return preg_match('/\d+\s*[=:]\s*[^,;]+/', $criteria);
    }

    protected function parseStructuredCriteria(string $criteria): array
    {
        $options = [];
        preg_match_all('/(\d+)\s*[=:]\s*([^,;]+)/', $criteria, $matches, PREG_SET_ORDER);

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
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik'
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

    protected function calculateGrade(float $score): string
    {
        $percentage = ($score / 4) * 100;

        return match (true) {
            $percentage >= 85 => 'Sangat Baik',
            $percentage >= 70 => 'Baik',
            $percentage >= 55 => 'Cukup',
            default => 'Kurang',
        };
    }

    public function submitAssessment(): void
    {
        try {
            $data = $this->form->getState();

            DB::transaction(function () use ($data) {
                // Create school assessment
                $assessment = SchoolAssessment::create([
                    'school_id' => $data['school_id'],
                    'assessment_period_id' => $data['assessment_period_id'],
                    'assessor_id' => Auth::id(),
                    'tanggal_asesmen' => $data['tanggal_asesmen'],
                    'status' => 'submitted',
                    'total_score' => $this->calculateTotalScore($data['scores'] ?? []),
                    'grade' => $this->calculateGradeForDatabase($data['scores'] ?? []),
                    'catatan' => $data['catatan_awal'] ?? null,
                    'submitted_at' => now(),
                ]);

                // Save scores
                if (!empty($data['scores'])) {
                    foreach ($data['scores'] as $indicatorId => $scoreData) {
                        if (!empty($scoreData['skor'])) {
                            AssessmentScore::create([
                                'school_assessment_id' => $assessment->id,
                                'assessment_indicator_id' => $indicatorId,
                                'skor' => $scoreData['skor'],
                                'bukti_dukung' => $scoreData['bukti_dukung'] ?? null,
                                'catatan' => $scoreData['catatan'] ?? null,
                                'tanggal_penilaian' => now(),
                            ]);
                        }
                    }
                }

                // Save files
                if (!empty($data['files'])) {
                    foreach ($data['files'] as $indicatorId => $files) {
                        if (is_array($files)) {
                            foreach ($files as $filePath) {
                                if ($filePath) {
                                    AssessmentFile::create([
                                        'school_assessment_id' => $assessment->id,
                                        'assessment_indicator_id' => $indicatorId,
                                        'file_name' => basename($filePath),
                                        'file_path' => $filePath,
                                        'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                        'description' => $data['file_descriptions'][$indicatorId] ?? null,
                                        'uploaded_by' => Auth::id(),
                                    ]);
                                }
                            }
                        }
                    }
                }

                // Create review record
                AssessmentReview::create([
                    'school_assessment_id' => $assessment->id,
                    'reviewer_id' => Auth::id(),
                    'status' => 'submitted',
                    'comments' => $data['final_comments'] ?? null,
                    'reviewed_at' => now(),
                ]);
            });

            Notification::make()
                ->title('Penilaian Berhasil Disimpan!')
                ->body('Penilaian telah berhasil disimpan dan dikirim untuk review.')
                ->success()
                ->send();

            $this->redirect(route('filament.admin.resources.assessment-reviews.index'));

        } catch (\Exception $e) {
            Log::error('Assessment submission error: ' . $e->getMessage());

            Notification::make()
                ->title('Gagal Menyimpan Penilaian')
                ->body('Terjadi kesalahan saat menyimpan penilaian: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function calculateGradeForDatabase(array $scores): string
    {
        $totalScore = $this->calculateTotalScore($scores);
        $percentage = ($totalScore / 4) * 100;

        return match (true) {
            $percentage >= 85 => 'Sangat Baik',
            $percentage >= 70 => 'Baik',
            $percentage >= 55 => 'Cukup',
            default => 'Kurang',
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('help')
                ->label('Bantuan')
                ->icon('heroicon-m-question-mark-circle')
                ->color('info')
                ->modalHeading('Panduan Assessment Wizard')
                ->modalContent(view('filament.components.assessment-help'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
        ];
    }
}
