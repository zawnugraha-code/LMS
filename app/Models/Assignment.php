<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assignment extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'meeting_id',
        'class_id',
        'title',
        'description',
        'max_score',
        'due_at',
        'late_policy',
        'allow_resubmit',
        'rubric',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'due_at' => 'datetime',
            'allow_resubmit' => 'boolean',
            'rubric' => 'array',
            'settings' => 'array',
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function userSubmission(int $userId): HasOne
    {
        return $this->hasOne(Submission::class)->where('user_id', $userId);
    }
}
