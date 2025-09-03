<?php

namespace App\Imports;

use App\Models\AssessmentCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class AssessmentCategoryImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows, SkipsFailures
{
    private $importedCount = 0;
    private $skippedCount = 0;
    private $errors = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validasi data lebih efisien dengan early return
        $requiredFields = ['komponen', 'nama_kategori', 'bobot_penilaian'];
        
        foreach ($requiredFields as $field) {
            if (empty(trim($row[$field] ?? ''))) {
                $this->skippedCount++;
                return null;
            }
        }

        // Validasi duplikat nama kategori dalam komponen yang sama
        if (AssessmentCategory::where('komponen', trim($row['komponen']))
                               ->where('nama_kategori', trim($row['nama_kategori']))
                               ->exists()) {
            $this->skippedCount++;
            return null;
        }

        // Konversi status text ke boolean
        $status = strtolower(trim($row['status'] ?? 'aktif'));
        $isActive = in_array($status, ['aktif', 'active', '1', 'ya', 'yes', 'true']);

        // Validasi bobot penilaian
        $bobot = floatval($row['bobot_penilaian'] ?? 0);
        if ($bobot <= 0 || $bobot > 100) {
            $this->skippedCount++;
            return null;
        }

        $this->importedCount++;

        return new AssessmentCategory([
            'komponen' => trim($row['komponen']),
            'nama_kategori' => trim($row['nama_kategori']),
            'deskripsi' => trim($row['deskripsi'] ?? ''),
            'bobot_penilaian' => $bobot,
            'urutan' => intval($row['urutan'] ?? 1),
            'is_active' => $isActive,
        ]);
    }

    public function rules(): array
    {
        return [
            'komponen' => 'required|string|max:255',
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'bobot_penilaian' => 'required|numeric|between:0.01,100',
            'urutan' => 'nullable|integer|min:1',
            'status' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'komponen.required' => 'Komponen harus diisi',
            'nama_kategori.required' => 'Nama kategori harus diisi',
            'bobot_penilaian.required' => 'Bobot penilaian harus diisi',
            'bobot_penilaian.numeric' => 'Bobot penilaian harus berupa angka',
            'bobot_penilaian.between' => 'Bobot penilaian harus antara 0.01 dan 100',
        ];
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

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
