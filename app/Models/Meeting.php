<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Meeting extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'class_id',
        'week',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'mode',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'week' => 'integer',
            'date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function class(): BelongsTo
    {
        return $this->academicClass();
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function attendanceSession(): HasOne
    {
        return $this->hasOne(AttendanceSession::class);
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'pertemuan_id');
    }
}
