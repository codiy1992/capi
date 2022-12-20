<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtocolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('protocols')->truncate();
        DB::table('protocols')->insert([
            // --------------------- Shadowsocks ---------------------
            [
                'name'  => 'ss',
                'transport' => 'tcp_bare',
                'status'    => 0,
                'remark'    => '',
                'port'      => 10000,
                'tls'       => 0,
                'cipher'    => 'chacha20-ietf-poly1305',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'GsH0P3BYMvayzuMK',
                'extra'     => json_encode([]),
            ],
            [
                'name'  => 'ss',
                'transport' => 'ws_web_tls',
                'status'    => 0,
                'remark'    => '没跑通',
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'chacha20-ietf-poly1305',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'GsH0P3BYMvayzuMK',
                'extra' => json_encode([
                    'plugin'      => 'v2ray-plugin',
                    'plugin-opts' => [
                        'tls'              => true,
                        'mode'             => 'websocket',
                        'path'             => '/ss_ws',
                        'skip-cert-verify' => true
                    ],
                ]),
            ],
            // --------------------- Trojan ---------------------
            [
                'name'  => 'trojan',
                'transport' => 'tcp_bare',
                'status'    => 0,
                'remark'    => '',
                'port'      => 11000,
                'tls'       => 0,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'OxprCzKv7yxGz0i7',
                'extra'     => json_encode([]),
            ],
            [
                'name'  => 'trojan',
                'transport' => 'tcp',
                'status'    => 1,
                'remark'    => '',
                'port'      => 11001,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'OxprCzKv7yxGz0i7',
                'extra'     => json_encode([]),
            ],
            [
                'name'  => 'trojan',
                'transport' => 'grpc',
                'status'    => 1,
                'remark'    => '',
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'OxprCzKv7yxGz0i7',
                'extra'     => json_encode([
                    'tls'       => true,
                    'network'   => 'grpc',
                    'grpc-opts' => [
                        'grpc-service-name' => 'e7b2ae75-fbaf-4c23-8428-d2c1c9ff22f2',
                    ],
                ]),
            ],
            [
                'name'  => 'trojan',
                'transport' => 'websocket',
                'status'    => 1,
                'remark'    => '',
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'OxprCzKv7yxGz0i7',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                    'ws-opts'          => [
                        'path' => '/trojan_ws',
                    ],
                ]),
            ],
            [
                'name'  => 'trojan',
                'transport' => 'http2',
                'status'    => 0,
                'remark'    => '没跑通',
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'OxprCzKv7yxGz0i7',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'h2',
                    'skip-cert-verify' => true,
                    'h2-opts' => [
                        'path'             => '/trojan_h2c',
                        'host'             => [
                            'cm.bilibili.com',
                            'data.bilibili.com',
                            'pcsdata.baidu.com',
                            'static.awsevents.cn',
                            'merak.alicdn.com',
                        ],
                    ],
                ]),
            ],
            // --------------------- Vmess ---------------------
            [
                'name'  => 'vmess',
                'transport' => 'tcp_bare',
                'status'    => 0,
                'remark'    => '',
                'port'      => 12000,
                'tls'       => 0,
                'cipher'    => 'none',
                'alterId'   => 0,
                'uuid'      => '931c89af-a362-44c5-80d2-bda173592f68',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'     => false,
                    'network' => 'tcp',
                ]),
            ],
            [
                'name'  => 'vmess',
                'transport' => 'tcp',
                'status'    => 1,
                'remark'    => '',
                'port'      => 12001,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '931c89af-a362-44c5-80d2-bda173592f68',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'tcp',
                    'skip-cert-verify' => true,
                ]),
            ],
            [
                'name'  => 'vmess',
                'transport' => 'grpc',
                'status'    => 1,
                'remark'    => '',
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '931c89af-a362-44c5-80d2-bda173592f68',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'grpc',
                    'skip-cert-verify' => true,
                    'grpc-opts'        => [
                        'grpc-service-name' => 'e4512876-ef85-4ee9-afd4-ac5f3371d33e',
                    ],
                ]),
            ],
            [
                'name'  => 'vmess',
                'transport' => 'websocket',
                'status'    => 1,
                'remark'    => '',
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '931c89af-a362-44c5-80d2-bda173592f68',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                    'ws-opts'          => [
                        'path' => '/vmess_ws',
                    ],
                ]),
            ],
            [
                'name'  => 'vmess',
                'transport' => 'http2',
                'status'    => 0,
                'remark'    => '没跑通',
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '931c89af-a362-44c5-80d2-bda173592f68',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'h2',
                    'skip-cert-verify' => true,
                    'h2-opts' => [
                        'path'             => '/vmess_h2c',
                        'host'             => [
                            'cm.bilibili.com',
                            'data.bilibili.com',
                            'pcsdata.baidu.com',
                            'static.awsevents.cn',
                            'merak.alicdn.com',
                        ],
                    ],
                ]),
            ],
            [
                'name'  => 'vmess',
                'transport' => 'h2_tls',
                'status'    => 1,
                'remark'    => '',
                'port'      => 12003,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '931c89af-a362-44c5-80d2-bda173592f68',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'h2',
                    'skip-cert-verify' => true,
                    'h2-opts' => [
                        'path'             => '/vmess_h2_tls',
                        'host'             => [
                            'cm.bilibili.com',
                            'data.bilibili.com',
                            'pcsdata.baidu.com',
                            'static.awsevents.cn',
                            'merak.alicdn.com',
                        ],
                    ],
                ]),
            ],
            [
                'name'  => 'vmess',
                'transport' => 'ws_tls',
                'status'    => 1,
                'remark'    => '',
                'port'      => 12004,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '931c89af-a362-44c5-80d2-bda173592f68',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                ]),
            ],
        ]);
    }
}
