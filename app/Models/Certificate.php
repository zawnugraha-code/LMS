<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'ulid',
        'user_id',
        'course_id',
        'certificate_number',
        'title',
        'final_score',
        'letter_grade',
        'issued_date',
        'verification_code',
        'pdf_path',
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'issued_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
