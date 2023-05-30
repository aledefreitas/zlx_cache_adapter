<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter;

use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\CacheManager;
use Illuminate\Redis\RedisManager;
use Illuminate\Container\Container;

class Cache
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @param  array  $configs
     *
     * @return void
     */
    public static function init(array $configs = [])
    {
        $app = new Container();
        Container::setInstance($app);

        $app->singleton('config', function ($app) use ($configs) {
            $connections = [];
            $stores = [];

            foreach ($configs['instances'] as $instance => $config) {
                $connections[$instance] = [
                    'url' => $config['url'] ?? null,
                    'host' => $config['host'] ?? null,
                    'socket' => $config['socket'] ?? null,
                    'password' => $config['password'] ?? null,
                    'port' => $config['port'] ?? null,
                    'database' => $config['database'] ?? 0,
                    'prefix' => $config['prefix'] ?? '',
                ];

                $stores["cache.stores.{$instance}"] = [
                    'driver' => 'staleRedis',
                    'connection' => $instance,
                    'prefix' => $config['prefix'],
                ];
            }

            $app->singleton('redis', function ($app) use ($configs, $connections) {
                return new RedisManager(
                    $app,
                    $configs['client'] ?? env('REDIS_CLIENT', 'phpredis'),
                    [
                        'options' => [
                            'cluster' => $configs['cluster'] ?? env('REDIS_CLUSTER', 'redis'),
                            'prefix' => $configs['prefix'] ?? 'zlx_cache_',
                        ],
                        ...$connections
                    ]
                );
            });

            return [
                'cache.default' => 'array',
                'cache.stores.array' => [
                    'driver' => 'array',
                ],
                ... $stores,
            ];
        });


        $cacheManager = new CacheManager($app);

        self::$instances['array_instance'] = $cacheManager->driver('array');

        foreach ($configs['instances'] as $instanceName => $config) {
            self::$instances[$instanceName] = $cacheManager->driver($instanceName);
        }
    }

    /**
     * Returns an instance of a Cache connection
     *
     * @param  string  $instance
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    protected static function instance(string $instance = 'default')
    {
        return self::$instances[$instance] ?? self::$instances['array_instance'];
    }

    /**
     * Returns a Taggable Cache instance from a given key pattern
     *
     * @param  string  $key
     * @param  string  $instance
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    private static function getCacheTagByKey(string $key, string $instance)
    {
        $keyParts = explode('.', $key);

        return count($keyParts) > 1 ?
            self::instance($instance)->tags([
                $keyParts[0]
            ]) :
            self::instance($instance);
    }

	/**
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $instance
     *
     * @return void
     */
    public static function set(
        string $key,
        $value,
        string $instance = 'default'
    ) {
        $instance = self::getCacheTagByKey($key, $instance);

        return $instance->put($key, $value, 3600);
    }

	/**
     * @param  string  $key
     * @param  string  $instance
     * @param  bool  $use_stale
     *
     * @return mixed
     */
    public static function get(
        string $key,
        string $instance = 'default',
        bool $use_stale = true
    ) {
        $instance = self::getCacheTagByKey($key, $instance);

        if ($data = $instance->get($key)) {
            return $data;
        }

        if ($use_stale === true && $stale = $instance->getStale($key)) {
            return $stale;
        }

        return false;
    }

	/**
     * @param  bool  $ignore_prevents
     * @param  string  $instance
     *
     * @return void
     */
    public static function clear(
        bool $ignore_prevents = false,
        string $instance = 'default'
    ) {
        return self::instance($instance)->flush();
    }

	/**
     * @param  string  $key
     * @param  \Closure  $callable
     * @param  string  $instance
     * @param  bool  $use_stale
     *
     * @return mixed
     */
    public static function remember(
        string $key,
        \Closure $callable,
        string $instance = 'default',
        bool $use_stale = true
    ) {
        $instance = self::getCacheTagByKey($key, $instance);

        return $instance->remember(
            $key,
            3600,
            $callable
        );
    }

	/**
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $instance
     * @param  int  $ttl
     *
     * @return bool
     */
    public static function add(
        string $key,
        $value,
        string $instance = 'default',
        int $ttl = 5
    ) {
        $instance = self::getCacheTagByKey($key, $instance);

        return $instance->add($key, $value, $ttl);
    }

    /**
     * @param  string  $key
     * @param  string  $instance
     *
     * @return void
     */
    public static function delete(
        string $key,
        string $instance = 'default'
    ) {
        $instance = self::getCacheTagByKey($key, $instance);

        return $instance->forget($key);
    }

	/**
     * @param  string  $group
     * @param  string  $instance
     *
     * @return void
     */
    public static function clearGroup(string $group, string $instance = 'default')
    {
        return self::instance($instance)->tags([ $group ])->flush();
    }
}