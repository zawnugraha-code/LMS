<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cpl extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'program_id',
        'code',
        'category',
        'description',
        'target_score',
    ];

    protected function casts(): array
    {
        return [
            'target_score' => 'decimal:2',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function outcomeScores(): HasMany
    {
        return $this->hasMany(OutcomeScore::class);
    }
}
