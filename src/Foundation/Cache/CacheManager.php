<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache;

use Illuminate\Cache\CacheManager as BaseManager;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Repository;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\RedisStore;
use Illuminate\Contracts\Cache\Store;

class CacheManager extends BaseManager
{
    /**
     * Create an instance of the Redis cache driver with stale data support.
     *
     * @param  array  $config
     * @return \Illuminate\Cache\Repository
     */
    protected function createStaleRedisDriver(array $config)
    {
        $redis = $this->app['redis'];

        $connection = $config['connection'] ?? 'default';

        $store = new RedisStore($redis, $this->getPrefix($config), $connection);

        return $this->repository(
            $store->setLockConnection($config['lock_connection'] ?? $connection)
        );
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  \Illuminate\Contracts\Cache\Store  $store
     * @return \Illuminate\Cache\Repository
     */
    public function repository(Store $store)
    {
        return tap(new Repository($store), function ($repository) {
            $this->setEventDispatcher($repository);
        });
    }
}
