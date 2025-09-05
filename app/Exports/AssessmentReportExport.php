<?php

namespace App\Exports;

use App\Models\AssessmentScore;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssessmentReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = AssessmentScore::query()
            ->with([
                'schoolAssessment.school',
                'schoolAssessment.period',
                'assessmentIndicator.category'
            ]);

        // Apply filters
        if (isset($this->filters['school_id'])) {
            $query->whereHas('schoolAssessment.school', function ($q) {
                $q->where('id', $this->filters['school_id']);
            });
        }

        if (isset($this->filters['period_id'])) {
            $query->whereHas('schoolAssessment.period', function ($q) {
                $q->where('id', $this->filters['period_id']);
            });
        }

        if (isset($this->filters['category_id'])) {
            $query->whereHas('assessmentIndicator.category', function ($q) {
                $q->where('id', $this->filters['category_id']);
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Sekolah',
            'NPSN',
            'Periode',
            'Kategori',
            'Indikator',
            'Skor',
            'Skor Maksimal',
            'Persentase',
            'Grade',
            'Bobot',
            'Skor Berbobot',
            'Tanggal Penilaian',
        ];
    }

    public function map($score): array
    {
        $indicator = $score->assessmentIndicator;
        $maxScore = $indicator->skor_maksimal ?? 4;
        $weight = $indicator->bobot_indikator ?? 1;
        $percentage = $maxScore > 0 ? ($score->skor / $maxScore) * 100 : 0;
        $weightedScore = $percentage * ($weight / 100);

        return [
            $score->schoolAssessment->school->nama_sekolah,
            $score->schoolAssessment->school->npsn ?? 'N/A',
            $score->schoolAssessment->period->nama_periode,
            $indicator->category->nama_kategori,
            $indicator->nama_indikator,
            $score->skor,
            $maxScore,
            round($percentage, 2) . '%',
            $this->getGrade($percentage),
            $weight . '%',
            round($weightedScore, 2),
            $score->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '366092'],
                ],
            ],
        ];
    }

    private function getGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 85 => 'A - Sangat Baik',
            $percentage >= 70 => 'B - Baik',
            $percentage >= 55 => 'C - Cukup',
            default => 'D - Kurang',
        };
    }
}
