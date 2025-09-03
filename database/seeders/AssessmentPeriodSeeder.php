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
            'name' => 'Assessment Period 2024/2025 Semester 1',
            'academic_year' => '2024/2025',
            'semester' => 'Ganjil',
            'start_date' => '2024-08-01',
            'end_date' => '2024-12-31',
            'status' => 'active',
            'description' => 'Assessment period for first semester of academic year 2024/2025',
        ]);

        AssessmentPeriod::create([
            'name' => 'Assessment Period 2024/2025 Semester 2',
            'academic_year' => '2024/2025',
            'semester' => 'Genap',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
            'status' => 'draft',
            'description' => 'Assessment period for second semester of academic year 2024/2025',
        ]);

        AssessmentPeriod::create([
            'name' => 'Assessment Period 2023/2024 Annual',
            'academic_year' => '2023/2024',
            'semester' => 'Tahunan',
            'start_date' => '2023-08-01',
            'end_date' => '2024-07-31',
            'status' => 'completed',
            'description' => 'Annual assessment period for academic year 2023/2024',
        ]);
    }
}
