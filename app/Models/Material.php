<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'meeting_id',
        'title',
        'type',
        'content',
        'file_path',
        'external_url',
        'is_downloadable',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_downloadable' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(MaterialProgress::class, 'material_id');
    }
}
