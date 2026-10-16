<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialProgress extends Model
{
    use HasFactory;

    protected $table = 'material_progress';

    protected $fillable = [
        'user_id',
        'material_id',
        'status',
        'watch_percentage',
        'completed_at',
    ];

    protected $casts = [
        'watch_percentage' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
