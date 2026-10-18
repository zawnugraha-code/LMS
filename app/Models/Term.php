<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCampus;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    use BelongsToCampus, HasFactory, HasUlid;

    protected $fillable = [
        'campus_id',
        'name',
        'academic_year',
        'semester_type',
        'start_date',
        'end_date',
        'is_active',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(AcademicClass::class);
    }

    public function isCurrentActive(): bool
    {
        return $this->status === 'aktif' || ($this->is_active && $this->status !== 'selesai');
    }

    public function isFinished(): bool
    {
        return $this->status === 'selesai';
    }

    public function isDraft(): bool
    {
        return $this->status === 'belum_aktif' || empty($this->status);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif')
            ->orWhere(function ($q) {
                $q->where('is_active', true)
                    ->where(function ($sub) {
                        $sub->whereNull('status')->orWhere('status', '!=', 'selesai');
                    });
            });
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeBelumAktif($query)
    {
        return $query->where('status', 'belum_aktif');
    }
}
