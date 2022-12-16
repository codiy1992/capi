<?php

namespace App\Services;

use App\Models\Protocol;
use App\Models\Server;
use App\Models\Config;
use App\Facades\VariedProxy;
use Symfony\Component\Yaml\Yaml;

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
                if (!$protocol->status && !$config->debug) {
                    continue;
                }
                $proxy = VariedProxy::format($protocol);
                $proxy['name'] = sprintf("%s.%s.%s.%s", $server->group, $server->name, $protocol->name, $protocol->transport);
                $proxy['type'] = $protocol->name;
                $proxy['server'] = $protocol->tls ? sprintf('%s.0x256.com', $server->ipv4) : $server->ipv4;
                $proxies[] = $proxy;
            }
            $shuffle && shuffle($proxies);
            $single && $proxies = [array_shift($proxies)];
        }
        return Yaml::dump(['proxies' => $proxies,]);
    }
}
