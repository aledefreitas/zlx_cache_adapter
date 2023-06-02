<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Concerns;

use Illuminate\Support\Str;

trait CreatesStaleCacheTrait
{
    /**
     * Store an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @return bool
     */
    public function put($key, $value, $ttl = null)
    {
        return parent::put($key, $value, $ttl);
    }

    /**
     * Store an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @return bool
     */
    public function putStale($key, $value, $ttl = null)
    {
        if (!Str::endsWith($key, self::REFERENCE_STALE_KEY)) {
            return parent::tags('stale')->put($key . self::REFERENCE_STALE_KEY, $value, !is_null($ttl) ? $this->getSeconds($ttl) : $this->getDefaultCacheTime());
        }

        return false;
    }

    /**
     * Retrieves a stale item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function getStale($key)
    {
        return $this->tags('stale')->get($key . self::REFERENCE_STALE_KEY);
    }
}
