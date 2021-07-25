# WinterBoot Module - Memdb

Winter Memory DB (memdb) is a module that provides in-memory database engines integrated into [winter-boot](https://github.com/suvera/winter-boot) framework.

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
    - module: dev\winterframework\memdb\MemdbModule
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

```

# Redis

Redis configuration

```yaml
redis:
    -   name: redisServerBean01
        serverBinary: /usr/local/bin/redis-server
        confFile: redis_1.conf  # relative or absolute path
```

- Many number of server configurations allowed.
- Client Beans will be auto-created by framework by given name.  ex: "redisServerBean01"
```phpt
#[Autowired('redisServerBean01')]
private PhpRedisTemplate $redis;
```


# Apache Ignite

Apache Ignite configuration
