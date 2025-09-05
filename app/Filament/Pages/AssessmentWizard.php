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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssessmentWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.pages.assessment-wizard';
    protected static ?string $navigationLabel = 'Assessment Wizard';
    protected static ?string $title = 'School Assessment Wizard';
    protected static ?string $navigationGroup = 'Assessment Management';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    public int $currentStep = 1;
    public ?SchoolAssessment $assessment = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return match ($this->currentStep) {
            1 => $this->getSchoolSelectionStep(),
            2 => $this->getAssessmentScoringStep(),
            3 => $this->getFileUploadStep(),
            4 => $this->getReviewStep(),
            default => [],
        };
    }

    protected function getSchoolSelectionStep(): array
    {
        return [
            Forms\Components\Section::make('School Information')
                ->description('Select the school and assessment period for this evaluation.')
                ->schema([
                    Forms\Components\Select::make('school_id')
                        ->label('School')
                        ->options(function () {
                            return School::all()->mapWithKeys(function ($school) {
                                return [$school->id => $school->nama_sekolah ?? 'Unnamed School'];
                            });
                        })
                        ->required()
                        ->searchable()
                        ->placeholder('Select a school'),

                    Forms\Components\Select::make('assessment_period_id')
                        ->label('Assessment Period')
                        ->options(function () {
                            return AssessmentPeriod::all()->mapWithKeys(function ($period) {
                                return [$period->id => $period->nama_periode ?? 'Unnamed Period'];
                            });
                        })
                        ->required()
                        ->placeholder('Select assessment period'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Initial Notes')
                        ->placeholder('Any preliminary notes about this assessment...')
                        ->rows(3),
                ]),
        ];
    }

    protected function getAssessmentScoringStep(): array
    {
        $categories = AssessmentCategory::with('indicators')->get();
        $schema = [];

        foreach ($categories as $category) {
            $indicatorFields = [];

            foreach ($category->indicators as $indicator) {
                $indicatorFields[] = Forms\Components\Section::make($indicator->name)
                    ->description($indicator->description)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Radio::make("scores.{$indicator->id}.score")
                            ->label('Score')
                            ->options($this->getScoreOptions($indicator))
                            ->inline()
                            ->required(),
                    ]);
            }

            $schema[] = Forms\Components\Section::make($category->name)
                ->description($category->description)
                ->schema($indicatorFields)
                ->collapsible()
                ->collapsed(false);
        }

        return $schema;
    }

    protected function getFileUploadStep(): array
    {
        $indicators = AssessmentIndicator::all();
        $schema = [];

        foreach ($indicators as $indicator) {
            $schema[] = Forms\Components\Section::make($indicator->name)
                ->description('Upload supporting documents for this indicator')
                ->schema([
                    Forms\Components\FileUpload::make("files.{$indicator->id}")
                        ->label('Supporting Documents')
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(10240) // 10MB
                        ->directory('assessment-files')
                        ->visibility('private')
                        ->downloadable()
                        ->previewable(false),

                    Forms\Components\Textarea::make("file_descriptions.{$indicator->id}")
                        ->label('File Description')
                        ->placeholder('Brief description of the uploaded files...')
                        ->rows(2),
                ])
                ->collapsible()
                ->collapsed(true);
        }

        return $schema;
    }

    protected function getReviewStep(): array
    {
        return [
            Forms\Components\Section::make('Assessment Review')
                ->description('Review your assessment before submission')
                ->schema([
                    Forms\Components\Placeholder::make('school_info')
                        ->label('School')
                        ->content(function (): string {
                            if (!isset($this->data['school_id'])) {
                                return 'No school selected';
                            }
                            $school = School::find($this->data['school_id']);
                            return $school ? $school->nama_sekolah : 'School not found';
                        }),

                    Forms\Components\Placeholder::make('assessment_period_info')
                        ->label('Assessment Period')
                        ->content(function (): string {
                            if (!isset($this->data['assessment_period_id'])) {
                                return 'No assessment period selected';
                            }
                            $period = AssessmentPeriod::find($this->data['assessment_period_id']);
                            return $period ? $period->nama_periode : 'Assessment period not found';
                        }),

                    Forms\Components\Placeholder::make('total_score')
                        ->label('Rata-rata Skor')
                        ->content(function (): string {
                            $average = $this->calculateTotalScore($this->data);
                            $grade = match (true) {
                                $average >= 3.5 => 'Sangat Baik',
                                $average >= 2.5 => 'Baik',
                                $average >= 1.5 => 'Cukup Baik',
                                default => 'Kurang'
                            };
                            return $average . '/4 (' . $grade . ')';
                        }),

                    Forms\Components\Placeholder::make('grade')
                        ->label('Final Grade')
                        ->content(fn (): string => $this->calculateFinalGrade($this->data)),

                    Forms\Components\Placeholder::make('scores_count')
                        ->label('Indicators Scored')
                        ->content(function (): string {
                            $scores = $this->data['scores'] ?? [];
                            $validScores = array_filter($scores, fn($score) => isset($score['score']) && is_numeric($score['score']));
                            return count($validScores) . ' indicators scored';
                        }),

                    Forms\Components\Textarea::make('final_comments')
                        ->label('Final Comments')
                        ->placeholder('Any final comments about this assessment...')
                        ->rows(3),
                ]),
        ];
    }

    public function nextStep(): void
    {
        try {
            $stepData = $this->form->getState();

            // Merge current step data with existing data
            $this->data = array_merge($this->data, $stepData);

            if ($this->currentStep < 4) {
                $this->currentStep++;
                $this->form->fill($this->data);
            }
        } catch (Halt $exception) {
            return;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->form->fill($this->data);
        }
    }

    public function submit(): void
    {
        try {
            $currentStepData = $this->form->getState();

            // Merge current step data with all previous data
            $allData = array_merge($this->data, $currentStepData);

            DB::transaction(function () use ($allData) {
                // Validate required fields
                if (!isset($allData['school_id']) || !isset($allData['assessment_period_id'])) {
                    throw new \Exception('School and Assessment Period are required');
                }

                // Ensure user is authenticated
                if (!Auth::check()) {
                    throw new \Exception('User must be authenticated to submit assessment');
                }

                // Create or update school assessment
                $this->assessment = SchoolAssessment::updateOrCreate(
                    [
                        'school_id' => $allData['school_id'],
                        'assessment_period_id' => $allData['assessment_period_id'],
                        'assessor_id' => Auth::id(),
                    ],
                    [
                        'status' => 'submitted',
                        'total_score' => $this->calculateTotalScore($allData),
                        'grade' => $this->calculateGradeForDatabase($allData),
                        'submitted_at' => now(),
                        'catatan' => $allData['notes'] ?? null,
                        'tanggal_asesmen' => now()->toDateString(),
                    ]
                );

                // Save assessment scores
                if (isset($allData['scores'])) {
                    foreach ($allData['scores'] as $indicatorId => $scoreData) {
                        AssessmentScore::updateOrCreate(
                            [
                                'school_assessment_id' => $this->assessment->id,
                                'assessment_indicator_id' => $indicatorId,
                            ],
                            [
                                'skor' => $scoreData['score'],
                                'catatan' => null,
                            ]
                        );
                    }
                }

                // Save uploaded files
                if (isset($allData['files'])) {
                    foreach ($allData['files'] as $indicatorId => $files) {
                        if (is_array($files)) {
                            foreach ($files as $filePath) {
                                if ($filePath) {
                                    $this->saveAssessmentFile($indicatorId, $filePath, $allData['file_descriptions'][$indicatorId] ?? null);
                                }
                            }
                        }
                    }
                }

                // Create review record if submitted
                AssessmentReview::create([
                    'school_assessment_id' => $this->assessment->id,
                    'reviewer_id' => Auth::id(),
                    'status' => 'submitted',
                    'comments' => $allData['final_comments'] ?? null,
                    'reviewed_at' => now(),
                ]);
            });

            Notification::make()
                ->title('Assessment submitted successfully!')
                ->body('Assessment has been saved and submitted for review.')
                ->success()
                ->send();

            // Redirect to assessment review page
            $this->redirect(route('filament.admin.resources.assessment-reviews.index'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving assessment')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();

            // Log the error for debugging
            Log::error('Assessment submission error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'data_keys' => array_keys($this->data),
            ]);
        }
    }

    protected function saveAssessmentFile(int $indicatorId, string $filePath, ?string $description): void
    {
        $file = Storage::disk('local')->get($filePath);
        $fileInfo = pathinfo($filePath);

        AssessmentFile::create([
            'school_assessment_id' => $this->assessment->id,
            'assessment_indicator_id' => $indicatorId,
            'file_name' => $fileInfo['basename'],
            'file_path' => $filePath,
            'file_type' => $fileInfo['extension'] ?? 'unknown',
            'mime_type' => mime_content_type(Storage::disk('local')->path($filePath)),
            'file_size' => Storage::disk('local')->size($filePath),
            'description' => $description,
            'uploaded_by' => Auth::id(),
        ]);
    }

    protected function getSchoolInfo(): string
    {
        if (!isset($this->data['school_id'])) {
            return 'No school selected';
        }

        $school = School::find($this->data['school_id']);
        return $school ? $school->nama_sekolah : 'School not found';
    }

    public function getSteps(): array
    {
        return [
            1 => 'School Selection',
            2 => 'Assessment Scoring',
            3 => 'File Upload',
            4 => 'Review & Submit',
        ];
    }

    public function getCurrentStepLabel(): string
    {
        return $this->getSteps()[$this->currentStep] ?? 'Unknown Step';
    }

    /**
     * Get score options for an indicator based on database configuration
     */
    protected function getScoreOptions(AssessmentIndicator $indicator): array
    {
        $maxScore = $indicator->skor_maksimal ?? 4;
        $criteria = $indicator->kriteria_penilaian;

        // If criteria_penilaian is defined and has specific format, parse it
        if ($criteria && $this->isStructuredCriteria($criteria)) {
            return $this->parseStructuredCriteria($criteria);
        }

        // Default fallback based on skor_maksimal
        return $this->generateDefaultOptions($maxScore);
    }

    /**
     * Check if criteria follows structured format like "1=Label, 2=Label"
     */
    protected function isStructuredCriteria(string $criteria): bool
    {
        return preg_match('/\d+\s*=\s*[^,]+/', $criteria);
    }

    /**
     * Parse structured criteria like "Skala 1-4: 1=Sangat Kurang, 2=Kurang, 3=Baik, 4=Sangat Baik"
     */
    protected function parseStructuredCriteria(string $criteria): array
    {
        $options = [];

        // Extract key=value pairs using regex
        preg_match_all('/(\d+)\s*=\s*([^,]+)/', $criteria, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $score = (int) $match[1];
            $label = trim($match[2]);
            $options[$score] = $label;
        }

        // Sort by score value
        ksort($options);

        return $options;
    }

    /**
     * Generate default options based on maximum score
     */
    protected function generateDefaultOptions(int $maxScore): array
    {
        $defaultLabels = [
            1 => 'Sangat Kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat Baik'
        ];

        $options = [];
        for ($i = 1; $i <= $maxScore; $i++) {
            $options[$i] = $defaultLabels[$i] ?? "Level {$i}";
        }

        return $options;
    }

    /**
     * Calculate weighted average score with enhanced algorithm
     */
    protected function calculateTotalScore(array $data = null): float
    {
        $scores = $data['scores'] ?? $this->data['scores'] ?? [];

        if (empty($scores)) {
            return 0;
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($scores as $indicatorId => $scoreData) {
            if (!isset($scoreData['score']) || !is_numeric($scoreData['score'])) {
                continue;
            }

            $indicator = AssessmentIndicator::find($indicatorId);
            if (!$indicator) continue;

            $score = (float) $scoreData['score'];
            $maxScore = $indicator->skor_maksimal ?? 4;
            $weight = $indicator->bobot_indikator ?? 1;

            // Normalize score to percentage (0-100)
            $normalizedScore = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;

            // Apply weight to normalized score
            $weightedScore = $normalizedScore * ($weight / 100);

            $totalWeightedScore += $weightedScore;
            $totalWeight += $weight;
        }

        // Return weighted average as percentage, then convert to 4-point scale
        $averagePercentage = $totalWeight > 0 ? $totalWeightedScore / ($totalWeight / 100) : 0;
        return round(($averagePercentage / 100) * 4, 2);
    }

    /**
     * Calculate final grade based on percentage
     */
    protected function calculateFinalGrade(array $data = null): string
    {
        $score = $this->calculateTotalScore($data);
        $percentage = ($score / 4) * 100;

        $grade = match (true) {
            $percentage >= 85 => 'A',
            $percentage >= 70 => 'B',
            $percentage >= 55 => 'C',
            default => 'D',
        };

        $label = match ($grade) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'D' => 'Kurang',
        };

        return "{$grade} ({$label})";
    }

    /**
     * Get grade for database storage
     */
    protected function calculateGradeForDatabase(array $data = null): string
    {
        $score = $this->calculateTotalScore($data);
        $percentage = ($score / 4) * 100;

        return match (true) {
            $percentage >= 85 => 'A',
            $percentage >= 70 => 'B',
            $percentage >= 55 => 'C',
            default => 'D',
        };
    }
}
