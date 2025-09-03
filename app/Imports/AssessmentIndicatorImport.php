<?php

namespace App\Imports;

use App\Models\AssessmentIndicator;
use App\Models\AssessmentCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;

class AssessmentIndicatorImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        // Cari Assessment Category berdasarkan nama_kategori
        $assessmentCategory = AssessmentCategory::where('nama_kategori', $row['nama_kategori'])->first();
        
        if (!$assessmentCategory) {
            throw new \Exception("Assessment Category '{$row['nama_kategori']}' tidak ditemukan.");
        }

        return new AssessmentIndicator([
            'assessment_category_id' => $assessmentCategory->id,
            'nama_indikator' => $row['nama_indikator'],
            'deskripsi' => $row['deskripsi'] ?? null,
            'bobot_indikator' => (float) ($row['bobot_indikator'] ?? 0),
            'kriteria_penilaian' => $row['kriteria_penilaian'] ?? null,
            'skor_maksimal' => (int) ($row['skor_maksimal'] ?? 4),
            'urutan' => (int) ($row['urutan'] ?? 0),
            'is_active' => $this->parseBoolean($row['is_active'] ?? true),
        ]);
    }

    public function rules(): array
    {
        $assessmentCategoryNames = AssessmentCategory::pluck('nama_kategori')->toArray();
        
        return [
            'nama_kategori' => [
                'required',
                'string',
                Rule::in($assessmentCategoryNames),
            ],
            'nama_indikator' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'bobot_indikator' => 'nullable|numeric|min:0|max:999.99',
            'kriteria_penilaian' => 'nullable|string',
            'skor_maksimal' => 'nullable|integer|min:1|max:10',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:1,0,true,false,ya,tidak,aktif,nonaktif',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori harus diisi.',
            'nama_kategori.in' => 'Nama kategori tidak valid atau tidak ditemukan.',
            'nama_indikator.required' => 'Nama indikator harus diisi.',
            'nama_indikator.max' => 'Nama indikator maksimal 255 karakter.',
            'bobot_indikator.numeric' => 'Bobot indikator harus berupa angka.',
            'bobot_indikator.max' => 'Bobot indikator maksimal 999.99.',
            'skor_maksimal.integer' => 'Skor maksimal harus berupa angka bulat.',
            'skor_maksimal.min' => 'Skor maksimal minimal 1.',
            'skor_maksimal.max' => 'Skor maksimal maksimal 10.',
            'urutan.integer' => 'Urutan harus berupa angka bulat.',
            'urutan.min' => 'Urutan minimal 0.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        
        return in_array($value, ['1', 'true', 'ya', 'aktif', '✓'], true);
    }
}
