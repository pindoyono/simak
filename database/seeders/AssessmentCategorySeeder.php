<?php

namespace Database\Seeders;

use App\Models\AssessmentCategory;
use App\Models\AssessmentIndicator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssessmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'komponen' => 'SISWA',
                'nama_kategori' => 'Standar Isi',
                'deskripsi' => 'Penilaian terhadap standar isi dan kurikulum sekolah',
                'bobot_penilaian' => 15.00,
                'urutan' => 1,
                'indicators' => [
                    'Kelengkapan perangkat pembelajaran',
                    'Kesesuaian kurikulum dengan standar nasional',
                    'Variasi metode pembelajaran',
                ]
            ],
            [
                'komponen' => 'GURU',
                'nama_kategori' => 'Standar Proses',
                'deskripsi' => 'Penilaian terhadap proses pembelajaran di sekolah',
                'bobot_penilaian' => 20.00,
                'urutan' => 2,
                'indicators' => [
                    'Kualitas perencanaan pembelajaran',
                    'Pelaksanaan pembelajaran',
                    'Penilaian hasil belajar',
                ]
            ],
            [
                'komponen' => 'KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN',
                'nama_kategori' => 'Standar Kompetensi Lulusan',
                'deskripsi' => 'Penilaian terhadap kompetensi lulusan',
                'bobot_penilaian' => 20.00,
                'urutan' => 3,
                'indicators' => [
                    'Pencapaian kompetensi siswa',
                    'Prestasi akademik',
                    'Prestasi non-akademik',
                ]
            ],
            [
                'komponen' => 'MANAGEMENT KEPALA SEKOLAH',
                'nama_kategori' => 'Standar Pendidik dan Tenaga Kependidikan',
                'deskripsi' => 'Penilaian terhadap kualitas pendidik dan tenaga kependidikan',
                'bobot_penilaian' => 15.00,
                'urutan' => 4,
                'indicators' => [
                    'Kualifikasi pendidik',
                    'Kompetensi pendidik',
                    'Kinerja tenaga kependidikan',
                ]
            ],
            [
                'komponen' => 'SISWA',
                'nama_kategori' => 'Standar Sarana dan Prasarana',
                'deskripsi' => 'Penilaian terhadap sarana dan prasarana sekolah',
                'bobot_penilaian' => 15.00,
                'urutan' => 5,
                'indicators' => [
                    'Kelengkapan sarana pembelajaran',
                    'Kondisi prasarana sekolah',
                    'Pemanfaatan sarana prasarana',
                ]
            ],
            [
                'komponen' => 'MANAGEMENT KEPALA SEKOLAH',
                'nama_kategori' => 'Standar Pengelolaan',
                'deskripsi' => 'Penilaian terhadap pengelolaan sekolah',
                'bobot_penilaian' => 10.00,
                'urutan' => 6,
                'indicators' => [
                    'Perencanaan program sekolah',
                    'Pelaksanaan rencana kerja',
                    'Pengawasan dan evaluasi',
                ]
            ],
            [
                'komponen' => 'MANAGEMENT KEPALA SEKOLAH',
                'nama_kategori' => 'Standar Pembiayaan',
                'deskripsi' => 'Penilaian terhadap pembiayaan pendidikan',
                'bobot_penilaian' => 5.00,
                'urutan' => 7,
                'indicators' => [
                    'Transparansi pengelolaan dana',
                    'Efisiensi penggunaan anggaran',
                    'Akuntabilitas keuangan',
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $indicators = $categoryData['indicators'];
            unset($categoryData['indicators']);

            $category = AssessmentCategory::create($categoryData);

            foreach ($indicators as $index => $indicatorName) {
                AssessmentIndicator::create([
                    'assessment_category_id' => $category->id,
                    'nama_indikator' => $indicatorName,
                    'deskripsi' => 'Indikator penilaian untuk ' . $indicatorName,
                    'bobot_indikator' => round(100 / count($indicators), 2),
                    'kriteria_penilaian' => 'Skala 1-4: 1=Sangat Kurang, 2=Kurang, 3=Baik, 4=Sangat Baik',
                    'skor_maksimal' => 4,
                    'urutan' => $index + 1,
                ]);
            }
        }
    }
}
