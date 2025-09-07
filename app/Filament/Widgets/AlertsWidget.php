<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\School;
use App\Models\AssessmentPeriod;
use Filament\Widgets\Widget;
use Carbon\Carbon;

class AlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.alerts';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    public function getViewData(): array
    {
        $alerts = $this->generateAlerts();
        
        return [
            'alerts' => $alerts,
            'hasUrgent' => $alerts->where('type', 'urgent')->isNotEmpty(),
            'hasWarning' => $alerts->where('type', 'warning')->isNotEmpty(),
            'totalAlerts' => $alerts->count(),
        ];
    }

    private function generateAlerts()
    {
        $alerts = collect();
        $currentPeriod = AssessmentPeriod::where('is_default', true)->first();

        // 1. Overdue Assessments (URGENT)
        $overdueCount = SchoolAssessment::where('status', 'draft')
            ->where('created_at', '<', now()->subDays(30))
            ->count();
        
        if ($overdueCount > 0) {
            $alerts->push([
                'type' => 'urgent',
                'icon' => 'heroicon-o-exclamation-triangle',
                'title' => 'Overdue Assessments',
                'message' => "$overdueCount assessment" . ($overdueCount > 1 ? 's are' : ' is') . " overdue (>30 days)",
                'action' => 'Review Now',
                'url' => route('filament.admin.resources.school-assessments.index') . '?tableFilters[status][value]=draft',
                'priority' => 1
            ]);
        }

        // 2. Schools without assessment (WARNING)
        $schoolsWithoutAssessment = School::whereDoesntHave('schoolAssessments', function($q) use ($currentPeriod) {
            if ($currentPeriod) {
                $q->where('assessment_period_id', $currentPeriod->id);
            }
        })->count();
        
        if ($schoolsWithoutAssessment > 0 && $currentPeriod) {
            $alerts->push([
                'type' => 'warning',
                'icon' => 'heroicon-o-building-office-2',
                'title' => 'Missing Assessments',
                'message' => "$schoolsWithoutAssessment school" . ($schoolsWithoutAssessment > 1 ? 's have' : ' has') . " no assessment for {$currentPeriod->nama_periode}",
                'action' => 'Start Assessment',
                'url' => route('filament.admin.pages.assessment-wizard'),
                'priority' => 2
            ]);
        }

        // 3. Assessment Period Ending Soon (INFO)
        if ($currentPeriod && $currentPeriod->tanggal_selesai) {
            $daysUntilEnd = Carbon::parse($currentPeriod->tanggal_selesai)->diffInDays(now());
            if ($daysUntilEnd <= 15 && $daysUntilEnd > 0) {
                $alerts->push([
                    'type' => 'info',
                    'icon' => 'heroicon-o-calendar-days',
                    'title' => 'Assessment Period Ending',
                    'message' => "Assessment period '{$currentPeriod->nama_periode}' ends in $daysUntilEnd days",
                    'action' => 'View Schedule',
                    'url' => route('filament.admin.resources.assessment-periods.view', $currentPeriod->id),
                    'priority' => 3
                ]);
            }
        }

        // 4. Recent Assessment Activity (SUCCESS)
        $recentAssessments = SchoolAssessment::where('status', 'submitted')
            ->where('updated_at', '>=', now()->subDays(1))
            ->with('school')
            ->get();
            
        foreach ($recentAssessments->take(2) as $assessment) {
            $alerts->push([
                'type' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'title' => 'New Assessment Submitted',
                'message' => "Assessment for {$assessment->school->nama_sekolah} submitted for review",
                'action' => 'Review',
                'url' => route('filament.admin.resources.school-assessments.view', $assessment->id),
                'priority' => 4
            ]);
        }

        // 5. Low Average Scores (WARNING)
        if ($currentPeriod) {
            $lowScoreSchools = SchoolAssessment::where('assessment_period_id', $currentPeriod->id)
                ->where('total_score', '<', 2.0)
                ->where('total_score', '>', 0)
                ->with('school')
                ->get();
                
            if ($lowScoreSchools->isNotEmpty()) {
                $schoolNames = $lowScoreSchools->pluck('school.nama_sekolah')->take(2)->join(', ');
                $total = $lowScoreSchools->count();
                $moreText = $total > 2 ? " and " . ($total - 2) . " more" : "";
                
                $alerts->push([
                    'type' => 'warning',
                    'icon' => 'heroicon-o-chart-bar-square',
                    'title' => 'Low Performance Alert',
                    'message' => "$schoolNames$moreText have scores below 2.0 - may need support",
                    'action' => 'View Details',
                    'url' => route('filament.admin.resources.school-assessments.index'),
                    'priority' => 2
                ]);
            }
        }

        // Sort by priority and take top 5
        return $alerts->sortBy('priority')->take(5);
    }
}
