<?php

namespace App\Services\VariedProxy\Protocols;

use App\Models\Protocol;

class Snell
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
                'port',
                'psk']),
            $protocol->extra);
    }

}
