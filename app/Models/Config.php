<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'dns',
        'groups',
        'interval',
        'shuffle',
        'single',
        'exclude',
    ];

    protected $casts = [
        'exclude' => 'array',
    ];

    protected $attributes = [
        'exclude' => '[]',
    ];
}
