<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache;

use Illuminate\Cache\RedisTagSet as TagSet;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Contracts\StaleCacheInterface;

class StaleTagSet extends TagSet implements StaleCacheInterface
{
    /**
     * Get a unique namespace for the stale tags.
     *
     * @return string
     */
    public function getStaleNamespace()
    {
        return implode('|', $this->staleTagIds());
    }

    /**
     * Get an array of tag identifiers for all of the stale tags in the set.
     *
     * @return array
     */
    protected function staleTagIds()
    {
        return array_map([$this, 'staleTagId'], $this->names);
    }

    /**
     * Get the unique tag identifier for a given tag.
     *
     * @param  string  $name
     * @return string
     */
    public function staleTagId($name)
    {
        return $this->tagId($name . self::REFERENCE_STALE_KEY);
    }
}
