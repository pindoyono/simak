<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class AssessmentCategoryTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        // Return example data for template
        return collect([
            [
                'Aspek Kepemimpinan',
                'Visi dan Misi Sekolah',
                'Penilaian terhadap visi dan misi sekolah yang jelas dan terukur',
                15.00,
                1,
                'Aktif'
            ],
            [
                'Aspek Kepemimpinan',
                'Perencanaan Strategis',
                'Kemampuan kepala sekolah dalam membuat perencanaan strategis jangka pendek dan panjang',
                10.00,
                2,
                'Aktif'
            ],
            [
                'Aspek Pembelajaran',
                'Kurikulum dan Program',
                'Kelengkapan dan implementasi kurikulum sesuai standar nasional',
                20.00,
                3,
                'Aktif'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Komponen',
            'Nama Kategori',
            'Deskripsi',
            'Bobot Penilaian (%)',
            'Urutan',
            'Status'
        ];
    }

    public function styles($sheet)
    {
        // Set header style
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
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

        // Set data rows style
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:F{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align for numeric and status columns
        $sheet->getStyle("D2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto height for all rows
        for ($i = 1; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Komponen
            'B' => 25,  // Nama Kategori
            'C' => 40,  // Deskripsi
            'D' => 18,  // Bobot Penilaian
            'E' => 12,  // Urutan
            'F' => 12,  // Status
        ];
    }

    public function title(): string
    {
        return 'Template Kategori Asesmen';
    }
}
