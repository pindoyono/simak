<?php

namespace App\Exports;

use App\Models\AssessmentIndicator;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AssessmentIndicatorExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithMapping
{
    public function collection()
    {
        return AssessmentIndicator::with('category')
            ->orderBy('assessment_category_id')
            ->orderBy('urutan')
            ->get();
    }

    public function map($indicator): array
    {
        return [
            $indicator->category->nama_kategori,
            $indicator->nama_indikator,
            $indicator->deskripsi,
            $indicator->bobot_indikator,
            $indicator->kriteria_penilaian,
            $indicator->skor_maksimal,
            $indicator->urutan,
            $indicator->is_active ? 'ya' : 'tidak',
            // Kolom-kolom baru
            strip_tags($indicator->kegiatan ?? ''),
            strip_tags($indicator->sumber_data ?? ''),
            $indicator->keterangan ?? '',
            strip_tags($indicator->kriteria_sangat_baik ?? ''),
            strip_tags($indicator->kriteria_baik ?? ''),
            strip_tags($indicator->kriteria_cukup ?? ''),
            strip_tags($indicator->kriteria_kurang ?? ''),
        ];
    }

    public function headings(): array
    {
        return [
            'nama_kategori',
            'nama_indikator',
            'deskripsi',
            'bobot_indikator',
            'kriteria_penilaian',
            'skor_maksimal',
            'urutan',
            'is_active',
            // Kolom-kolom baru
            'kegiatan',
            'sumber_data',
            'keterangan',
            'kriteria_sangat_baik',
            'kriteria_baik',
            'kriteria_cukup',
            'kriteria_kurang',
            'is_active',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style untuk data
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle("A2:H{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);

            // Style khusus untuk kolom angka
            $sheet->getStyle("D2:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Set tinggi baris
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // nama_kategori
            'B' => 50, // nama_indikator (increased for longer text)
            'C' => 40, // deskripsi
            'D' => 15, // bobot_indikator
            'E' => 50, // kriteria_penilaian
            'F' => 12, // skor_maksimal
            'G' => 10, // urutan
            'H' => 12, // is_active
            // Kolom-kolom baru
            'I' => 40, // kegiatan
            'J' => 40, // sumber_data
            'K' => 40, // keterangan
            'L' => 40, // kriteria_sangat_baik
            'M' => 40, // kriteria_baik
            'N' => 40, // kriteria_cukup
            'O' => 40, // kriteria_kurang
            'E' => 50, // kriteria_penilaian
            'F' => 12, // skor_maksimal
            'G' => 10, // urutan
            'H' => 12, // is_active
        ];
    }

    public function title(): string
    {
        return 'Assessment Indicator Data';
    }
}
