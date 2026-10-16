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

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'class_id', 'user_id')
            ->withPivot(['status', 'final_score', 'letter_grade'])
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

    public function isLocked(): bool
    {
        return (bool) ($this->settings['is_locked'] ?? false);
    }
}
