<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 11.01.2019
 * Time: 16:25
 */

namespace Exodus4D\ESI\Lib\Middleware\Cache\Storage;


use Exodus4D\ESI\Lib\Middleware\Cache\CacheEntry;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Psr6CacheStorage implements CacheStorageInterface {

    /**
     * The cache Pool
     * @var CacheItemPoolInterface
     */
    protected $cachePool;

    /**
     * The last item retrieved from the cache.
     * This item is transiently stored so that save() can reuse the cache item
     * usually retrieved by fetch() beforehand, instead of requesting it a second time.
     * @var CacheItemInterface|null
     */
    protected $lastItem;

    /**
     * Psr6CacheStorage constructor.
     * @param CacheItemPoolInterface $cachePool
     */
    public function __construct(CacheItemPoolInterface $cachePool){
        $this->cachePool = $cachePool;
    }

    /**
     * @param $key
     * @return CacheEntry|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function fetch($key) : ?CacheEntry {
        $item = $this->cachePool->getItem($key);
        $this->lastItem = $item;

        $cacheEntry = $item->get();

        return ($cacheEntry instanceof CacheEntry) ? $cacheEntry : null;
    }

    /**
     * @param string $key
     * @param CacheEntry $cacheEntry
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function save(string $key, CacheEntry $cacheEntry) : bool {
        if($this->lastItem && $this->lastItem->getKey() == $key){
            $item = $this->lastItem;
        }else{
            $item = $this->cachePool->getItem($key);
        }

        $this->lastItem = null;

        $item->set($cacheEntry);

        $ttl = $cacheEntry->getTTL();
        if($ttl === 0){
            // No expiration
            $item->expiresAfter(null);
        }else{
            $item->expiresAfter($ttl);
        }

        return $this->cachePool->save($item);
    }

    /**
     * @param string $key
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(string $key) : bool {
        if(!is_null($this->lastItem) && $this->lastItem->getKey() === $key) {
            $this->lastItem = null;
        }

        return $this->cachePool->deleteItem($key);
    }
}