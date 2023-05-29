<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache;

use Illuminate\Cache\RedisStore as BaseStore;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Redis\RedisTaggedCache;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\StaleTagSet;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Contracts\StaleCacheInterface;

class RedisStore extends BaseStore implements StaleCacheInterface
{
    /**
     * Begin executing a new tags operation.
     *
     * @param  array|mixed  $names
     * @return \Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Redis\RedisTaggedCache
     */
    public function tags($names)
    {
        return new RedisTaggedCache(
            $this, new StaleTagSet($this, is_array($names) ? $names : func_get_args())
        );
    }
}
