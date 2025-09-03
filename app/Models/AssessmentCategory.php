<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function indicators(): HasMany
    {
        return $this->hasMany(AssessmentIndicator::class, 'assessment_category_id');
    }

    // Accessor for name to use nama_kategori
    public function getNameAttribute(): string
    {
        return $this->nama_kategori;
    }

    // Accessor for description to use deskripsi
    public function getDescriptionAttribute(): string
    {
        return $this->deskripsi ?? '';
    }
}
