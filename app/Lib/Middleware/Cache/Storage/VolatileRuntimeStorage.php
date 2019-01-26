<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 11.01.2019
 * Time: 19:42
 */

namespace Exodus4D\ESI\Lib\Middleware\Cache\Storage;


use Exodus4D\ESI\Lib\Middleware\Cache\CacheEntry;

class VolatileRuntimeStorage implements CacheStorageInterface {

    /**
     * @var CacheEntry[]
     */
    protected $cache = [];

    /**
     * @param $key
     * @return CacheEntry|null
     */
    public function fetch($key) : ?CacheEntry {
        return isset($this->cache[$key]) ? $this->cache[$key] : null;
    }

    /**
     * @param string $key
     * @param CacheEntry $data
     * @return bool
     */
    public function save(string $key, CacheEntry $data) : bool {
        $this->cache[$key] = $data;
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key) : bool {
        if(array_key_exists($key, $this->cache)){
            unset($this->cache[$key]);
            return true;
        }
        return false;
    }
}