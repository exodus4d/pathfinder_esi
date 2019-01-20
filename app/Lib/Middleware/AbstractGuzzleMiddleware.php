<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 20.01.2019
 * Time: 18:16
 */

namespace Exodus4D\ESI\Lib\Middleware;


use Cache\Adapter\Void\VoidCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;

abstract class AbstractGuzzleMiddleware {

    /**
     * @var null|\Closure
     */
    private $getCachePool = null;

    /**
     * @param RequestInterface $request
     * @param array $options
     */
    public function __invoke(RequestInterface $request, array $options){
        if(is_callable($options['get_cache_pool'])){
            $this->getCachePool = $options['get_cache_pool'];
        }
    }

    /**
     * get PSR-6 CachePool instance
     * @return CacheItemPoolInterface
     */
    protected function cache() : CacheItemPoolInterface {
        if(!is_null($this->getCachePool)){
            // return should be a full working PSR-6 Cache pool instance
            return ($this->getCachePool)();
        }else{
            // no Cache pool provided -> use default "void" Cache Pool
            // -> no storage at all! Dummy PSR-6
            return new VoidCachePool();
        }
    }

    protected function getKeyFromUrl(string $url) : string {

    }

    /**
     * get "normalized" url (ids in path get replaced)
     * @param string $url
     * @return string
     */
    protected function getNormalizedUrl(string $url) : string {
        $urlParts = parse_url($url);
        $urlParts['path'] = preg_replace('/\/(\d+)\//', '/x/', $urlParts['path']);
        return $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
    }
}