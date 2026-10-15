<?php

namespace App\Models\Concerns;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToCampus
{
    /**
     * Boot the trait to add campus scoping and auto-fill campus_id.
     */
    public static function bootBelongsToCampus(): void
    {
        // Auto-assign campus_id from authenticated user if not provided
        static::creating(function (Model $model) {
            if (empty($model->campus_id) && Auth::check() && Auth::user()->campus_id) {
                $model->campus_id = Auth::user()->campus_id;
            }
        });

        // Add global scope for campus tenant isolation
        static::addGlobalScope('campus', function (Builder $builder) {
            if (session()->has('active_campus_id')) {
                $builder->where($builder->getModel()->getTable().'.campus_id', session('active_campus_id'));
            } elseif (Auth::check() && Auth::user()->campus_id && ! Auth::user()->isSuperAdmin()) {
                $builder->where($builder->getModel()->getTable().'.campus_id', Auth::user()->campus_id);
            }
        });
    }

    /**
     * Get the campus that owns the model.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Scope a query to only include records of a given campus.
     */
    public function scopeForCampus(Builder $query, int $campusId): Builder
    {
        return $query->withoutGlobalScope('campus')->where($this->getTable().'.campus_id', $campusId);
    }
}
