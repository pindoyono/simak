<?php

namespace App\Imports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Validation\Rule;

class SchoolImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $importedCount = 0;
    protected $skippedCount = 0;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validasi data lebih efisien dengan early return
        $requiredFields = ['nama_sekolah', 'npsn', 'alamat'];

        foreach ($requiredFields as $field) {
            if (empty(trim($row[$field] ?? ''))) {
                $this->skippedCount++;
                return null;
            }
        }

        $this->importedCount++;

        return new School([
            'nama_sekolah' => trim($row['nama_sekolah']),
            'npsn' => trim($row['npsn']),
            'alamat' => trim($row['alamat']),
            'kecamatan' => trim($row['kecamatan'] ?? ''),
            'kabupaten_kota' => trim($row['kabupaten_kota'] ?? ''),
            'provinsi' => trim($row['provinsi'] ?? ''),
            'jenjang' => trim($row['jenjang'] ?? 'SD'),
            'status' => trim($row['status'] ?? 'Negeri'),
            'kepala_sekolah' => trim($row['kepala_sekolah'] ?? ''),
            'telepon' => !empty(trim($row['telepon'] ?? '')) ? trim($row['telepon']) : null,
            'email' => !empty(trim($row['email'] ?? '')) ? trim($row['email']) : null,
            'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama_sekolah' => ['nullable'], // Will be checked in model() method
            'npsn' => ['nullable'], // Will be checked in model() method
            'alamat' => ['nullable'], // Will be checked in model() method
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'jenjang' => ['nullable', 'string', Rule::in(['SD', 'SMP', 'SMA', 'SMK', 'TK', 'PAUD'])],
            'status' => ['nullable', 'string', Rule::in(['Negeri', 'Swasta'])],
            'kepala_sekolah' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Get custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'nama_sekolah.required' => 'Nama sekolah wajib diisi.',
            'npsn.required' => 'NPSN wajib diisi.',
            'npsn.unique' => 'NPSN sudah ada dalam database.',
            'jenjang.in' => 'Jenjang harus salah satu dari: SD, SMP, SMA, SMK, TK, PAUD.',
            'status.in' => 'Status harus salah satu dari: Negeri, Swasta.',
            'email.email' => 'Format email tidak valid.',
        ];
    }

    /**
     * Get the count of imported rows
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get the count of skipped rows
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
