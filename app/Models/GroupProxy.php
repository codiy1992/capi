<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupProxy extends Model
{

    protected $table = 'group_proxy';

    const CREATED_AT = null;

    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'proxy_id',
    ];
}
