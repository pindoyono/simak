<?php

namespace App\Exports;

use App\Models\AssessmentCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AssessmentCategoryExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return AssessmentCategory::all();
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->komponen,
            $category->nama_kategori,
            $category->deskripsi,
            $category->bobot_penilaian,
            $category->urutan,
            $category->is_active ? 'Aktif' : 'Tidak Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Komponen',
            'Nama Kategori',
            'Deskripsi',
            'Bobot Penilaian (%)',
            'Urutan',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Daftar Kategori Asesmen';
    }
}
