<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{

    protected static function booted()
    {
        static::addGlobalScope(fn ($query) => $query->where('status', 1));
    }
}
