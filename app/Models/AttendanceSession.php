<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'meeting_id',
        'qr_secret',
        'qr_expires_at',
        'geofence',
        'opened_at',
        'closed_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'qr_expires_at' => 'datetime',
            'geofence' => 'array',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
