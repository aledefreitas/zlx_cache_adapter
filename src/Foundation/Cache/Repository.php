<?php

/**
 * @author Alexandre Caetano
 */

namespace Aledefreitas\ZlxCacheAdapter\Foundation\Cache;

use Illuminate\Cache\Repository as BaseRepository;
use \Closure;

use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Concerns\CreatesStaleCacheTrait;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Concerns\LockAwareCacheTrait;
use Aledefreitas\ZlxCacheAdapter\Foundation\Cache\Contracts\StaleCacheInterface;

class Repository extends BaseRepository implements StaleCacheInterface
{
    use CreatesStaleCacheTrait;
    use LockAwareCacheTrait;
}
