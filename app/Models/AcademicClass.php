<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicClass extends Model
{
    use HasFactory, HasUlid;

    protected $table = 'classes';

    protected $fillable = [
        'course_id',
        'term_id',
        'lecturer_id',
        'section',
        'room',
        'schedule_day',
        'start_time',
        'end_time',
        'capacity',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'settings' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->whereIn('status', ['aktif', 'enrolled']);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'class_id', 'user_id')
            ->withPivot(['status', 'final_score', 'letter_grade', 'dikeluarkan_at', 'dikeluarkan_oleh'])
            ->withTimestamps();
    }

    public function activeStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'class_id', 'user_id')
            ->wherePivotIn('status', ['aktif', 'enrolled'])
            ->withPivot(['status', 'final_score', 'letter_grade', 'dikeluarkan_at', 'dikeluarkan_oleh'])
            ->withTimestamps();
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'class_id')->orderBy('week');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'class_id');
    }

    public function assessmentComponents(): HasMany
    {
        return $this->hasMany(CourseAssessmentComponent::class, 'class_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'class_id');
    }

    public function discussionThreads(): HasMany
    {
        return $this->hasMany(DiscussionThread::class, 'class_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'class_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function isLocked(): bool
    {
        return (bool) ($this->settings['is_locked'] ?? false);
    }

    public function isFinished(): bool
    {
        return $this->status === 'selesai' || ($this->term && $this->term->status === 'selesai');
    }

    public function isFull(): bool
    {
        if ($this->capacity <= 0) {
            return false;
        }

        $count = $this->relationLoaded('activeEnrollments')
            ? $this->activeEnrollments->count()
            : $this->activeEnrollments()->count();

        return $count >= $this->capacity;
    }

    public function remainingCapacity(): int
    {
        $count = $this->relationLoaded('activeEnrollments')
            ? $this->activeEnrollments->count()
            : $this->activeEnrollments()->count();

        return max(0, (int) $this->capacity - $count);
    }
}
