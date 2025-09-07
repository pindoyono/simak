<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SchoolAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'assessment_period_id',
        'assessor_id',
        'tanggal_asesmen',
        'status',
        'total_score',
        'grade',
        'catatan',
        'submitted_at',
        'reviewed_at',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_asesmen' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'total_score' => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class, 'assessment_period_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assessor_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(AssessmentFile::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AssessmentReview::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(AssessmentReview::class)->latest();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'info',
            'under_review' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    public function getGradeWithDescriptionAttribute(): string
    {
        return match($this->grade) {
            'Sangat Baik' => 'Sangat Baik',
            'Baik' => 'Baik',
            'Cukup' => 'Cukup',
            'Kurang' => 'Kurang',
            // Keep backward compatibility
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'D' => 'Kurang',
            'F' => 'Kurang',
            default => $this->grade ?? 'N/A',
        };
    }
}
