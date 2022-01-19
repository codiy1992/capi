<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class VariedProxy extends Facade
{

    protected static function getFacadeAccessor()
    {
        return \App\Services\VariedProxy\Factory::class;
    }
}
