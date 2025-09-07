<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;

class AssessmentReport extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'school_assessment_id',
        'judul_laporan',
        'ringkasan_eksekutif',
        'temuan_utama',
        'rekomendasi',
        'kesimpulan',
        'data_statistik',
        'status_laporan',
        'dibuat_oleh',
        'direview_oleh',
        'disetujui_oleh',
        'tanggal_review',
        'tanggal_approval',
        'tanggal_publikasi',
        'file_lampiran',
        'catatan_reviewer',
        'skor_total',
        'grade_akhir',
        'is_public',
    ];

    protected $casts = [
        'data_statistik' => 'array',
        'file_lampiran' => 'array',
        'tanggal_review' => 'datetime',
        'tanggal_approval' => 'datetime',
        'tanggal_publikasi' => 'datetime',
        'skor_total' => 'decimal:2',
        'is_public' => 'boolean',
    ];

    // Relationships
    public function schoolAssessment(): BelongsTo
    {
        return $this->belongsTo(SchoolAssessment::class);
    }

    public function pembuatLaporan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'direview_oleh');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_laporan) {
            'draft' => 'Draft',
            'review' => 'Sedang Review',
            'final' => 'Final',
            'published' => 'Dipublikasikan',
            default => 'Tidak Diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_laporan) {
            'draft' => 'gray',
            'review' => 'warning',
            'final' => 'info',
            'published' => 'success',
            default => 'gray',
        };
    }

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade_akhir) {
            'Sangat Baik' => 'Sangat Baik (≥85%)',
            'Baik' => 'Baik (70-84%)',
            'Cukup' => 'Cukup (55-69%)',
            'Kurang' => 'Kurang (<55%)',
            // Keep backward compatibility
            'A' => 'Sangat Baik (≥85%)',
            'B' => 'Baik (70-84%)',
            'C' => 'Cukup (55-69%)',
            'D' => 'Kurang (<55%)',
            default => 'Belum Dinilai',
        };
    }

    public function getGradeColorAttribute(): string
    {
        return match ($this->grade_akhir) {
            'Sangat Baik' => 'success',
            'Baik' => 'info',
            'Cukup' => 'warning',
            'Kurang' => 'danger',
            // Keep backward compatibility
            'A' => 'success',
            'B' => 'info',
            'C' => 'warning',
            'D' => 'danger',
            default => 'gray',
        };
    }

    public function getPersentaseAttribute(): float
    {
        $maxScore = 100; // Assuming max score is 100
        return $maxScore > 0 ? round(($this->skor_total / $maxScore) * 100, 2) : 0;
    }

    public function getFileCountAttribute(): int
    {
        return is_array($this->file_lampiran) ? count($this->file_lampiran) : 0;
    }

    public function getHasFilesAttribute(): bool
    {
        return $this->file_count > 0;
    }

    public function getIsCompletedAttribute(): bool
    {
        return !empty($this->judul_laporan) &&
               !empty($this->ringkasan_eksekutif) &&
               !empty($this->kesimpulan);
    }

    public function getCanBePublishedAttribute(): bool
    {
        return $this->status_laporan === 'final' &&
               !empty($this->disetujui_oleh) &&
               $this->is_completed;
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status_laporan', $status);
    }

    public function scopePublished($query)
    {
        return $query->where('status_laporan', 'published')->where('is_public', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('status_laporan', 'draft');
    }

    public function scopeInReview($query)
    {
        return $query->where('status_laporan', 'review');
    }

    public function scopeFinal($query)
    {
        return $query->where('status_laporan', 'final');
    }

    public function scopeByGrade($query, string $grade)
    {
        return $query->where('grade_akhir', $grade);
    }

    public function scopeHighPerforming($query)
    {
        return $query->whereIn('grade_akhir', ['Sangat Baik', 'Baik', 'A', 'B']); // Include both new and old grades
    }

    public function scopeLowPerforming($query)
    {
        return $query->whereIn('grade_akhir', ['Cukup', 'Kurang', 'C', 'D']); // Include both new and old grades
    }

    public function scopeWithFiles($query)
    {
        return $query->whereNotNull('file_lampiran')->where('file_lampiran', '!=', '[]');
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('dibuat_oleh', $userId);
    }

    public function scopeRecentlyPublished($query, $days = 30)
    {
        return $query->where('status_laporan', 'published')
                    ->where('tanggal_publikasi', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function isDraft(): bool
    {
        return $this->status_laporan === 'draft';
    }

    public function isInReview(): bool
    {
        return $this->status_laporan === 'review';
    }

    public function isFinal(): bool
    {
        return $this->status_laporan === 'final';
    }

    public function isPublished(): bool
    {
        return $this->status_laporan === 'published';
    }

    public function canEdit(): bool
    {
        return in_array($this->status_laporan, ['draft', 'review']);
    }

    public function canDelete(): bool
    {
        return $this->status_laporan === 'draft';
    }

    public function markAsReview(): void
    {
        $this->update([
            'status_laporan' => 'review',
            'tanggal_review' => now(),
        ]);
    }

    public function markAsFinal(): void
    {
        $this->update([
            'status_laporan' => 'final',
            'tanggal_approval' => now(),
        ]);
    }

    public function publish(): void
    {
        $this->update([
            'status_laporan' => 'published',
            'tanggal_publikasi' => now(),
            'is_public' => true,
        ]);
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'judul_laporan',
                'status_laporan',
                'skor_total',
                'grade_akhir',
                'is_public'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
