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
        $group_names = request()->input('groups', $config->groups);
        $group_names = explode(',', $group_names);
        array_walk($group_names, function(&$value) { $value = trim(strtolower($value)); });
        $interval = (int)request()->input('interval', $config->interval);
        $shuffle = (int)request()->input('shuffle', $config->shuffle);
        $single = (int)request()->input('single', $config->single);

        $proxy_groups_and_providers = $this->genProxyGroupsAndProviders($name, $group_names, $interval, $shuffle, $single);

        // Load Yaml Files
        $config = Yaml::parseFile(resource_path('clash/config.yaml'));
        $dns = Yaml::parseFile(resource_path('clash/dns.yaml'));
        $rules = Yaml::parseFile(resource_path('clash/rules.yaml'));
        $dns['dns']['enable'] = (bool) $dns_enable;
        return Yaml::dump(array_merge(
            $config, $dns, $proxy_groups_and_providers, $rules
        ), 2, 2);
    }

    public function genProxyGroupsAndProviders(
        string $config_name,
        array $group_names,
        $interval = 3600,
        $shuffle = 0,
        $single = 0
    )
    {
        $proxy_providers = [];
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
        $valid_groups = array_unique(Server::whereIn('group', $group_names)->pluck('group')->toArray());
        array_walk($valid_groups, function(&$value) { $value = trim(strtolower($value)); });
        foreach($group_names as $name) {
            if (!in_array($name, $valid_groups)) {continue;}
            $provider_name = sprintf("provier_%s", $name);
            $auto_name = sprintf("auto-%s", strtoupper($name));
            $group_auto = [
                'name' => $auto_name,
                'type' => 'url-test',
                'use' => [$provider_name],
                'url'      => 'http://www.gstatic.com/generate_204',
                'interval' => 300,
            ];
            $providers[$provider_name] = [
                'url'      => sprintf(
                        "{$server_host}/proxies/%s?groups=%s&shuffle=%s&single=%s",
                        $config_name, $name, $shuffle, $single
                    ),
                'path'     => "./providers/{$name}.yaml",
                'interval' => $interval,
                'type'     => 'http',
                'health-check' => [
                    'url'      => 'http://www.gstatic.com/generate_204',
                    'enable'   => true,
                    'interval' => 600
                ],
            ];
            $group_select['proxies'][] = $auto_name;
            $group_fallback['proxies'][] = $auto_name;
            $proxy_groups[] = $group_auto;
        }

        array_unshift($proxy_groups, $group_fallback);
        array_unshift($proxy_groups, $group_select);

        $providers['provider_all'] = [
            'url' => sprintf(
                    "{$server_host}/proxies/%s?groups=%s&shuffle=%s&single=%s",
                    $config_name, implode(',', $group_names), $shuffle, $single
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

        return [
            'proxy-providers' => $providers,
            'proxy-groups' => $proxy_groups,
        ];
    }

    public function proxies(
        string $config_name,
        string $group_names = '',
        bool $shuffle = false,
        bool $single = false
    )
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
                if (!empty($proxy['ws-opts'])) {
                    $proxy['ws-opts']['headers']['host'] = $proxy['server'];
                }
                $proxies[] = $proxy;
            }
            $shuffle && shuffle($proxies);
            $single && $proxies = [array_shift($proxies)];
        }
        return Yaml::dump(['proxies' => $this->cdnWrap($proxies, $config),]);
    }

    public function cdnWrap($proxies, Config $config)
    {
        $result = [];
        foreach ($proxies as $proxy) {
            $result[] = $proxy;
            $array = explode('.', $proxy['name']);
            $transport = array_pop($array);
            $protocol = array_pop($array);
            if ($proxy['port'] == 443) {
                $worker = $proxy;
                // cdn
                $proxy['name'] = "{$proxy['name']}_cdn";
                $proxy['server'] = str_replace('.0x256.com', '', $proxy['server']);
                $proxy['server'] = str_replace('.', '', $proxy['server']) . '.0x256.com';
                in_array($protocol, ['trojan', 'vmess']) && $proxy['servername'] = $proxy['server'];
                if (!empty($proxy['plugin-opts']['host'])) {
                    $proxy['plugin-opts']['host'] = $proxy['server'];
                }
                if (!empty($proxy['ws-opts'])) {
                    $proxy['ws-opts']['headers']['host'] = $proxy['server'];
                }
                if (!empty($config->extra['anycast'])) {
                    $proxy['server'] = Cache::get('cloudflare:anycast:ipv4', $proxy['server']);
                }
                $result[] = $proxy;
                if (!empty($config->extra['worker'])) {
                    // worker
                    $worker['name'] = "{$worker['name']}_worker";
                    $worker['server'] = "w{$proxy['server']}";
                    in_array($protocol, ['trojan', 'vmess']) && $worker['servername'] = $worker['server'];
                    if (!empty($worker['plugin-opts']['host'])) {
                        $worker['plugin-opts']['host'] = $worker['server'];
                    }
                    if (!empty($proxy['ws-opts'])) {
                        $worker['ws-opts']['headers']['host'] = $worker['server'];
                    }
                    if (!empty($config->extra['anycast'])) {
                        $worker['server'] = Cache::get('cloudflare:anycast:ipv4', $worker['server']);
                    }
                    $result[] = $worker;
                }
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

    public function updateAnyCastIPV4(string $ipv4)
    {
        $key = 'cloudflare:anycast:ipv4';
        if (Cache::get($key) != $ipv4) {
            Cache::put($key, $ipv4, 86400);
        }
        return $ipv4;
    }
}
