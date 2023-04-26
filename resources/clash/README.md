## Clash 分流规则与DNS和TUN模式的协调

实验平台:
Macos(13.3.1), Clash for Windows(v0.20.21), Clash Core(2023.04.13 Premium), Wireshark

分流追求的目标: 
1. 对主流的google, twitter 域名不要发起DNS请求,尽可能将这类域名规则写在前面,直接让Proxy带走,防止DNS泄露
2. 对内网自定义的域名解析支持, 不影响使用Clash代理

### DNS和Tun模块都关闭

```
dns:
  enable: false
tun:
  enable: false
```

1. 匹配到基于域名规则的域名如 `DOMAIN-SUFFIX,yahabibiy.com,Proxy`, 不发起DNS请求, 直接代理走
2. 匹配到 DIRECT 规则, 或者基于目标IP的规则(IP-CIDR, GEOIP), 向本地DNS发起请求,是否代理看最终是DIRECT还是Proxy, 并且在最终规则是Proxy时,不论DNS是否解析出正确的IP,都会将原始请求转发给代理节点(在代理节点服务器再次做DNS,并处理请求)
3. 关于第2点, 是基于规则里没有 no-resolve, 如果有 no-resolve 也不会发起DNS请求

### DNS关闭,TUN开启

```
dns:
  enable: false
tun:
  enable: true
  stack: gvisor
  auto-route: true
  auto-detect-interface: true
  dns-hijack:
    - any:53
```

Clash for windows 开启TUN模式后(DNS模块关闭), 会默认把本地DNS改成 8.8.8.8
TUN 模式会新建虚拟网卡utun,可通过 wireshark 抓包查看里面的数据包
本地流量都会走到该网卡交给Clash内核处理. 本机在该网卡的IP默认为 198.18.0.1

1. 匹配到基于域名规则的域名如 `DOMAIN-SUFFIX,google.com,Proxy`, 不发起DNS请求, 直接代理走
2. 匹配到 DIRECT 规则, 或者基于目标IP的规则(IP-CIDR, GEOIP), 向TUN模块指定的DNS发起请求,是否代理看最终是DIRECT还是Proxy, 并且在最终规则是Proxy时,不论DNS是否解析出正确的IP,都会将原始请求转发给代理节点(在代理节点服务器再次做DNS,并处理请求)
3. 关于第2点, 是基于规则里没有 no-resolve, 如果有 no-resolve 也不会发起DNS请求

### DNS开启, TUN关闭

```
dns:
  enable: true
  listen: 0.0.0.0:53
  ipv6: true
  enhanced-mode: fake-ip
  fake-ip-range: 198.18.0.1/16
  fake-ip-filter:
    - stun.*.*
    - stun.*.*.*
    - +.stun.*.*
    - +.stun.*.*.*
    - +.stun.*.*.*.*
    - +.stun.*.*.*.*.*
  nameserver:
    - 114.114.114.114
    - 223.5.5.5
    - 8.8.8.8
  fallback:
    - dhcp://en0
tun:
  enable: false
```

TUN没有开启, 则即使指定`enhanced-mode: fake-ip`, 也不会使用到fake-ip (因为`fake-ip`依赖tun的虚拟网卡)

1. 匹配到基于域名规则的域名如 `DOMAIN-SUFFIX,google.com,Proxy`, 不发起DNS请求, 直接代理走
2. 匹配到 DIRECT 规则, 或者基于目标IP的规则(IP-CIDR, GEOIP), 向DNS模块中`nameserver` 和 `fallback`指定的DNS并行发起请求,是否代理看最终是DIRECT还是Proxy, 并且在最终规则是Proxy时,不论DNS是否解析出正确的IP,都会将原始请求转发给代理节点(在代理节点服务器再次做DNS,并处理请求)
> * 如果`nameserver`指定的DNS解析出来的IP是国内IP, 并且最终匹配的规则是 DIRECT, 则直接用此IP, 封装网络IP数据包发起请求
> * 如果`nameserver`指定的DNS解析出来的是国外IP或者解析失败, 则会使用`fallbak`DNS的解析结果(可以利用这一点,在本地内网,搞一些自定义域名解析,外网解析不到自动回落到`fallback`指定的`dhcp://en0`,即本地DNS解析).
> 但是如果加上`fallback-filter` 
> ```
> # If IP addresses resolved with servers in `nameservers` are in the specified
> # subnets below, they are considered invalid and results from `fallback`
> # servers are used instead.
> #
> # IP address resolved with servers in `nameserver` is used when
> # `fallback-filter.geoip` is true and when GEOIP of the IP address is `CN`.
> #
> # If `fallback-filter.geoip` is false, results from `nameserver` nameservers
> # are always used if not match `fallback-filter.ipcidr`.
> #
> # This is a countermeasure against DNS pollution attacks.
> fallback-filter:
>    geoip: ture
>    geoip-code: CN
>    ipcidr:
>      - 240.0.0.0/4
>      - 0.0.0.0/32
> ```
> 情况就不太一样, 虽然作者说明`geoip: ture`只要`geoip-code`是`CN`,就会使用`nameserver`的解析结果, 但在实践中, 即使`nameserver`的解析结果不是CN的IP,也会使用`nameserver`的解析结果,与预期不符. 所以最好不指定这个`fallback-filter`
3. 关于第2点, 是基于规则里没有 no-resolve, 如果有 no-resolve 也不会发起DNS请求


### DNS和TUN都开启

```
dns:
  enable: true
  listen: 0.0.0.0:53
  ipv6: true
  enhanced-mode: fake-ip
  fake-ip-range: 198.18.0.1/16
  fake-ip-filter:
    - stun.*.*
    - stun.*.*.*
    - +.stun.*.*
    - +.stun.*.*.*
    - +.stun.*.*.*.*
    - +.stun.*.*.*.*.*
  nameserver:
    - 114.114.114.114
    - 223.5.5.5
    - 8.8.8.8
  fallback:
    - dhcp://en0
tun:
  enable: true
  stack: gvisor
  auto-route: true
  auto-detect-interface: true
  dns-hijack:
    - any:53
```

Clash for windows 开启TUN模式后(DNS模块关闭), 会默认把本地DNS改成 8.8.8.8
TUN 模式会新建虚拟网卡utun,可通过 wireshark 抓包查看里面的数据包
本地流量都会走到该网卡交给Clash内核处理. 本机在该网卡的IP默认为 198.18.0.1
因为DNS模块开启了`fake-ip`, 域名将被解析成`198.18.0.1/16`下的IP

1. 匹配到基于域名规则的域名如 `DOMAIN-SUFFIX,google.com,Proxy`, 不发起DNS请求, 直接代理走
2. 匹配到 DIRECT 规则, 或者基于目标IP的规则(IP-CIDR, GEOIP), 仍然会向DNS模块中`nameserver` 和 `fallback`指定的DNS并行发起请求,同时还会在tun网卡向`dns-hijack`指定的DNS发起请求, 并且似乎会忽视DNS模块`fallback`相关设定,没办法正确走到`fallback`,是否代理看最终是DIRECT还是Proxy, 并且在最终规则是Proxy时,不论DNS是否解析出正确的IP,都会将原始请求转发给代理节点(在代理节点服务器再次做DNS,并处理请求)
> 如果手动把tun的`dns-hijack`配置移除,则表现的不太正常,一会能根据`nameserver`和`fallback`规则走到`fallback`, 一会又只用`nameserver`的结果
> 
> **结论: 只要开启了TUN模式, `fallback` 就没办法正常被使用, 可借助`nameserver-policy:`来手动指定DNS**
