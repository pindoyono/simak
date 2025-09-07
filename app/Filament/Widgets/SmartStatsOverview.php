<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\SchoolAssessment;
use App\Models\AssessmentScore;
use App\Models\User;
use App\Models\AssessmentPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SmartStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get current active period
        $currentPeriod = AssessmentPeriod::where('is_default', true)->first();
        $currentPeriodId = $currentPeriod?->id;

        // Basic counts
        $totalSchools = School::count();
        $activeSchools = School::where('status', 'Negeri')->orWhere('status', 'Swasta')->count();

        // Assessment progress for current period
        $totalAssessments = SchoolAssessment::when($currentPeriodId, function($q) use ($currentPeriodId) {
            return $q->where('assessment_period_id', $currentPeriodId);
        })->count();

        $completedAssessments = SchoolAssessment::where('status', 'approved')
            ->when($currentPeriodId, function($q) use ($currentPeriodId) {
                return $q->where('assessment_period_id', $currentPeriodId);
            })->count();

        $inProgressAssessments = SchoolAssessment::whereIn('status', ['draft', 'submitted', 'reviewed'])
            ->when($currentPeriodId, function($q) use ($currentPeriodId) {
                return $q->where('assessment_period_id', $currentPeriodId);
            })->count();

        // Calculate average score for current period
        $averageScore = SchoolAssessment::when($currentPeriodId, function($q) use ($currentPeriodId) {
            return $q->where('assessment_period_id', $currentPeriodId);
        })->avg('total_score') ?? 0;

        // School completion percentage
        $schoolCompletionRate = $totalSchools > 0 ? round(($completedAssessments / $totalSchools) * 100, 1) : 0;

        // Active assessors
        $activeAssessors = User::whereHas('schoolAssessments', function($q) use ($currentPeriodId) {
            $q->when($currentPeriodId, function($query) use ($currentPeriodId) {
                return $query->where('assessment_period_id', $currentPeriodId);
            });
        })->count();

        return [
            // Row 1: Assessment Overview
            Stat::make('Active Schools', $activeSchools . ' / ' . $totalSchools)
                ->description($schoolCompletionRate . '% completed assessments')
                ->descriptionIcon($schoolCompletionRate > 50 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($schoolCompletionRate > 75 ? 'success' : ($schoolCompletionRate > 50 ? 'warning' : 'danger'))
                ->chart($this->getSchoolCompletionChart()),

            Stat::make('In Progress', $inProgressAssessments)
                ->description('Assessments need attention')
                ->descriptionIcon('heroicon-m-clock')
                ->color($inProgressAssessments > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.school-assessments.index')),

            Stat::make('Completed', $completedAssessments)
                ->description('Ready for review')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getCompletionTrendChart()),

            Stat::make('Avg Score', number_format($averageScore, 2) . ' / 4.00')
                ->description($this->getScoreDescription($averageScore))
                ->descriptionIcon($this->getScoreIcon($averageScore))
                ->color($this->getScoreColor($averageScore))
                ->chart($this->getScoreTrendChart()),
        ];
    }

    private function getSchoolCompletionChart(): array
    {
        // Simple completion trend over last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $completed = SchoolAssessment::whereDate('updated_at', $date)
                ->where('status', 'approved')
                ->count();
            $data[] = $completed;
        }
        return $data;
    }

    private function getCompletionTrendChart(): array
    {
        // Weekly completion trend
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $completed = SchoolAssessment::whereDate('created_at', $date)->count();
            $data[] = $completed;
        }
        return $data;
    }

    private function getScoreTrendChart(): array
    {
        // Average score trend over time
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $avgScore = SchoolAssessment::whereDate('updated_at', $date)
                ->avg('total_score') ?? 0;
            $data[] = round($avgScore * 10); // Scale for better visualization
        }
        return $data;
    }

    private function getScoreDescription(float $score): string
    {
        return match (true) {
            $score >= 3.5 => 'Excellent performance',
            $score >= 2.5 => 'Good performance',
            $score >= 1.5 => 'Needs improvement',
            default => 'Critical attention needed'
        };
    }

    private function getScoreIcon(float $score): string
    {
        return match (true) {
            $score >= 3.5 => 'heroicon-m-trophy',
            $score >= 2.5 => 'heroicon-m-star',
            $score >= 1.5 => 'heroicon-m-exclamation-triangle',
            default => 'heroicon-m-x-circle'
        };
    }

    private function getScoreColor(float $score): string
    {
        return match (true) {
            $score >= 3.5 => 'success',
            $score >= 2.5 => 'info',
            $score >= 1.5 => 'warning',
            default => 'danger'
        };
    }
}
