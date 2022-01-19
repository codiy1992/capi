<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Group;
use App\Models\Proxy;
use App\Models\GroupProxy;

class UpdateGroupProxies extends Command
{

    protected $signature = 'update-group-proxies';

    protected $description = '更新代理分组';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $where = [];
        $where[] = ['modify', '=', 1];
        Proxy::withoutGlobalScopes()->where($where)->chunk(100, function($records) {
            foreach($records as $proxy) {
                $group_names = explode(',', $proxy->groups);
                array_walk($group_names, function(&$value) { $value = trim(strtolower($value)); });
                $group_ids = Group::whereIn('name', $group_names)->pluck('id')->toArray();
                if ($group_proxies = GroupProxy::where([
                    'proxy_id' => $proxy->id,
                ])->get()) {
                    foreach($group_proxies as $group_proxy) {
                        if (!in_array($group_proxy->group_id, $group_ids)) {
                            $group_proxy->delete();
                        } else {
                            $group_ids = array_diff($group_ids, [$group_proxy->group_id]);
                        }
                    }
                }

                if (!empty($group_ids)) {
                    foreach($group_ids as $group_id) {
                        GroupProxy::create([
                            'group_id' => $group_id,
                            'proxy_id' => $proxy->id,
                        ]);
                    }
                }
                $proxy->modify = 0;
                $proxy->save();
            }
        });
    }
}
