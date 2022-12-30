<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnycastLog extends Model
{
    protected $dateFormat = 'U';

    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'ipv4',
        'down',
        'icmp',
    ];
}
