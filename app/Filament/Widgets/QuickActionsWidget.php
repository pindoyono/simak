<?php

namespace App\Filament\Widgets;

use App\Models\SchoolAssessment;
use App\Models\School;
use App\Models\AssessmentPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();
        $currentPeriod = AssessmentPeriod::where('is_default', true)->first();
        
        // Get actionable insights
        $pendingAssessments = SchoolAssessment::whereIn('status', ['draft', 'submitted'])
            ->when($currentPeriod, function($q) use ($currentPeriod) {
                return $q->where('assessment_period_id', $currentPeriod->id);
            })->count();
            
        $schoolsWithoutAssessment = School::whereDoesntHave('schoolAssessments', function($q) use ($currentPeriod) {
            if ($currentPeriod) {
                $q->where('assessment_period_id', $currentPeriod->id);
            }
        })->count();

        $overdueAssessments = SchoolAssessment::where('status', 'draft')
            ->where('created_at', '<', now()->subDays(30))
            ->count();

        return [
            'pendingAssessments' => $pendingAssessments,
            'schoolsWithoutAssessment' => $schoolsWithoutAssessment,
            'overdueAssessments' => $overdueAssessments,
            'currentPeriod' => $currentPeriod,
            'canCreateAssessment' => $user->can('create', SchoolAssessment::class),
            'canViewReports' => true, // Adjust based on permissions
        ];
    }
}
