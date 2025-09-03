<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_periode',
        'tahun_ajaran',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function schoolAssessments(): HasMany
    {
        return $this->hasMany(SchoolAssessment::class);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'aktif';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }
}
