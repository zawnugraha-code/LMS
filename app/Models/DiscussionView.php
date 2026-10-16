<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionView extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'user_id',
        'viewed_date',
    ];

    protected $casts = [
        'viewed_date' => 'date',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
