<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUlid
{
    /**
     * Boot the trait to automatically assign a ULID when creating a model.
     */
    public static function bootHasUlid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Resolve route binding accepting either ID (numeric) or ULID string.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->first();
        }

        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }

        return $this->where('ulid', $value)->first();
    }
}
