<?php

namespace App\Services;

use App\Models\Protocol;
use App\Models\Server;
use App\Models\Config;
use App\Facades\VariedProxy;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Cache;

class ClashService
{

    public function config(string $name)
    {
        if (!$config = Config::where(['name' => $name])->first()) {
            return ;
        }

        // Options
        $dns_enable = request()->input('dns', $config->dns);
        $dns_enable = (strtolower($dns_enable) == 'false' || empty($dns_enable)) ? false : true;

        // Groups
        $groups = request()->input('groups', $config->groups);
        $groups = explode(',', $groups);
        $valid_groups = explode(',', $config->groups);
        array_walk($valid_groups, function(&$value) { $value = trim(strtoupper($value)); });
        array_walk($groups, function(&$value) { $value = trim(strtoupper($value)); });
        $groups = array_intersect($groups, $valid_groups);
        empty($groups) && $groups = $valid_groups;
        $online_groups = array_unique(Server::whereIn('group', $groups)->pluck('group')->toArray());
        $groups = array_intersect($groups, $online_groups);

        $interval = (int)request()->input('interval', $config->interval);
        $single = (int)request()->input('single', $config->single);

        $providers = $this->proxyProviders($config, $groups, $interval, $single);
        $groups = $this->proxyGroups($config, $groups);

        // Load Yaml Files
        $config = Yaml::parseFile(resource_path('clash/config.yaml'));
        $dns = Yaml::parseFile(resource_path('clash/dns.yaml'));
        $rules = Yaml::parseFile(resource_path('clash/rules.yaml'));
        $dns['dns']['enable'] = (bool) $dns_enable;
        return Yaml::dump(array_merge(
            $config, $dns, $providers, $groups, $rules
        ), 2, 2);
    }

    /**
     *
     */
    public function proxyProviders(Config $config, array $groups, $interval = 3600, $single = 0)
    {
        $providers = [];
        $server_host = request()->getSchemeAndHttpHost();
        foreach($groups as $group) {
            $group = strtolower($group);
            $providers["provider_{$group}"] = [
                'url'      => sprintf(
                        "{$server_host}/proxies/%s?groups=%s&single=%s",
                        $config->name, $group, $single
                    ),
                'path'     => "./providers/{$group}.yaml",
                'interval' => $interval,
                'type'     => 'http',
                'health-check' => [
                    'url'      => 'http://www.gstatic.com/generate_204',
                    'enable'   => true,
                    'interval' => 600
                ],
            ];
        }
        $providers['provider_all'] = [
            'url' => sprintf(
                    "{$server_host}/proxies/%s?groups=%s&single=%s",
                    $config->name, implode(',', $groups), $single
            ),
            'interval' => $interval,
            'path'     => './providers/all.yaml',
            'type'     => 'http',
            'health-check' => [
                'url'      => 'http://www.gstatic.com/generate_204',
                'enable'   => true,
                'interval' => 600
            ],
        ];
        return ['proxy-providers' => $providers];
    }

    public function proxyGroups(Config $config, array $groups)
    {
        $proxy_groups = [];
        $server_host = request()->getSchemeAndHttpHost();

        $group_select = [
            'name'    => 'Proxy',
            'type'    => 'select',
            'use'     => ['provider_all'],
            'proxies' => ['fallback-auto', 'DIRECT'],
        ];
        $group_fallback = [
            'name'     => 'fallback-auto',
            'type'     => 'fallback',
            'url'      => 'http://www.gstatic.com/generate_204',
            'interval' => 300,
            'proxies'  => [],
        ];

        foreach($groups as $name) {
            $auto_name = sprintf("auto-%s", strtoupper($name));
            $name = strtolower($name);
            $group_auto = [
                'name' => $auto_name,
                'type' => 'url-test',
                'use' => ["provider_{$name}"],
                'url'      => 'http://www.gstatic.com/generate_204',
                'interval' => 300,
            ];
            $group_select['proxies'][] = $auto_name;
            $group_fallback['proxies'][] = $auto_name;
            $proxy_groups[] = $group_auto;
        }

        array_unshift($proxy_groups, $group_fallback);
        array_unshift($proxy_groups, $group_select);

        return [ 'proxy-groups' => $proxy_groups];
    }

