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
                'port'      => 20000,
                'tls'       => 0,
                'cipher'    => 'chacha20-ietf-poly1305',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'MorHQzWFvJ8ox3h4',
                'extra'     => json_encode([]),
            ],
            [
                'name'  => 'ss',
                'transport' => 'websocket',
                'status'    => 1,
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'chacha20-ietf-poly1305',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'MorHQzWFvJ8ox3h4',
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
            [
                'name'  => 'ss',
                'transport' => 'grpc',
                'status'    => 0,
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'chacha20-ietf-poly1305',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'MorHQzWFvJ8ox3h4',
                'extra' => json_encode([
                    'plugin'      => 'v2ray-plugin',
                    'plugin-opts' => [
                        'tls'              => true,
                        'mode'             => 'grpc',
                        'serviceName'      => 'eed88573-8380-4379-b179-aa0c10f1716f',
                        'skip-cert-verify' => true
                    ],
                ]),
            ],
            // --------------------- Trojan ---------------------
            [
                'name'  => 'trojan',
                'transport' => 'tcp_tls',
                'status'    => 1,
                'port'      => 21001,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'MorHQzWFvJ8ox3h4',
                'extra'     => json_encode([]),
            ],
            [
                'name'  => 'trojan',
                'transport' => 'grpc',
                'status'    => 1,
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'MorHQzWFvJ8ox3h4',
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
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'MorHQzWFvJ8ox3h4',
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
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => '',
                'alterId'   => 0,
                'uuid'      => '',
                'password'  => 'MorHQzWFvJ8ox3h4',
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
                'port'      => 22000,
                'tls'       => 0,
                'cipher'    => 'none',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'     => false,
                    'network' => 'tcp',
                ]),
            ],
            [
                'name'  => 'vmess',
                'transport' => 'tcp_tls',
                'status'    => 1,
                'port'      => 22001,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
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
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
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
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
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
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
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
                'port'      => 22003,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
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
                'port'      => 22004,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                ]),
            ],
            // --------------------- Vless ---------------------
            [
                'name'  => 'vless',
                'transport' => 'tcp_tls_rprx_vision',
                'status'    => 1,
                'port'      => 23001,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'tcp',
                    'skip-cert-verify' => true,
                    'flow' => 'xtls-rprx-vision',
                    'client-fingerprint' => 'chrome',
                ]),
            ],
            [
                'name'  => 'vless',
                'transport' => 'grpc',
                'status'    => 1,
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'grpc',
                    'skip-cert-verify' => true,
                    'grpc-opts'        => [
                        'grpc-service-name' => 'd21dd88f-ad25-4fa0-9a3e-8fd84cfadf8a',
                    ],
                ]),
            ],
            [
                'name'  => 'vless',
                'transport' => 'websocket',
                'status'    => 1,
                'port'      => 443,
                'tls'       => 1,
                'cipher'    => 'auto',
                'alterId'   => 0,
                'uuid'      => '20bdb54b-9a2a-4e24-9103-980df15647ea',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                    'ws-opts'          => [
                        'path' => '/vless_ws',
                    ],
                ]),
            ],
        ]);
    }
}
