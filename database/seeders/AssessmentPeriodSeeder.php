<?php

namespace Database\Seeders;

use App\Models\AssessmentPeriod;
use Illuminate\Database\Seeder;

class AssessmentPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AssessmentPeriod::create([
            'nama_periode' => 'Assessment Period 2024/2025 Semester 1',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2024-08-01',
            'tanggal_selesai' => '2024-12-31',
            'status' => 'aktif',
            'deskripsi' => 'Assessment period for first semester of academic year 2024/2025',
        ]);

        AssessmentPeriod::create([
            'nama_periode' => 'Assessment Period 2024/2025 Semester 2',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'Genap',
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-06-30',
            'status' => 'draft',
            'deskripsi' => 'Assessment period for second semester of academic year 2024/2025',
        ]);

        AssessmentPeriod::create([
            'nama_periode' => 'Assessment Period 2023/2024 Annual',
            'tahun_ajaran' => '2023/2024',
            'semester' => 'Tahunan',
            'tanggal_mulai' => '2023-08-01',
            'tanggal_selesai' => '2024-07-31',
            'status' => 'selesai',
            'deskripsi' => 'Annual assessment period for academic year 2023/2024',
        ]);
    }
}
