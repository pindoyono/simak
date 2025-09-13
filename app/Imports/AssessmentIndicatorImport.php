<?php

namespace App\Imports;

use App\Models\AssessmentIndicator;
use App\Models\AssessmentCategory;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Illuminate\Validation\Rule;

class AssessmentIndicatorImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsEmptyRows, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    private $importedCount = 0;
    private $skippedCount = 0;

    public function model(array $row)
    {
        Log::info('Processing row: ', $row);
        
        // Skip baris kosong atau yang hanya berisi null values
        $filteredRow = array_filter($row, function($value) {
            return $value !== null && $value !== '';
        });
        
        if (empty($filteredRow) || !isset($row['category_id']) || !isset($row['nama_indikator'])) {
            Log::info('Skipping empty or incomplete row');
            $this->skippedCount++;
            return null;
        }

        // Validasi category exists
        $assessmentCategory = AssessmentCategory::find($row['category_id']);
        if (!$assessmentCategory) {
            Log::error("Assessment Category dengan ID '{$row['category_id']}' tidak ditemukan.");
            $this->skippedCount++;
            return null;
        }

        Log::info('Creating indicator with category ID: ' . $assessmentCategory->id);
        $this->importedCount++;

        return new AssessmentIndicator([
            'assessment_category_id' => $assessmentCategory->id,
            'nama_indikator' => $row['nama_indikator'],
            'deskripsi' => $row['deskripsi'] ?? null,
            'bobot_indikator' => (float) ($row['bobot_indikator'] ?? 0),
            'kriteria_penilaian' => $row['kriteria_penilaian'] ?? null,
            'skor_maksimal' => (int) ($row['skor_maksimal'] ?? 4),
            'urutan' => (int) ($row['urutan'] ?? 1),
            'is_active' => $this->parseBoolean($row['is_active'] ?? true),
            // Kolom-kolom baru
            'kegiatan' => $row['kegiatan'] ?? null,
            'sumber_data' => $row['sumber_data'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
            'kriteria_sangat_baik' => $row['kriteria_sangat_baik'] ?? null,
            'kriteria_baik' => $row['kriteria_baik'] ?? null,
            'kriteria_cukup' => $row['kriteria_cukup'] ?? null,
            'kriteria_kurang' => $row['kriteria_kurang'] ?? null,
        ]);
    }

    public function rules(): array
    {
        $assessmentCategoryIds = AssessmentCategory::pluck('id')->toArray();

        return [
            'category_id' => ['nullable', 'numeric', Rule::in($assessmentCategoryIds)],
            'nama_indikator' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'bobot_indikator' => 'nullable|numeric|min:0|max:999.99',
            'kriteria_penilaian' => 'nullable|string',
            'skor_maksimal' => 'nullable|numeric|min:1|max:10',
            'urutan' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|in:1,0,true,false,ya,tidak,aktif,nonaktif',
            // Validasi untuk kolom-kolom baru
            'kegiatan' => 'nullable|string',
            'sumber_data' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'kriteria_sangat_baik' => 'nullable|string',
            'kriteria_baik' => 'nullable|string',
            'kriteria_cukup' => 'nullable|string',
            'kriteria_kurang' => 'nullable|string',
        ];
    }

    public function prepareForValidation($data, $index)
    {
        // Skip validation for empty rows
        $filteredData = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        if (empty($filteredData)) {
            return [];
        }
        
        return $data;
    }

    public function customValidationMessages(): array
    {
        return [
            'category_id.required' => 'ID kategori harus diisi.',
            'category_id.numeric' => 'ID kategori harus berupa angka.',
            'category_id.in' => 'ID kategori tidak valid atau tidak ditemukan.',
            'nama_indikator.required' => 'Nama indikator harus diisi.',
            'bobot_indikator.numeric' => 'Bobot indikator harus berupa angka.',
            'bobot_indikator.max' => 'Bobot indikator maksimal 999.99.',
            'skor_maksimal.numeric' => 'Skor maksimal harus berupa angka.',
            'skor_maksimal.min' => 'Skor maksimal minimal 1.',
            'skor_maksimal.max' => 'Skor maksimal maksimal 10.',
            'urutan.numeric' => 'Urutan harus berupa angka.',
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

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'ya', 'aktif', '✓'], true);
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        Log::error('Import failures detected:');
        foreach ($failures as $failure) {
            Log::error('Row: ' . $failure->row() . ', Errors: ' . implode(', ', $failure->errors()));
        }
    }

    public function onError(\Throwable $error)
    {
        Log::error('Import error: ' . $error->getMessage());
        Log::error('Error trace: ' . $error->getTraceAsString());
    }
}
