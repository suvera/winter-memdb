# WinterBoot Module - Memdb

Winter Memory DB (memdb) is a module that provides in-memory database engines integrated
into [winter-boot](https://github.com/suvera/winter-boot) framework.

Following databases maybe integrated, Auto-started on application startup and stopped upon application stop.

- Redis
- Apache Ignite
- Hazelcast
- Memcached
- RocksDB
- H2

## Setup

1. This requires `swoole` php extension

```shell
composer require suvera/winter-memdb
```

To enable Memdb module in applications, append following code to **application.yml**

```yaml
modules:
    -   module: dev\winterframework\memdb\MemdbModule
        enabled: true
        configFile: memdb-config.yml

```

**configFile** is a file path (relative to config dir or absolute path)

## memdb-config.yml

```yaml
# Redis
redis:
# Redis Configuration here, see below section

# Apache Ignite
ignite:
# Apache Ignite Configuration here

# Memcached
memcached:
# Memcached Configuration here
```

### 1. Redis

Redis configuration

```yaml
redis:
    -   name: redisServerBean01
        serverBinary: /usr/local/bin/redis-server
        confFile: redis_1.conf  # relative or absolute path
        args: ""
```

- Many number of server configurations allowed.
- Client Beans will be auto-created by framework by given name. ex: "redisServerBean01"

```phpt
#[Autowired('redisServerBean01')]
private PhpRedisTemplate $redis;
```

### 2. Apache Ignite

Apache Ignite configuration

```yaml
ignite:
    -   name: igniteServerBean01
        serverBinary: /etc/apache-ignite-2.10.0/bin/ignite.sh
        # Optional config
        confFile: ignite-config.xml  # relative or absolute path
        args: ""
```

- Many number of server configurations allowed.
- Client Beans will be auto-created by framework by given name. ex: "igniteServerBean01"

```phpt
#[Autowired('igniteServerBean01')]
private IgniteCacheTemplate $ignite;
```

### 3. Memcached

- This requires `memcached` or `memcache` php extension

Memcached configuration.

```yaml
memcached:
    -   name: memcachedServerBean01
        serverBinary: /usr/bin/memcached
        host: 127.0.0.1
        port: 11211
        udpPort: 0
        unixSocket:
        pidfile: "/var/run/memcached_1.pid"
        args: "-u memcached "
```

- Many number of server configurations allowed.
- Client Beans will be auto-created by framework by given name. ex: "memcachedServerBean01"

```phpt
// `memcached` php extension installed
#[Autowired('memcachedServerBean01')]
private MemcachedTemplate $memcached;

// OR

//  if `memcache` php extension installed
#[Autowired('memcacheBean02')]
private MemcacheTemplate $memcache;
```

### 4. Hazelcast

- Hazelcast needs `memcached` php extension as a client

```shell
pecl install memcached
```

Hazelcast configuration.

```yaml
hazelcast:
    -   name: hazelcastBean01
        serverBinary: etc/hazelcast-4.2.1/bin/start.sh
        confFile: /etc/hazelcast/config/hazelcast.xml
        bootUpTimeMs: 300
        args: ""
```

- Many number of server configurations allowed.
- Client Beans will be auto-created by framework by given name. ex: "hazelcastBean01"

```phpt
#[Autowired('hazelcastBean01)]
private MemcachedTemplate $memcached;
```

>> Note: Hazelcast configuration need memcache enabled.  see it here https://docs.hazelcast.com/imdg/4.2/clients/memcache.html

```xml
<hazelcast>
    ...
    <network>
        <memcache-protocol enabled="true"/>
    </network>
    ...
</hazelcast>
```
