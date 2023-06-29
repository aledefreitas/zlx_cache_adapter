<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Concerns;

use Illuminate\Contracts\Cache\LockTimeoutException;
use \Closure;

trait LockAwareCacheTrait
{
    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param  string  $key
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @param  \Closure  $callback
     * @return mixed
     */
    public function remember($key, $ttl, ?Closure $callback = null)
    {
        $args = func_get_args();

        if ($args[1] instanceof Closure) {
            $callback = $args[1];
            $ttl = $this->getDefaultCacheTime();
        }

        $callback = $callback ?? fn () => null;

        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of seconds so it's available for all subsequent requests.
        if (!is_null($value)) {
            return $value;
        }

        try {
            $lock = $this->store->tags('locks')->lock($key . '::lock_key', 3);

            if (!$lock->get()) {
                $value = $this->getStale($key);
                if (!is_null($value) and $value !== false) {
                    return $value;
                }
            }

            $lock->betweenBlockedAttemptsSleepFor(500);

            $lock->block(3);
            $value = $this->get($key);

            if (is_null($value) or $value === false) {
                $value = call_user_func($callback);
                $this->put($key, $value, $ttl);
            }
        } catch (LockTimeoutException $e) {
            $value = false;
        } finally {
            $lock?->forceRelease();
        }

        if ($value === false) {
            $value = $this->getStale($key);

            if (is_null($value) or $value === false) {
                $value = call_user_func($callback);
                $this->put($key, $value, $ttl);
            }
        }

        // This reverts value back to its original value if it was a boolean false
        return $value === 'false' ? false : $value;
    }
}
