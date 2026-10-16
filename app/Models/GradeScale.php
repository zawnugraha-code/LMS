<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'letter',
        'min_score',
        'max_score',
        'gpa_point',
        'is_passing',
        'legend_group',
        'description',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'gpa_point' => 'decimal:2',
        'is_passing' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
