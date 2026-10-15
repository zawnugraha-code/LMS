<?php

namespace App\Models;

use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'name',
        'email',
        'institution',
        'phone',
        'role',
        'students_count',
        'message',
        'status',
    ];
}
