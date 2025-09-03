<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\AssessmentPeriod;

class AssessmentDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample schools if not exist
        $schools = [
            [
                'nama_sekolah' => 'SDN 01 Jakarta Pusat',
                'npsn' => '20100001',
                'alamat' => 'Jl. Tanah Abang I No. 1',
                'kecamatan' => 'Tanah Abang',
                'kabupaten_kota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'kepala_sekolah' => 'Dr. Ahmad Sutrisno, M.Pd',
                'is_active' => true,
            ],
            [
                'nama_sekolah' => 'SDN 02 Jakarta Pusat',
                'npsn' => '20100002',
                'alamat' => 'Jl. Kebon Kacang Raya No. 15',
                'kecamatan' => 'Tanah Abang',
                'kabupaten_kota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'kepala_sekolah' => 'Dra. Siti Nurhalimah, M.Pd',
                'is_active' => true,
            ],
            [
                'nama_sekolah' => 'SMPN 1 Jakarta Pusat',
                'npsn' => '20200001',
                'alamat' => 'Jl. Budi Kemuliaan No. 7',
                'kecamatan' => 'Gambir',
                'kabupaten_kota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'jenjang' => 'SMP',
                'status' => 'Negeri',
                'kepala_sekolah' => 'Dr. Bambang Wahyudi, M.Pd',
                'is_active' => true,
            ],
        ];

        foreach ($schools as $school) {
            School::firstOrCreate(['npsn' => $school['npsn']], $school);
        }

        // Create sample assessment periods if not exist
        $periods = [
            [
                'name' => 'Assessment Period 2024/2025 Semester 1',
                'academic_year' => '2024/2025',
                'semester' => 'Ganjil',
                'start_date' => '2024-07-01',
                'end_date' => '2024-12-31',
                'status' => 'aktif',
                'description' => 'Periode penilaian semester ganjil tahun ajaran 2024/2025',
            ],
            [
                'name' => 'Assessment Period 2024/2025 Semester 2',
                'academic_year' => '2024/2025',
                'semester' => 'Genap',
                'start_date' => '2025-01-01',
                'end_date' => '2025-06-30',
                'status' => 'aktif',
                'description' => 'Periode penilaian semester genap tahun ajaran 2024/2025',
            ],
            [
                'name' => 'Assessment Period 2025/2026 Semester 1',
                'academic_year' => '2025/2026',
                'semester' => 'Ganjil',
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'status' => 'draft',
                'description' => 'Periode penilaian semester ganjil tahun ajaran 2025/2026',
            ],
        ];

        foreach ($periods as $period) {
            AssessmentPeriod::firstOrCreate(['name' => $period['name']], $period);
        }
    }
}
