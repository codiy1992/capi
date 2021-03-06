<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $dateFormat = 'U';

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
        'types',
        'ports',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'types' => 'array',
        'ports' => 'array',
    ];
}
