<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor_identitas',
        'nomor_telepon',
        'institusi',
        'posisi_jabatan',
        'pengalaman_tahun',
        'sertifikasi',
        'bidang_keahlian',
        'catatan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pengalaman_tahun' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
