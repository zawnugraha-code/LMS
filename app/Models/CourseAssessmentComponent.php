<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAssessmentComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'jenis',
        'bobot',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'integer',
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }
}
