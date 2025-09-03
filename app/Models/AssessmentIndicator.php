<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_category_id',
        'nama_indikator',
        'deskripsi',
        'bobot_indikator',
        'kriteria_penilaian',
        'skor_maksimal',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bobot_indikator' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(AssessmentCategory::class, 'assessment_category_id');
    }
}
