<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCampus;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use BelongsToCampus, HasFactory, HasUlid;

    protected $fillable = [
        'campus_id',
        'name',
        'code',
        'description',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
