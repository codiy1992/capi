<?php

namespace App\Services;

use App\Models\Proxy;
use App\Models\Group;
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
        $groups = Group::whereIn('name', $group_names)->get()->keyBy('name');

        $group_select = [
            'name'    => 'Proxy',
            'type'    => 'select',
            'use'     => ['provider_all'],
            'proxies' => ['fallback-auto'],
        ];
        $group_fallback = [
            'name'     => 'fallback-auto',
            'type'     => 'fallback',
            'url'      => 'http://www.gstatic.com/generate_204',
            'interval' => 300,
            'proxies'  => [],
        ];

        foreach($group_names as $name) {
            if (! $group = $groups[$name] ?? null) {
                continue;
            }
            $group->url = sprintf(
                "{$server_host}/proxies/%s?groups=%s&shuffle=%s&single=%s",
                $config_name, $name, $shuffle, $single);
            $group->path = "./providers/{$name}.yaml";
            $group->interval = $interval;
            $provider_name = sprintf("provier_%s", $name);
            $auto_name = sprintf("auto-%s", strtoupper($name));
            $group_auto = [
                'name' => $auto_name,
                'type' => 'url-test',
                'use' => [$provider_name],
                'url'      => 'http://www.gstatic.com/generate_204',
                'interval' => 300,
            ];
            $providers[$provider_name] = $group->toArray();
            $group_select['proxies'][] = $auto_name;
            $group_fallback['proxies'][] = $auto_name;
            $proxy_groups[] = $group_auto;
        }

        array_unshift($proxy_groups, $group_fallback);
        array_unshift($proxy_groups, $group_select);

        $provider_all = app(Group::class);
        $provider_all->url = sprintf(
            "{$server_host}/proxies/%s?groups=%s&shuffle=%s&single=%s",
            $config_name, implode(',', $group_names), $shuffle, $single);
        $provider_all->interval = $interval;

        $providers['provider_all'] = $provider_all->toArray();

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
        $group_names = explode(',', $group_names);

        $groups = Group::with(['proxies'=> function ($query) use ($config) {
            return $query
                ->when(!empty($config->types['allow']),
                fn($q) => $q->whereIn('type', $config->types['allow']))
                ->when(!empty($config->types['deny']),
                fn($q) => $q->whereNotIn('type', $config->types['deny']))
                ->when(!empty($config->ports['allow']),
                fn($q) => $q->whereIn('port', $config->ports['allow']))
                ->when(!empty($config->ports['deny']),
                fn($q) => $q->whereNotIn('port', $config->ports['deny']));
        }])->whereIn('name', $group_names)->get()->keyBy('name');

        $varied_proxies = [];
        foreach($group_names as $name) {
            $group = $groups[$name] ?? null;
            $proxies = optional($group)->proxies;
            if (!empty($proxies)) {
                $temp = [];
                foreach($proxies as $proxy) {
                    $temp[] = VariedProxy::format($proxy);
                }
                $shuffle && shuffle($temp);
                $single && $temp = [array_shift($temp)];
                $varied_proxies = array_merge($varied_proxies, $temp);
            }
        }
        return Yaml::dump(['proxies' => $varied_proxies,]);
    }
}
