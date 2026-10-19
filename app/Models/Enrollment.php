<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_DIKELUARKAN = 'dikeluarkan';
    public const STATUS_KELUAR = 'keluar';

    /**
     * Statuses that grant full active academic access (submit assignments, check-in, active counting).
     */
    public const ACTIVE_STATUSES = [
        self::STATUS_AKTIF,
        self::STATUS_ENROLLED,
    ];

    /**
     * Statuses that grant view/read access (including completed courses).
     */
    public const ACCESSIBLE_STATUSES = [
        self::STATUS_AKTIF,
        self::STATUS_ENROLLED,
        self::STATUS_COMPLETED,
    ];

    /**
     * Statuses indicating student removal / inactive enrollment.
     */
    public const REMOVED_STATUSES = [
        self::STATUS_DIKELUARKAN,
        self::STATUS_KELUAR,
    ];

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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dikeluarkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikeluarkan_oleh');
    }

    /**
     * Scope a query to only include active enrollments with full operational privileges.
     */
    public function scopeAktif($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    /**
     * Scope a query to include accessible enrollments (active + completed read-only).
     */
    public function scopeAccessible($query)
    {
        return $query->whereIn('status', self::ACCESSIBLE_STATUSES);
    }

    /**
     * Scope a query to only include completed enrollments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include removed / expelled students.
     */
    public function scopeDikeluarkan($query)
    {
        return $query->whereIn('status', self::REMOVED_STATUSES);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isAccessible(): bool
    {
        return in_array($this->status, self::ACCESSIBLE_STATUSES, true);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDikeluarkan(): bool
    {
        return in_array($this->status, self::REMOVED_STATUSES, true);
    }
}
