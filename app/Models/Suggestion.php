<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $fillable = [
        'sender',
        'sender_name',
        'message',
        'source',
    ];
}
