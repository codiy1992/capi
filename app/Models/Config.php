<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public $timestamps = false;

    protected $casts = [
        'extra' => 'array',
        'exclude' => 'array',
    ];

    protected $attributes = [
        'extra' => '[]',
        'exclude' => '[]',
    ];
}
