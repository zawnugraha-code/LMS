<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'content',
        'file_path',
        'score',
        'status',
        'status_waktu',
        'feedback',
        'rubric_scores',
        'submitted_at',
        'graded_at',
        'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'rubric_scores' => 'array',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function getMahasiswaIdAttribute()
    {
        return $this->user_id;
    }

    public function getNilaiAttribute()
    {
        return $this->score;
    }

    public function getCatatanAttribute()
    {
        return $this->content;
    }

    public function getDinilaiAtAttribute()
    {
        return $this->graded_at;
    }

    public function getDinilaiOlehAttribute()
    {
        return $this->graded_by;
    }
}
