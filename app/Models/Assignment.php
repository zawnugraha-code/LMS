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
        'dosen_id',
        'jenis',
        'mode',
        'metode',
        'title',
        'description',
        'lampiran_path',
        'max_score',
        'mulai_at',
        'due_at',
        'publish_at',
        'status',
        'late_policy',
        'allow_resubmit',
        'rubric',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'mulai_at' => 'datetime',
            'due_at' => 'datetime',
            'publish_at' => 'datetime',
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

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function userSubmission(int $userId): HasOne
    {
        return $this->hasOne(Submission::class)->where('user_id', $userId);
    }

    public function getDeadlineAtAttribute()
    {
        return $this->due_at;
    }

    public function getNilaiMaksAttribute()
    {
        return $this->max_score;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'dipublikasikan')
            ->where(function ($q) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            });
    }

    public function scopeForDosen($query, int $dosenId)
    {
        return $query->where(function ($q) use ($dosenId) {
            $q->where('dosen_id', $dosenId)
                ->orWhereHas('academicClass', fn ($cq) => $cq->where('lecturer_id', $dosenId));
        });
    }
}
