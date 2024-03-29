<?php

namespace App\Services;

use App\Models\Protocol;
use App\Models\Server;
use App\Models\Config;
use App\Models\AnycastLog;
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

        // Options
        $dns_enable = request()->input('dns', $config->dns);
        $dns_enable = (strtolower($dns_enable) == 'false' || empty($dns_enable)) ? false : true;
        $interval = (int)request()->input('interval', $config->interval);

        $providers = $this->proxyProviders($config, $groups, $interval);
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
    public function proxyProviders(Config $config, array $groups, $interval = 3600)
    {
        $providers = [];
        $meta_core = request()->input('meta', false);
        $server_host = request()->getSchemeAndHttpHost();
        foreach($groups as $group) {
            $group = strtolower($group);
            $url = sprintf("{$server_host}/proxies/%s?groups=%s", $config->name, $group);
            !empty($meta_core) && $url = "{$url}&meta=true";
            $providers["provider_{$group}"] = [
                'url'      => $url,
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
        return ['proxy-providers' => $providers];
    }

    /**
     *
     */
    public function proxyGroups(Config $config, array $groups)
    {
        $proxy_groups = [];
        $server_host = request()->getSchemeAndHttpHost();

        $group_select = [
            'name'    => 'Proxy',
            'type'    => 'select',
            'use'     => [],
            'proxies' => ['load-balance(hashing)', 'load-balance(round-robin)', 'fallback-auto', 'DIRECT'],
        ];

        $group_lb_hashing = [
            'name' => 'load-balance(hashing)',
            'type' => 'load-balance',
            'use' => [],
            'url'      => 'http://www.gstatic.com/generate_204',
            'interval' => 300,
            'strategy' => 'consistent-hashing',
        ];
        $group_lb_round_robin= [
            'name' => 'load-balance(round-robin)',
            'type' => 'load-balance',
            'use' => [],
            'url'      => 'http://www.gstatic.com/generate_204',
            'interval' => 300,
            'strategy' => 'round-robin',
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
            $group_lb_hashing['use'][]     = "provider_{$name}";
            $group_lb_round_robin['use'][] = "provider_{$name}";
            $group_select['use'][]         = "provider_{$name}";
            $group_select['proxies'][]     = $auto_name;
            $group_fallback['proxies'][]   = $auto_name;
            $proxy_groups[]                = $group_auto;
        }

        array_unshift($proxy_groups, $group_fallback);
        array_unshift($proxy_groups, $group_lb_round_robin);
        array_unshift($proxy_groups, $group_lb_hashing);
        array_unshift($proxy_groups, $group_select);

        return [ 'proxy-groups' => $proxy_groups];
    }

    /**
     * 代理节点
     */
    public function proxies(string $config_name, string $group_names = '')
    {
        if (!$config = Config::where(['name' => $config_name])->first()) {
            return ;
        }
        $meta_core = request()->input('meta', false);
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
                if ($protocol->name == 'vless' && !$meta_core) {
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
                $proxy['udp'] = true; # 允许在客户端开启UDP代理转发(需要服务端的代理节点支持UDP转发,一般不需要特别设置https://github.com/XTLS/Xray-core/discussions/237)
                $proxies[] = $proxy;
            }
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
            $result[] = $proxy;
            $array = explode('.', $proxy['name']);
            $transport = array_pop($array);
            $protocol = array_pop($array);

            if ($proxy['port'] != 443) { continue; }
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

    public function updateAnyCastIPV4(string $provider, string $ipv4, string $down = '', string $icmp = '')
    {
        $key = "{$provider}:anycast:ipv4";
        if (Cache::get($key) != $ipv4) {
            Cache::put($key, $ipv4, 7*86400);
            if (!empty($down) && !empty($icmp)) {
                AnycastLog::create([
                    'provider' => $provider,
                    'ipv4'     => $ipv4,
                    'down'     => $down,
                    'icmp'     => $icmp,
                ]);
            }
        }
        return $ipv4;
    }
}
