<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $dateFormat = 'U';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'url',
        'interval',
        'path',
        'health_check',
        'remark',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'id',
        'name',
        'remark',
        'health_check',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'health-check',
    ];

    protected $attributes = [
        'type'         => 'http',
        'url'          => '',
        'interval'     => 3600,
        'path'         => './providers/all.yaml',
        'health_check' => '{"enable": true, "interval": 600, "url": "http://www.gstatic.com/generate_204"}',
    ];

    protected $casts = [
        'health_check' => 'array',
    ];

    public function getHealthCheckAttribute()
    {
        return json_decode($this->attributes['health_check'], true);
    }

    public function proxies()
    {
        return $this->belongsToMany('App\Models\Proxy');
    }


}
