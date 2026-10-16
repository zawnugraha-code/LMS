<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'active_term_id',
        'theme',
        'font_size',
        'locale',
        'timezone',
        'date_format',
        'time_format',
        'notifications',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'google_id',
    ];

    protected $casts = [
        'notifications' => 'array',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activeTerm(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'active_term_id');
    }
}
