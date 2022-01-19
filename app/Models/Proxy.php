<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $dateFormat = 'U';

    protected static function booted()
    {
        static::addGlobalScope(fn ($query) => $query->where('status', 1));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sort',
        'name',
        'type',
        'server',
        'port',
        'status',
        'groups',
        'modify',
        'source',
        'cipher',
        'alterId',
        'uuid',
        'password',
        'psk',
        'extra',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'extra' => 'array',
    ];

    public function groups()
    {
        return $this->belongsToMany('App\Models\Group');
    }
}
