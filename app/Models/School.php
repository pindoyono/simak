<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'alamat',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'jenjang',
        'status',
        'kepala_sekolah',
        'telepon',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assessments()
    {
        return $this->hasMany(SchoolAssessment::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->nama_sekolah . ' (' . $this->npsn . ')';
    }
}
