<?php

namespace App\Services\VariedProxy;

use App\Error\Code;
use BadMethodCallException;
use App\Exceptions\BusinessException;
use App\Models\Protocol;

class Factory
{
    public static $instance;

    protected static $protocols= [
        'ss'     => Protocols\Shadowsocks::class,
        'ssr'    => Protocols\ShadowsocksR::class,
        'trojan' => Protocols\Trojan::class,
        'http'   => Protocols\Http::class,
        'socks5' => Protocols\Socks5::class,
        'snell'  => Protocols\Snell::class,
        'vmess'  => Protocols\Vmess::class,
        'vless'  => Protocols\Vless::class,
    ];

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        } else {
            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ));
        }
    }

    public static function __callStatic($method, $arguments)
    {
        return (static::getInstance())->$method(...$arguments);
    }

    protected function make($protocol)
    {
        if (!in_array($protocol, array_keys(static::$protocols))) {
            throw new BusinessException(Code::PROXY_PROTOCOL_NO_FOUND);
        } else {
            return new static::$protocols[$protocol]();
        }
    }

    protected function format(Protocol $protocol)
    {
        return (new static::$protocols[$protocol->name]($protocol))->format();
    }
}
