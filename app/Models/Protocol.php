<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{

    public $timestamps = false;

    protected $casts = [
        'extra' => 'array',
    ];

    protected $attributes = [
        'extra' => '[]',
    ];
}
