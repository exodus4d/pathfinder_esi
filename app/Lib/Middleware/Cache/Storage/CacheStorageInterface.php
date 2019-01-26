<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 11.01.2019
 * Time: 16:25
 */

namespace Exodus4D\ESI\Lib\Middleware\Cache\Storage;


use Exodus4D\ESI\Lib\Middleware\Cache\CacheEntry;

interface CacheStorageInterface {

    /**
     * @param $key
     * @return CacheEntry|null
     */
    public function fetch($key) : ?CacheEntry;

    /**
     * @param string $key
     * @param CacheEntry $data
     * @return bool
     */
    public function save(string $key, CacheEntry $data) : bool;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key) : bool;
}