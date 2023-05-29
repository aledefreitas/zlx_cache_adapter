<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Contracts;

interface StaleCacheInterface
{
    /**
     * @var string
     */
    const REFERENCE_STALE_KEY = '::stale_cache_ref';
}