    /**
     * 代理节点
     */
    public function proxies(string $config_name, string $group_names = '', bool $single = false)
    {
        if (!$config = Config::where(['name' => $config_name])->first()) {
            return ;
        }
        empty($group_names) && $group_names = $config->groups;
        $groups = array_intersect(explode(',', $group_names), explode(',', $config->groups));
        $servers = Server::whereIn('group', $groups)
                ->when(
                    !empty($config->exclude['servers']),
                    fn($q) => $q->whereNotIn('name', $config->exclude['servers'])
                )->get();
        $protocols = Protocol::when(
                    !empty($config->exclude['protocols']),
                    fn($q) => $q->whereNotIn('name', $config->exclude['protocols'])
                )->when(
                    !empty($config->exclude['transports']),
                    fn($q) => $q->whereNotIn('transport', $config->exclude['transports'])
                )->get();
        $proxies = [];
        foreach($servers as $server) {
            foreach($protocols as $protocol) {
                if (!$protocol->status && !($config->debug && $server->debug)) {
                    continue;
                }
                $proxy = VariedProxy::format($protocol);
                $proxy['name'] = sprintf("%s.%s.%s.%s", $server->group, $server->name, $protocol->name, $protocol->transport);
                $proxy['type'] = $protocol->name;
                $proxy['server'] = $protocol->tls ? sprintf('%s.0x256.com', $server->ipv4) : $server->ipv4;
                if (!empty($proxy['plugin']) && in_array($proxy['plugin'], ['v2ray-plugin'])) {
                    $proxy['plugin-opts']['host'] = $proxy['server'];
                }
                !empty($proxy['ws-opts']) && $proxy['ws-opts']['headers']['host'] = $proxy['server'];
                $protocol->name == 'trojan' && $proxy['sni'] = $proxy['server'];
                $protocol->name == 'vmess' && $proxy['servername'] = $proxy['server'];
                $proxies[] = $proxy;
            }
            $single && $proxies = [array_shift($proxies)];
        }
        return Yaml::dump(['proxies' => $this->cdnWrap($proxies, $config),]);
    }

    /**
     * 套CDN
     */
    public function cdnWrap($proxies, Config $config)
    {
        $result = [];
        foreach ($proxies as $proxy) {
            if ($proxy['port'] != 443) { continue; }
            $result[] = $proxy;
            $array = explode('.', $proxy['name']);
            $transport = array_pop($array);
            $protocol = array_pop($array);
            // cloudfront
            if (in_array($transport, ['websocket'])) {
                $cloudfront = $proxy;
                $cloudfront['name'] = "{$cloudfront['name']}_cloudfront";
                $cloudfront['server'] = str_replace('.0x256.com', '', $cloudfront['server']);
                $cloudfront['server'] = str_replace('.', '', $cloudfront['server']) . '.0x256.com';
                $cloudfront['server'] = "cft{$cloudfront['server']}";

                !empty($cloudfront['plugin-opts']['host']) && $cloudfront['plugin-opts']['host'] = $cloudfront['server'];
                !empty($cloudfront['ws-opts']) && $cloudfront['ws-opts']['headers']['host'] = $cloudfront['server'];
                !empty($cloudfront['sni']) && $cloudfront['sni'] = $cloudfront['server'];
                !empty($cloudfront['servername']) && $cloudfront['servername'] = $cloudfront['server'];
                if (!empty($config->extra['anycast'])) {
                    $cloudfront['server'] = Cache::get('cloudfront:anycast:ipv4', $cloudfront['server']);
                }
                $result[] = $cloudfront;
            }
            // cloudflare
            $cloudflare = $proxy;
            $cloudflare['name'] = "{$cloudflare['name']}_cloudflare";
            $cloudflare['server'] = str_replace('.0x256.com', '', $cloudflare['server']);
            $cloudflare['server'] = str_replace('.', '', $cloudflare['server']) . '.0x256.com';

            !empty($cloudflare['plugin-opts']['host']) && $cloudflare['plugin-opts']['host'] = $cloudflare['server'];
            !empty($cloudflare['ws-opts']) && $cloudflare['ws-opts']['headers']['host'] = $cloudflare['server'];
            !empty($cloudflare['sni']) && $cloudflare['sni'] = $cloudflare['server'];
            !empty($cloudflare['servername']) && $cloudflare['servername'] = $cloudflare['server'];
            if (!empty($config->extra['anycast'])) {
                $cloudflare['server'] = Cache::get('cloudflare:anycast:ipv4', $cloudflare['server']);
            }
            $result[] = $cloudflare;

            // cloudflare worker
            $worker = $proxy;
            if (!empty($config->extra['worker'])) {
                // worker
                $worker['name'] = "{$worker['name']}_worker";
                $worker['server'] = str_replace('.0x256.com', '', $worker['server']);
                $worker['server'] = str_replace('.', '', $worker['server']) . '.0x256.com';
                $worker['server'] = "w{$worker['server']}";

                !empty($worker['plugin-opts']['host']) && $worker['plugin-opts']['host'] = $worker['server'];
                !empty($worker['ws-opts']) && $worker['ws-opts']['headers']['host'] = $worker['server'];
                !empty($worker['sni']) && $worker['sni'] = $worker['server'];
                !empty($worker['servername']) && $worker['servername'] = $worker['server'];
                if (!empty($config->extra['anycast'])) {
                    $worker['server'] = Cache::get('cloudflare:anycast:ipv4', $worker['server']);
                }
                $result[] = $worker;
            }
        }
        return $result;
    }

    public function updateServer(array $inputs)
    {
        if ($server = Server::where(['ipv4' => $inputs['ipv4_old']])->first()) {
            $server->ipv4 = $inputs['ipv4_new'];
            $server->save();
            return $server;
        }
        return [];
    }

    public function updateAnyCastIPV4(string $provider, string $ipv4)
    {
        $key = "{$provider}:anycast:ipv4";
        if (Cache::get($key) != $ipv4) {
            Cache::put($key, $ipv4, 86400);
        }
        return $ipv4;
    }
}
