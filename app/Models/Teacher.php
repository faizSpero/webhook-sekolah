<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'external_id',
        'nip',
        'name',
        'email',
        'phone',
        'subject',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
