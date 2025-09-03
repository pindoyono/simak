<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'komponen',
        'nama_kategori',
        'deskripsi',
        'bobot_penilaian',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bobot_penilaian' => 'decimal:2',
    ];
}
