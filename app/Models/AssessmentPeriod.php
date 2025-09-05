<?php

namespace App\Models;

use Carbon\Carbon;
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
        'is_default',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Pastikan hanya ada satu periode default
        static::saving(function ($period) {
            if ($period->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $period->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function schoolAssessments(): HasMany
    {
        return $this->hasMany(SchoolAssessment::class);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'aktif';
    }

    public function getDurasiAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function getStatusWaktuAttribute(): string
    {
        $today = Carbon::today();

        if ($today->lt($this->tanggal_mulai)) {
            return 'akan_datang';
        } elseif ($today->between($this->tanggal_mulai, $this->tanggal_selesai)) {
            return 'berlangsung';
        } else {
            return 'berakhir';
        }
    }

    public function getStatusWaktuLabelAttribute(): string
    {
        return match ($this->status_waktu) {
            'akan_datang' => 'Akan Datang',
            'berlangsung' => 'Sedang Berlangsung',
            'berakhir' => 'Sudah Berakhir',
            default => 'Tidak Diketahui',
        };
    }

    public function getHariTersisaAttribute(): int
    {
        $today = Carbon::today();

        if ($today->lt($this->tanggal_mulai)) {
            return $today->diffInDays($this->tanggal_mulai);
        } elseif ($today->lte($this->tanggal_selesai)) {
            return $today->diffInDays($this->tanggal_selesai);
        }

        return 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('tanggal_mulai', '<=', $today)
                    ->where('tanggal_selesai', '>=', $today);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_mulai', '>', Carbon::today());
    }

    public function scopePast($query)
    {
        return $query->where('tanggal_selesai', '<', Carbon::today());
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByTahunAjaran($query, $tahunAjaran)
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    // Static methods
    public static function getDefaultPeriod()
    {
        return static::where('is_default', true)->first();
    }

    public static function getCurrentPeriod()
    {
        return static::current()->first();
    }
}
