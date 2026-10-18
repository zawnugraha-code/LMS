<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pertemuan_id',
        'mahasiswa_id',
        'status',
        'catatan',
        'dicatat_oleh',
    ];

    /**
     * Get the meeting associated with the attendance record.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'pertemuan_id');
    }

    /**
     * Alias for meeting relation.
     */
    public function pertemuan(): BelongsTo
    {
        return $this->meeting();
    }

    /**
     * Get the student associated with the attendance record.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    /**
     * Alias for student relation.
     */
    public function student(): BelongsTo
    {
        return $this->mahasiswa();
    }

    /**
     * Get the lecturer/user who recorded the attendance.
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * Alias for recorder relation.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->pencatat();
    }
}
