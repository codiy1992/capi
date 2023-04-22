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
        return response($service->config($name), 200, [
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
            // Clash.Meta
            preg_match('/clash.meta/i', $user_agent) ||
            // Shadowrocket
            preg_match('/CFNetwork/i', $user_agent)
        ) {
            return response(
                $service->proxies(
                    $config_name,
                    $request->input('groups', ''),
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

    public function updateServer(Request $request, ClashService $service)
    {
        $inputs = $this->validate($request, [
            'ipv4_old' => 'required|ipv4',
            'ipv4_new' => 'required|ipv4',
        ]);
        return response($service->updateServer($inputs));
    }

    public function updateAnyCastIPV4(Request $request, ClashService $service)
    {
        $inputs = $this->validate($request, [
            'provider' => 'required|in:cloudfront,cloudflare',
            'ipv4'     => 'required|ipv4',
            'icmp'     => 'nullable|string',
            'down'     => 'nullable|string',
        ]);
        return response($service->updateAnyCastIPV4($inputs['provider'], $inputs['ipv4'], $inputs['down'], $inputs['icmp']));
    }
}
