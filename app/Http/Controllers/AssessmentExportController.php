<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolAssessment;
use App\Models\AssessmentScore;
use App\Exports\AssessmentDetailExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class AssessmentExportController extends Controller
{
    public function exportPDF($schoolAssessmentId)
    {
        try {
            $schoolAssessment = SchoolAssessment::with(['school', 'period', 'assessor'])->findOrFail($schoolAssessmentId);

            $assessmentScores = AssessmentScore::where('school_assessment_id', $schoolAssessmentId)
                ->with(['assessmentIndicator.category'])
                ->get()
                ->groupBy('assessmentIndicator.category.nama_kategori');

            $allScores = $assessmentScores->flatten();
            $totalScore = $allScores->sum('skor');
            $averageScore = $allScores->avg('skor');

            // Calculate total weighted score from all categories
            $totalWeightedScore = 0;
            foreach ($assessmentScores as $categoryName => $scores) {
                if ($scores->isNotEmpty()) {
                    $firstScore = $scores->first();
                    $categoryWeight = $firstScore &&
                                    $firstScore->assessmentIndicator &&
                                    $firstScore->assessmentIndicator->category
                        ? $firstScore->assessmentIndicator->category->bobot_penilaian
                        : 0;

                    $categoryAverage = $scores->avg('skor');
                    $weightedCategoryScore = $categoryAverage * ($categoryWeight / 100);
                    $totalWeightedScore += $weightedCategoryScore;
                }
            }

            $overallGrade = match (true) {
                $averageScore >= 3.5 => 'Sangat Baik',
                $averageScore >= 2.5 => 'Baik',
                $averageScore >= 1.5 => 'Cukup',
                default => 'Kurang',
            };

            $data = [
                'schoolAssessment' => $schoolAssessment,
                'assessmentScores' => $assessmentScores,
                'totalScore' => $totalScore,
                'averageScore' => $averageScore,
                'totalWeightedScore' => $totalWeightedScore,
                'overallGrade' => $overallGrade,
                'generatedAt' => now()->format('d M Y H:i:s')
            ];

            // Test if view exists
            if (!view()->exists('exports.assessment-report')) {
                Log::error('View exports.assessment-report not found');
                return response()->json(['error' => 'PDF template not found'], 404);
            }

            $pdf = Pdf::loadView('exports.assessment-report', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'SIMAK-PM_Assessment_Report_' . str_replace(' ', '_', $schoolAssessment->school->nama_sekolah) . '_' . date('Y-m-d') . '.pdf';

            // Stream PDF to browser (display inline in new tab)
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }

    public function exportExcel($schoolAssessmentId)
    {
        try {
            $schoolAssessment = SchoolAssessment::with(['school', 'period', 'assessor'])->findOrFail($schoolAssessmentId);

            $filename = 'SIMAK-PM_Assessment_Report_' . str_replace(' ', '_', $schoolAssessment->school->nama_sekolah) . '_' . date('Y-m-d') . '.xlsx';

            return Excel::download(new AssessmentDetailExport($schoolAssessmentId), $filename);

        } catch (\Exception $e) {
            Log::error('Excel Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate Excel: ' . $e->getMessage()], 500);
        }
    }
}
