<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\SchoolAssessment;
use App\Models\AssessmentPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class SchoolPerformanceWidget extends Widget
{
    protected static string $view = 'filament.widgets.school-performance';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '120s';

    public function getViewData(): array
    {
        $currentPeriod = AssessmentPeriod::where('is_default', true)->first();
        
        // Top performing schools
        $topSchools = SchoolAssessment::with('school')
            ->when($currentPeriod, function($q) use ($currentPeriod) {
                return $q->where('assessment_period_id', $currentPeriod->id);
            })
            ->where('total_score', '>', 0)
            ->orderBy('total_score', 'desc')
            ->take(5)
            ->get();

        // Schools needing attention
        $needsAttentionSchools = SchoolAssessment::with('school')
            ->when($currentPeriod, function($q) use ($currentPeriod) {
                return $q->where('assessment_period_id', $currentPeriod->id);
            })
            ->where('total_score', '<', 2.5)
            ->where('total_score', '>', 0)
            ->orderBy('total_score', 'asc')
            ->take(5)
            ->get();

        // Grade distribution
        $gradeDistribution = SchoolAssessment::when($currentPeriod, function($q) use ($currentPeriod) {
                return $q->where('assessment_period_id', $currentPeriod->id);
            })
            ->where('total_score', '>', 0)
            ->get()
            ->groupBy(function($assessment) {
                $score = $assessment->total_score;
                if ($score >= 3.5) return 'A';
                if ($score >= 2.5) return 'B';
                if ($score >= 1.5) return 'C';
                return 'D';
            })
            ->map(function($group) {
                return $group->count();
            });

        // Assessment progress by school status
        $progressByStatus = School::select('status')
            ->withCount(['assessments as completed_count' => function($q) use ($currentPeriod) {
                $q->where('status', 'approved');
                if ($currentPeriod) {
                    $q->where('assessment_period_id', $currentPeriod->id);
                }
            }])
            ->withCount(['assessments as total_count' => function($q) use ($currentPeriod) {
                if ($currentPeriod) {
                    $q->where('assessment_period_id', $currentPeriod->id);
                }
            }])
            ->groupBy('status')
            ->get();

        // Recent activity
        $recentActivity = SchoolAssessment::with(['school', 'assessor'])
            ->when($currentPeriod, function($q) use ($currentPeriod) {
                return $q->where('assessment_period_id', $currentPeriod->id);
            })
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return [
            'topSchools' => $topSchools,
            'needsAttentionSchools' => $needsAttentionSchools,
            'gradeDistribution' => $gradeDistribution,
            'progressByStatus' => $progressByStatus,
            'recentActivity' => $recentActivity,
            'currentPeriod' => $currentPeriod,
        ];
    }
}
