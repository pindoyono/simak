<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_assessment_id',
        'reviewer_id',
        'review_date',
        'status',
        'grade_recommendation',
        'strengths',
        'weaknesses',
        'recommendations',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'review_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function schoolAssessment(): BelongsTo
    {
        return $this->belongsTo(SchoolAssessment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Review',
            'in_progress' => 'Sedang Direview',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revision_needed' => 'Perlu Revisi',
            default => 'Unknown'
        };
    }

    public function getGradeLabelAttribute(): string
    {
        return match($this->grade_recommendation) {
            'A' => 'A - Sangat Baik (≥85%)',
            'B' => 'B - Baik (70-84%)',
            'C' => 'C - Cukup (55-69%)',
            'D' => 'D - Kurang (<55%)',
            default => 'Belum Ditentukan'
        };
    }
}
