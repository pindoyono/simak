<?php

namespace App\Exports;

use App\Models\AssessmentCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AssessmentIndicatorTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        // Ambil beberapa kategori sebagai contoh
        $categories = AssessmentCategory::select('nama_kategori', 'komponen')->limit(4)->get();
        
        $data = collect();
        
        foreach ($categories as $category) {
            $data->push([
                'nama_kategori' => $category->nama_kategori,
                'nama_indikator' => 'Contoh Indikator untuk ' . $category->nama_kategori,
                'deskripsi' => 'Deskripsi detail mengenai indikator penilaian ini',
                'bobot_indikator' => '25.00',
                'kriteria_penilaian' => 'Kriteria: Sangat Baik (4), Baik (3), Cukup (2), Kurang (1)',
                'skor_maksimal' => '4',
                'urutan' => '1',
                'is_active' => 'ya',
            ]);
        }
        
        return $data;
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

        // Set tinggi baris
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // nama_kategori
            'B' => 30, // nama_indikator
            'C' => 40, // deskripsi
            'D' => 15, // bobot_indikator
            'E' => 50, // kriteria_penilaian
            'F' => 12, // skor_maksimal
            'G' => 10, // urutan
            'H' => 12, // is_active
        ];
    }

    public function title(): string
    {
        return 'Template Assessment Indicator';
    }
}
