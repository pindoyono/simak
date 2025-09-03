<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class SchoolTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        // Return sample data untuk template
        return [
            [
                'SDN 001 Contoh',
                '20123456',
                'Jl. Pendidikan No. 123, RT 01/RW 02',
                'Cibinong',
                'Bogor',
                'Jawa Barat',
                'SD',
                'Negeri',
                'Drs. John Doe, M.Pd',
                '021-12345678',
                'sdn001@email.com',
                '1'
            ],
            [
                'SMP Swasta Harapan',
                '20123457',
                'Jl. Harapan Bangsa No. 456, RT 03/RW 04',
                'Bekasi Utara',
                'Bekasi',
                'Jawa Barat',
                'SMP',
                'Swasta',
                'Dr. Jane Smith, S.Pd, M.M',
                '021-87654321',
                'smp.harapan@email.com',
                '1'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'nama_sekolah',
            'npsn',
            'alamat',
            'kecamatan',
            'kabupaten_kota',
            'provinsi',
            'jenjang',
            'status',
            'kepala_sekolah',
            'telepon',
            'email',
            'is_active'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:L1')->applyFromArray([
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

        // Data rows style
        $sheet->getStyle('A2:L3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Auto-fit row height
        $sheet->getDefaultRowDimension()->setRowHeight(-1);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // nama_sekolah
            'B' => 15, // npsn
            'C' => 40, // alamat
            'D' => 20, // kecamatan
            'E' => 20, // kabupaten_kota
            'F' => 15, // provinsi
            'G' => 10, // jenjang
            'H' => 10, // status
            'I' => 25, // kepala_sekolah
            'J' => 15, // telepon
            'K' => 25, // email
            'L' => 10, // is_active
        ];
    }

    public function title(): string
    {
        return 'Template Data Sekolah';
    }
}
