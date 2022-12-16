<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{

    protected $casts = [
        'extra' => 'array',
    ];

    protected $attributes = [
        'extra' => '[]',
    ];
}
