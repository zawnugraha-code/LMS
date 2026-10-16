<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionThread extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'ulid',
        'class_id',
        'category_id',
        'user_id',
        'title',
        'content',
        'attachment_path',
        'is_pinned',
        'is_locked',
        'views_count',
        'upvotes_count',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'views_count' => 'integer',
        'upvotes_count' => 'integer',
    ];

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DiscussionCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'thread_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(DiscussionView::class, 'thread_id');
    }
}
