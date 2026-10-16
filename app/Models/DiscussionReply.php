<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionReply extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'ulid',
        'thread_id',
        'user_id',
        'parent_id',
        'content',
        'is_solution',
        'upvotes_count',
    ];

    protected $casts = [
        'is_solution' => 'boolean',
        'upvotes_count' => 'integer',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'parent_id');
    }
}
