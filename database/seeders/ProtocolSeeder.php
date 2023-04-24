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
                'password'  => 'sKl73yonPFEaTL10',
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
                'password'  => 'sKl73yonPFEaTL10',
                'extra' => json_encode([
                    'plugin'      => 'v2ray-plugin',
                    'plugin-opts' => [
                        'tls'              => true,
                        'mode'             => 'websocket',
                        'path'             => '/path_15rzfSRG',
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
                'password'  => 'sKl73yonPFEaTL10',
                'extra' => json_encode([
                    'plugin'      => 'v2ray-plugin',
                    'plugin-opts' => [
                        'tls'              => true,
                        'mode'             => 'grpc',
                        'serviceName'      => 'a9f48415-35c7-4e23-9e27-bf25898bf88d',
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
                'password'  => 'sKl73yonPFEaTL10',
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
                'password'  => 'sKl73yonPFEaTL10',
                'extra'     => json_encode([
                    'tls'       => true,
                    'network'   => 'grpc',
                    'grpc-opts' => [
                        'grpc-service-name' => 'tr_slmsx405',
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
                'password'  => 'sKl73yonPFEaTL10',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                    'ws-opts'          => [
                        'path' => '/path_Ls7uBqH4',
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
                'password'  => 'sKl73yonPFEaTL10',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'h2',
                    'skip-cert-verify' => true,
                    'h2-opts' => [
                        'path'             => 'path_NJZjvzm6',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'grpc',
                    'skip-cert-verify' => true,
                    'grpc-opts'        => [
                        'grpc-service-name' => 'vm_uSQAS4A1',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                    'ws-opts'          => [
                        'path' => '/path_b814PbYC',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'h2',
                    'skip-cert-verify' => true,
                    'h2-opts' => [
                        'path'             => '/path_w0ehdqRy',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'h2',
                    'skip-cert-verify' => true,
                    'h2-opts' => [
                        'path'             => '/path_2fuV5sKE',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'grpc',
                    'skip-cert-verify' => true,
                    'grpc-opts'        => [
                        'grpc-service-name' => 'vl_Ny6Z4idS',
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
                'uuid'      => '2bca9aac-75f6-4ae3-88f4-ea660daa0457',
                'password'  => '',
                'extra'     => json_encode([
                    'tls'              => true,
                    'network'          => 'ws',
                    'skip-cert-verify' => true,
                    'ws-opts'          => [
                        'path' => '/path_iLYxj9ip',
                    ],
                ]),
            ],
        ]);
    }
}
