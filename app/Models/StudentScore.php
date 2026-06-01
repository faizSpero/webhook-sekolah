<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentScore extends Model
{
    protected $fillable = [
        'student_id',
        'subject',
        'score',
        'score_date',
        'semester',
        'tahun_akademik',
        'notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'score_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
