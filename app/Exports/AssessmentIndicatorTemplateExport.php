<?php

namespace App\Exports;

use App\Models\AssessmentCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AssessmentIndicatorTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Template Import' => new TemplateSheet(),
            'Daftar Kategori' => new CategoryListSheet(),
        ];
    }
}

class TemplateSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        // Return empty collection for clean template
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'category_id',
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
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header - sekarang 15 kolom (A1:O1)
        $sheet->getStyle('A1:O1')->applyFromArray([
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

        // Style untuk data - sekarang 15 kolom (A2:O{lastRow})
        $lastRow = $sheet->getHighestRow();
        if ($lastRow < 2) $lastRow = 100; // Default untuk template kosong
        $sheet->getStyle("A2:O{$lastRow}")->applyFromArray([
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

        // Set tinggi baris
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // category_id
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
        ];
    }

    public function title(): string
    {
        return 'Template Assessment Indicator';
    }
}

class CategoryListSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        $categories = AssessmentCategory::select('id', 'is_active')
            ->orderBy('urutan')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'status' => $category->is_active ? 'Aktif' : 'Tidak Aktif',
                ];
            });

        // Add instruction row at the top
        $instruction = collect([
            [
                'id' => 'PENTING:',
                'status' => 'Gunakan angka ID ini untuk mengisi template import (contoh: 1, 2, 3)',
            ]
        ]);

        return $instruction->concat($categories);
    }

    public function headings(): array
    {
        return [
            'ID Kategori',
            'Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
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
        $sheet->getStyle("A2:D{$lastRow}")->applyFromArray([
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

        // Center align ID column
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D2:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set tinggi baris
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // ID Kategori
            'B' => 30, // Nama Kategori
            'C' => 50, // Deskripsi
            'D' => 15, // Status
        ];
    }
}
