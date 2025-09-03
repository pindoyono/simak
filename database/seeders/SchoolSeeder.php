<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            [
                'nama_sekolah' => 'SDN 01 Jakarta Pusat',
                'npsn' => '20104001',
                'alamat' => 'Jl. Kebon Sirih No. 15',
                'kecamatan' => 'Menteng',
                'kabupaten_kota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'kepala_sekolah' => 'Dr. Siti Aminah, S.Pd., M.Pd.',
                'telepon' => '021-3456789',
                'email' => 'sdn01@jakarta.go.id',
            ],
            [
                'nama_sekolah' => 'SMPN 5 Bandung',
                'npsn' => '20219005',
                'alamat' => 'Jl. Veteran No. 25',
                'kecamatan' => 'Coblong',
                'kabupaten_kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'jenjang' => 'SMP',
                'status' => 'Negeri',
                'kepala_sekolah' => 'Drs. Ahmad Fauzi, M.Pd.',
                'telepon' => '022-2345678',
                'email' => 'smpn5@bandung.go.id',
            ],
            [
                'nama_sekolah' => 'SMAN 2 Surabaya',
                'npsn' => '20538002',
                'alamat' => 'Jl. Raya Gubeng No. 40',
                'kecamatan' => 'Gubeng',
                'kabupaten_kota' => 'Surabaya',
                'provinsi' => 'Jawa Timur',
                'jenjang' => 'SMA',
                'status' => 'Negeri',
                'kepala_sekolah' => 'Dr. Rina Wijayanti, S.Pd., M.Si.',
                'telepon' => '031-5678901',
                'email' => 'sman2@surabaya.go.id',
            ],
            [
                'nama_sekolah' => 'SMKN 1 Yogyakarta',
                'npsn' => '20404001',
                'alamat' => 'Jl. Malioboro No. 55',
                'kecamatan' => 'Gondokusuman',
                'kabupaten_kota' => 'Yogyakarta',
                'provinsi' => 'DI Yogyakarta',
                'jenjang' => 'SMK',
                'status' => 'Negeri',
                'kepala_sekolah' => 'Dra. Indah Sari, M.Pd.',
                'telepon' => '0274-123456',
                'email' => 'smkn1@yogya.go.id',
            ],
            [
                'nama_sekolah' => 'SMA Unggulan Al-Azhar',
                'npsn' => '20104099',
                'alamat' => 'Jl. Sisingamangaraja No. 10',
                'kecamatan' => 'Kebayoran Baru',
                'kabupaten_kota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'jenjang' => 'SMA',
                'status' => 'Swasta',
                'kepala_sekolah' => 'Prof. Dr. Abdullah Rahman, M.A.',
                'telepon' => '021-7654321',
                'email' => 'info@alazhar.sch.id',
            ]
        ];

        foreach ($schools as $school) {
            School::create($school);
        }
    }
}
