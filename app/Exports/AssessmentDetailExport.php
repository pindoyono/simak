<?php

namespace App\Exports;

use App\Models\SchoolAssessment;
use App\Models\AssessmentScore;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AssessmentDetailExport implements WithMultipleSheets
{
    protected $schoolAssessment;
    protected $assessmentScores;
    protected $totalWeightedScore;

    public function __construct($schoolAssessmentId)
    {
        $this->schoolAssessment = SchoolAssessment::with(['school', 'period', 'assessor'])->findOrFail($schoolAssessmentId);

        $this->assessmentScores = AssessmentScore::where('school_assessment_id', $schoolAssessmentId)
            ->with(['assessmentIndicator.category'])
            ->get()
            ->groupBy('assessmentIndicator.category.nama_kategori');

        // Calculate total weighted score
        $this->totalWeightedScore = 0;
        foreach ($this->assessmentScores as $categoryName => $scores) {
            if ($scores->isNotEmpty()) {
                $firstScore = $scores->first();
                $categoryWeight = $firstScore &&
                                $firstScore->assessmentIndicator &&
                                $firstScore->assessmentIndicator->category
                    ? $firstScore->assessmentIndicator->category->bobot_penilaian
                    : 0;

                $categoryAverage = $scores->avg('skor');
                $weightedCategoryScore = $categoryAverage * ($categoryWeight / 100);
                $this->totalWeightedScore += $weightedCategoryScore;
            }
        }
    }

    public function sheets(): array
    {
        return [
            new AssessmentSummarySheet($this->schoolAssessment, $this->assessmentScores, $this->totalWeightedScore),
            new ComponentBreakdownSheet($this->schoolAssessment, $this->assessmentScores, $this->totalWeightedScore),
            new DetailedScoresSheet($this->schoolAssessment, $this->assessmentScores, $this->totalWeightedScore),
        ];
    }
}

class AssessmentSummarySheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $schoolAssessment;
    protected $assessmentScores;
    protected $totalWeightedScore;

    public function __construct($schoolAssessment, $assessmentScores, $totalWeightedScore)
    {
        $this->schoolAssessment = $schoolAssessment;
        $this->assessmentScores = $assessmentScores;
        $this->totalWeightedScore = $totalWeightedScore;
    }

    public function title(): string
    {
        return 'Ringkasan Assessment';
    }

    public function array(): array
    {
        $allScores = $this->assessmentScores->flatten();
        $averageScore = $allScores->avg('skor');

        $overallGrade = match (true) {
            $averageScore >= 3.5 => 'Sangat Baik',
            $averageScore >= 2.5 => 'Baik',
            $averageScore >= 1.5 => 'Cukup',
            default => 'Kurang',
        };

        return [
            ['LAPORAN ASSESSMENT SIMAK-PM'],
            [''],
            ['Informasi Sekolah'],
            ['Nama Sekolah', $this->schoolAssessment->school->nama_sekolah],
            ['NPSN', $this->schoolAssessment->school->npsn],
            ['Alamat', $this->schoolAssessment->school->alamat],
            [''],
            ['Informasi Assessment'],
            ['Periode', $this->schoolAssessment->period->nama_periode],
            ['Tanggal Mulai', $this->schoolAssessment->tanggal_mulai],
            ['Tanggal Selesai', $this->schoolAssessment->tanggal_selesai],
            ['Assessor', $this->schoolAssessment->assessor->name ?? 'N/A'],
            ['Status', $this->schoolAssessment->status],
            [''],
            ['Hasil Assessment'],
            ['Total Skor', number_format($allScores->sum('skor'), 2)],
            ['Rata-rata Skor', number_format($averageScore, 2)],
            ['Total Hasil Penilaian (Berbobot)', number_format($this->totalWeightedScore, 3)],
            ['Nilai Keseluruhan', $overallGrade],
            [''],
            ['Digenerate pada', now()->format('d M Y H:i:s')],
        ];
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => ['font' => ['bold' => true, 'size' => 12]],
            8 => ['font' => ['bold' => true, 'size' => 12]],
            15 => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:B1');
                $event->sheet->getDelegate()->getStyle('A1:B1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2563eb']
                    ],
                    'font' => ['color' => ['rgb' => 'FFFFFF']]
                ]);
            },
        ];
    }
}

class ComponentBreakdownSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $schoolAssessment;
    protected $assessmentScores;
    protected $totalWeightedScore;

    public function __construct($schoolAssessment, $assessmentScores, $totalWeightedScore)
    {
        $this->schoolAssessment = $schoolAssessment;
        $this->assessmentScores = $assessmentScores;
        $this->totalWeightedScore = $totalWeightedScore;
    }

    public function title(): string
    {
        return 'Breakdown Per Komponen';
    }

    public function array(): array
    {
        // Group categories by component
        $componentGroups = [];
        $componentTotals = [];

        foreach ($this->assessmentScores as $categoryName => $scores) {
            if ($scores->isNotEmpty()) {
                $firstScore = $scores->first();
                $categoryComponent = $firstScore &&
                                   $firstScore->assessmentIndicator &&
                                   $firstScore->assessmentIndicator->category
                    ? $firstScore->assessmentIndicator->category->komponen
                    : 'Unknown';

                $categoryWeight = $firstScore &&
                                $firstScore->assessmentIndicator &&
                                $firstScore->assessmentIndicator->category
                    ? $firstScore->assessmentIndicator->category->bobot_penilaian
                    : 0;

                $categoryAverage = $scores->avg('skor');
                $weightedCategoryScore = $categoryAverage * ($categoryWeight / 100);

                // Map components to display names
                $componentDisplayName = match($categoryComponent) {
                    'MANAGEMENT KEPALA SEKOLAH' => 'Kepemimpinan Kepala Sekolah',
                    'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)' => 'Pelanggan (Siswa, Orang Tua, dan Masyarakat)',
                    'PENGUKURAN, ANALISIS DAN MANAGAMEN PENGETAHUAN' => 'Pengukuran, Analisis, dan Manajemen Pengetahuan',
                    'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)' => 'Tenaga Kerja (Tenaga Pendidik dan Kependidikan)',
                    'PROSES' => 'Proses (Operasional)',
                    'SISWA' => 'Siswa',
                    'GURU' => 'Guru',
                    'KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN' => 'Kinerja Guru dalam Mengelola Proses Pembelajaran',
                    'HASIL PRODUK DAN/ATAU LAYANAN' => 'Hasil Produk dan/atau Layanan',
                    default => $categoryComponent
                };

                if (!isset($componentGroups[$componentDisplayName])) {
                    $componentGroups[$componentDisplayName] = [];
                    $componentTotals[$componentDisplayName] = [
                        'total_weight' => 0,
                        'total_weighted_score' => 0,
                        'category_count' => 0,
                        'total_avg_score' => 0
                    ];
                }

                $componentGroups[$componentDisplayName][] = [
                    'category_name' => $categoryName,
                    'average' => $categoryAverage,
                    'weight' => $categoryWeight,
                    'weighted_score' => $weightedCategoryScore,
                    'indicator_count' => $scores->count()
                ];

                $componentTotals[$componentDisplayName]['total_weight'] += $categoryWeight;
                $componentTotals[$componentDisplayName]['total_weighted_score'] += $weightedCategoryScore;
                $componentTotals[$componentDisplayName]['category_count']++;
                $componentTotals[$componentDisplayName]['total_avg_score'] += $categoryAverage;
            }
        }

        // Calculate component averages and contributions
        foreach ($componentTotals as $component => $totals) {
            $componentTotals[$component]['avg_score'] = $totals['category_count'] > 0
                ? $totals['total_avg_score'] / $totals['category_count']
                : 0;
            $componentTotals[$component]['contribution'] = $this->totalWeightedScore > 0
                ? ($totals['total_weighted_score'] / $this->totalWeightedScore) * 100
                : 0;
        }

        $data = [
            ['BREAKDOWN SKOR BERBOBOT PER KOMPONEN'],
            [''],
            ['Komponen', 'Jumlah Kategori', 'Bobot Total (%)', 'Skor Berbobot', 'Kontribusi (%)'],
        ];

        foreach ($componentTotals as $componentName => $totals) {
            $data[] = [
                $componentName,
                $totals['category_count'],
                number_format($totals['total_weight'], 1),
                number_format($totals['total_weighted_score'], 3),
                number_format($totals['contribution'], 1)
            ];
        }

        $data[] = ['TOTAL', '', '', number_format($this->totalWeightedScore, 3), '100.0'];
        $data[] = [''];
        $data[] = ['DETAIL KATEGORI PER KOMPONEN'];
        $data[] = [''];

        foreach ($componentGroups as $componentName => $categories) {
            $data[] = [$componentName];
            $data[] = ['Kategori', 'Rata-rata Skor', 'Bobot (%)', 'Skor Berbobot', 'Jumlah Indikator'];

            foreach ($categories as $category) {
                $data[] = [
                    $category['category_name'],
                    number_format($category['average'], 2),
                    number_format($category['weight'], 1),
                    number_format($category['weighted_score'], 3),
                    $category['indicator_count']
                ];
            }
            $data[] = [''];
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'e3f2fd']
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:E1');
                $event->sheet->getDelegate()->getStyle('A1:E1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2563eb']
                    ],
                    'font' => ['color' => ['rgb' => 'FFFFFF']]
                ]);
            },
        ];
    }
}

class DetailedScoresSheet implements FromArray, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $schoolAssessment;
    protected $assessmentScores;
    protected $totalWeightedScore;

    public function __construct($schoolAssessment, $assessmentScores, $totalWeightedScore)
    {
        $this->schoolAssessment = $schoolAssessment;
        $this->assessmentScores = $assessmentScores;
        $this->totalWeightedScore = $totalWeightedScore;
    }

    public function title(): string
    {
        return 'Detail Skor Per Indikator';
    }

    public function array(): array
    {
        $data = [
            ['DETAIL SKOR PER INDIKATOR'],
            [''],
            ['Kategori', 'Indikator', 'Skor', 'Nilai', 'Catatan'],
        ];

        foreach ($this->assessmentScores as $categoryName => $scores) {
            $data[] = [$categoryName, '', '', '', ''];

            foreach ($scores as $score) {
                $gradeDisplay = match ($score->grade) {
                    'Sangat Baik', 'A' => 'Sangat Baik',
                    'Baik', 'B' => 'Baik',
                    'Cukup', 'C' => 'Cukup',
                    'Kurang', 'D' => 'Kurang',
                    default => $score->grade,
                };

                $data[] = [
                    '',
                    $score->assessmentIndicator->nama_indikator,
                    number_format($score->skor, 2),
                    $gradeDisplay,
                    $score->catatan ?: '-'
                ];
            }

            // Category summary
            $categoryAverage = $scores->avg('skor');
            $categoryGrade = match (true) {
                $categoryAverage >= 3.5 => 'Sangat Baik',
                $categoryAverage >= 2.5 => 'Baik',
                $categoryAverage >= 1.5 => 'Cukup',
                default => 'Kurang',
            };

            $data[] = [
                'Rata-rata Kategori:',
                '',
                number_format($categoryAverage, 2),
                $categoryGrade,
                ''
            ];
            $data[] = [''];
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'e3f2fd']
                ]
            ],
        ];
    }
}
