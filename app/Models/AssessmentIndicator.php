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
        // Kolom baru yang ditambahkan
        'kegiatan',
        'sumber_data',
        'keterangan',
        'kriteria_sangat_baik',
        'kriteria_baik',
        'kriteria_cukup',
        'kriteria_kurang',
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

    // Helper methods untuk kriteria baru
    public function getKriteriaByScore(int $score): ?string
    {
        return match($score) {
            4 => $this->kriteria_sangat_baik,
            3 => $this->kriteria_baik,
            2 => $this->kriteria_cukup,
            1 => $this->kriteria_kurang,
            default => null,
        };
    }

    public function getAllKriteria(): array
    {
        return [
            4 => ['label' => 'Sangat Baik', 'kriteria' => $this->kriteria_sangat_baik],
            3 => ['label' => 'Baik', 'kriteria' => $this->kriteria_baik],
            2 => ['label' => 'Cukup', 'kriteria' => $this->kriteria_cukup],
            1 => ['label' => 'Kurang', 'kriteria' => $this->kriteria_kurang],
        ];
    }

    public function getKriteriaLengkapAttribute(): bool
    {
        return !empty($this->kriteria_sangat_baik) &&
               !empty($this->kriteria_baik) &&
               !empty($this->kriteria_cukup) &&
               !empty($this->kriteria_kurang);
    }

    public function getKriteriaCountAttribute(): int
    {
        $count = 0;
        if (!empty($this->kriteria_sangat_baik)) $count++;
        if (!empty($this->kriteria_baik)) $count++;
        if (!empty($this->kriteria_cukup)) $count++;
        if (!empty($this->kriteria_kurang)) $count++;
        return $count;
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
