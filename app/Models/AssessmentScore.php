<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AssessmentScore extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'school_assessment_id',
        'assessment_indicator_id',
        'skor',
        'bukti_dukung',
        'catatan',
        'file_bukti',
        'tanggal_penilaian',
    ];

    protected $casts = [
        'skor' => 'decimal:2',
        'file_bukti' => 'array',
        'tanggal_penilaian' => 'datetime',
    ];

    // Relationships
    public function schoolAssessment(): BelongsTo
    {
        return $this->belongsTo(SchoolAssessment::class);
    }

    public function assessmentIndicator(): BelongsTo
    {
        return $this->belongsTo(AssessmentIndicator::class);
    }

    // Accessors
    public function getScoreAttribute(): float
    {
        return $this->skor ?? 0;
    }

    public function getNotesAttribute(): ?string
    {
        return $this->catatan;
    }

    public function getPersentaseAttribute(): float
    {
        $indicator = $this->assessmentIndicator;
        $maxScore = $indicator?->skor_maksimal ?? 4;
        return $maxScore > 0 ? round(($this->skor / $maxScore) * 100, 2) : 0;
    }

    public function getSkorBerbobotAttribute(): float
    {
        $indicator = $this->assessmentIndicator;
        if (!$indicator) {
            return 0;
        }

        // Load category if not already loaded
        if (!$indicator->relationLoaded('category')) {
            $indicator->load('category');
        }

        if (!$indicator->category) {
            return 0;
        }

        // Use category weight instead of indicator weight
        $bobotKategori = $indicator->category->bobot_penilaian ?? 0;
        return round(($this->persentase * $bobotKategori) / 100, 2);
    }

    public function getGradeAttribute(): string
    {
        return match (true) {
            $this->persentase >= 85 => 'Sangat Baik',
            $this->persentase >= 70 => 'Baik',
            $this->persentase >= 55 => 'Cukup',
            default => 'Kurang',
        };
    }

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade) {
            'Sangat Baik' => 'Sangat Baik (≥85%)',
            'Baik' => 'Baik (70-84%)',
            'Cukup' => 'Cukup (55-69%)',
            'Kurang' => 'Kurang (<55%)',
            default => 'Tidak Dinilai',
        };
    }

    public function getGradeColorAttribute(): string
    {
        return match ($this->grade) {
            'Sangat Baik' => 'success',
            'Baik' => 'info',
            'Cukup' => 'warning',
            'Kurang' => 'danger',
            default => 'gray',
        };
    }

    public function getFileCountAttribute(): int
    {
        return is_array($this->file_bukti) ? count($this->file_bukti) : 0;
    }

    public function getHasFilesAttribute(): bool
    {
        return $this->file_count > 0;
    }

    // Mutators
    public function setScoreAttribute($value): void
    {
        $this->skor = $value;
    }

    public function setNotesAttribute($value): void
    {
        $this->catatan = $value;
    }

    // Scopes
    public function scopeByGrade($query, string $grade)
    {
        return $query->whereHas('assessmentIndicator', function ($subQuery) use ($grade) {
            $condition = match ($grade) {
                'Sangat Baik' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 >= 85',
                'Baik' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 >= 70 AND (assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 < 85',
                'Cukup' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 >= 55 AND (assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 < 70',
                'Kurang' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 < 55',
                // Keep backward compatibility for old letter grades
                'A' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 >= 85',
                'B' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 >= 70 AND (assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 < 85',
                'C' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 >= 55 AND (assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 < 70',
                'D' => '(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 < 55',
                default => '1=1',
            };
            $subQuery->whereRaw($condition);
        });
    }

    public function scopeHighPerforming($query)
    {
        return $query->whereHas('assessmentIndicator', function ($subQuery) {
            $subQuery->whereRaw('(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 >= 85');
        });
    }

    public function scopeLowPerforming($query)
    {
        return $query->whereHas('assessmentIndicator', function ($subQuery) {
            $subQuery->whereRaw('(assessment_scores.skor / assessment_indicators.skor_maksimal) * 100 < 55');
        });
    }

    public function scopeWithFiles($query)
    {
        return $query->whereNotNull('file_bukti')->where('file_bukti', '!=', '[]');
    }

    public function scopeWithEvidence($query)
    {
        return $query->whereNotNull('bukti_dukung')->where('bukti_dukung', '!=', '');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['skor', 'bukti_dukung', 'catatan'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Helper Methods
    public function isExcellent(): bool
    {
        return $this->grade === 'Sangat Baik';
    }

    public function isGood(): bool
    {
        return in_array($this->grade, ['Sangat Baik', 'Baik']);
    }

    public function needsImprovement(): bool
    {
        return in_array($this->grade, ['Cukup', 'Kurang']);
    }
}
