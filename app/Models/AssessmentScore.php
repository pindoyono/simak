<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_assessment_id',
        'assessment_indicator_id',
        'skor',
        'bukti_dukung',
        'catatan',
        'file_bukti',
    ];

    protected $casts = [
        'skor' => 'decimal:2',
    ];

    // Accessor for score to use skor field
    public function getScoreAttribute(): float
    {
        return $this->skor ?? 0;
    }

    // Mutator for score to set skor field
    public function setScoreAttribute($value): void
    {
        $this->skor = $value;
    }

    // Accessor for notes to use catatan field
    public function getNotesAttribute(): ?string
    {
        return $this->catatan;
    }

    // Mutator for notes to set catatan field
    public function setNotesAttribute($value): void
    {
        $this->catatan = $value;
    }

    public function schoolAssessment(): BelongsTo
    {
        return $this->belongsTo(SchoolAssessment::class);
    }

    public function assessmentIndicator(): BelongsTo
    {
        return $this->belongsTo(AssessmentIndicator::class);
    }

    // Enhanced score calculation methods
    public function getScorePercentageAttribute(): float
    {
        $indicator = $this->assessmentIndicator;
        $maxScore = $indicator->skor_maksimal ?? 4;
        return $maxScore > 0 ? ($this->skor / $maxScore) * 100 : 0;
    }

    public function getWeightedScoreAttribute(): float
    {
        $indicator = $this->assessmentIndicator;
        $weight = $indicator->bobot_indikator ?? 1;
        return $this->score_percentage * ($weight / 100);
    }

    public function getGradeAttribute(): string
    {
        return match (true) {
            $this->score_percentage >= 85 => 'A',
            $this->score_percentage >= 70 => 'B',
            $this->score_percentage >= 55 => 'C',
            default => 'D',
        };
    }

    public function getGradeColorAttribute(): string
    {
        return match ($this->grade) {
            'A' => 'success',
            'B' => 'info',
            'C' => 'warning',
            'D' => 'danger',
            default => 'gray',
        };
    }

    // Scope methods for filtering
    public function scopeByGrade($query, string $grade)
    {
        return $query->whereRaw('CASE
            WHEN (skor / (SELECT skor_maksimal FROM assessment_indicators WHERE id = assessment_scores.assessment_indicator_id)) * 100 >= 85 THEN "A"
            WHEN (skor / (SELECT skor_maksimal FROM assessment_indicators WHERE id = assessment_scores.assessment_indicator_id)) * 100 >= 70 THEN "B"
            WHEN (skor / (SELECT skor_maksimal FROM assessment_indicators WHERE id = assessment_scores.assessment_indicator_id)) * 100 >= 55 THEN "C"
            ELSE "D"
        END = ?', [$grade]);
    }

    public function scopeHighPerforming($query)
    {
        return $query->whereRaw('(skor / (SELECT skor_maksimal FROM assessment_indicators WHERE id = assessment_scores.assessment_indicator_id)) * 100 >= 70');
    }
}
