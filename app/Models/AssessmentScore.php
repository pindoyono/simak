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
}
