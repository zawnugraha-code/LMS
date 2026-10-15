<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'xp_required',
        'criteria',
    ];

    protected function casts(): array
    {
        return [
            'xp_required' => 'integer',
            'criteria' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'badge_user')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
