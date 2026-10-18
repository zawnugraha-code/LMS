<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'user_id',
        'status',
        'final_score',
        'letter_grade',
        'dikeluarkan_at',
        'dikeluarkan_oleh',
    ];

    protected function casts(): array
    {
        return [
            'final_score' => 'decimal:2',
            'dikeluarkan_at' => 'datetime',
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->student();
    }

    public function dikeluarkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikeluarkan_oleh');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['aktif', 'enrolled']);
    }

    public function scopeDikeluarkan($query)
    {
        return $query->where('status', 'keluar');
    }
}
