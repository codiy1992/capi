<?php

namespace App\Services\VariedProxy\Protocols;

use App\Models\Protocol;

class Http
{

    protected $protocol;

    public function __construct(Protocol $protocol = null)
    {
        $this->protocol = $protocol;
    }

    public function format(Protocol $protocol = null)
    {
        $protocol = $protocol ?: $this->protocol;
        return array_merge(
            $protocol->only([
                'name',
                'type',
                'server',
                'port']),
            $protocol->extra);
    }

}
