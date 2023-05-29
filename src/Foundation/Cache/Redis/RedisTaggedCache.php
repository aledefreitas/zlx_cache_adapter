<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Redis;

use Illuminate\Support\Str;
use Illuminate\Cache\RedisTaggedCache as BaseTaggedCache;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Contracts\StaleCacheInterface;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Concerns\CreatesStaleCacheTrait;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Concerns\LockAwareCacheTrait;

class RedisTaggedCache extends BaseTaggedCache implements StaleCacheInterface
{
    use CreatesStaleCacheTrait;
    use LockAwareCacheTrait;

    /**
     * {@inheritdoc}
     */
    protected function itemKey($key)
    {
        if (Str::endsWith($key, self::REFERENCE_STALE_KEY)) {
            return $this->staleTaggedItemKey($key);
        }

        return $this->taggedItemKey($key);
    }

    /**
     * Get a fully qualified key for a tagged item.
     *
     * @param  string  $key
     * @return string
     */
    public function staleTaggedItemKey($key)
    {
        return sha1($this->tags->getStaleNamespace()).':'.$key;
    }
}
