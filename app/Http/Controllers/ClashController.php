<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ClashService;

class ClashController extends Controller
{

    public function config(Request $request, string $name, ClashService $service)
    {
        $user_agent = $request->headers->get('User-Agent', '');
        $accept_encoding = $request->headers->get('Accept-Encoding', '');
        \App\Models\AccessLog::create([
            'config' => $name,
            'ipv4'   => $request->headers->get('X-Forwarded-For', $request->ip()),
            'uri'    => $request->getRequestUri(),
            'agent'  => $user_agent,
        ]);
        if (($user_agent == 'Go-http-client/1.1' &&  $accept_encoding == 'gzip') ||
            // ClashForDocker
            preg_match('/ClashForDocker/i', $user_agent) ||
            // ClashForAndroid
            preg_match('/ClashForAndroid/i', $user_agent) ||
            // ClashforWindows
            preg_match('/ClashforWindows/i', $user_agent) ||
            // Shadowrocket
            preg_match('/Shadowrocket/i', $user_agent) ||
            // ClashX
            preg_match('/com\.west2online\.ClashX/i', $user_agent) ||
            // OpenWrt
            (preg_match('/Clash/', $user_agent) && $name == 'openwrt')
        ) {
            return response($service->config($name), 200, [
                'Content-Type' => 'application/x-yaml',
            ]);
        }
        return response('', 200, [
            'Content-Type' => 'application/x-yaml',
        ]);
    }

    public function proxies(Request $request, string $config_name, ClashService $service)
    {
        $user_agent = $request->headers->get('User-Agent', '');
        $accept_encoding = $request->headers->get('Accept-Encoding', '');
        \App\Models\AccessLog::create([
            'config' => $config_name,
            'ipv4'   => $request->headers->get('X-Forwarded-For', $request->ip()),
            'uri'    => $request->getRequestUri(),
            'agent'  => $user_agent,
        ]);
        if (($user_agent == 'Go-http-client/1.1' &&  $accept_encoding == 'gzip') ||
            // Shadowrocket
            preg_match('/CFNetwork/i', $user_agent)
        ) {
            return response(
                $service->proxies(
                    $config_name,
                    $request->input('groups', ''),
                    (bool) $request->input('shuffle', 0),
                    (bool) $request->input('single', 0)
                ),
                200,
                [ 'Content-Type' => 'application/x-yaml', ]
            );
        }
        return response('proxies:', 200, [
            'Content-Type' => 'application/x-yaml',
        ]);
    }
}
