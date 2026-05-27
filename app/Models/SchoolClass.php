<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'grade_level',
        'academic_year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
