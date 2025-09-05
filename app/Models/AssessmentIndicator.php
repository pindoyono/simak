<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'skor_maksimal' => 'integer',
        'urutan' => 'integer',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'assessment_category_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class, 'assessment_indicator_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('assessment_category_id', $categoryId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public function scopeByKomponen($query, $komponen)
    {
        return $query->whereHas('category', function ($q) use ($komponen) {
            $q->where('komponen', $komponen);
        });
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    public function getScoreCountAttribute(): int
    {
        return $this->scores()->count();
    }

    public function getAverageScoreAttribute(): float
    {
        return round($this->scores()->avg('skor') ?? 0, 2);
    }

    public function getWeightPercentageAttribute(): string
    {
        return number_format($this->bobot_indikator, 2) . '%';
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->category->komponen} - {$this->category->nama_kategori} - {$this->nama_indikator}";
    }

    // Legacy support for old field names
    public function getNameAttribute(): string
    {
        return $this->nama_indikator;
    }

    public function getDescriptionAttribute(): string
    {
        return $this->deskripsi ?? '';
    }
}
