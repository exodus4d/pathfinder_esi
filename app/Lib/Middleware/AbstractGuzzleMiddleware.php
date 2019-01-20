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

    /**
     * get a hashed key from $request URL
     * @see cacheKeyFromUrl()
     * @param RequestInterface $request
     * @param string $tag
     * @return string
     */
    protected function cacheKeyFromRequestUrl(RequestInterface $request, string $tag = '') : string {
        return $this->cacheKeyFromUrl($request->getUri()->__toString(), $tag);
    }

    /**
     * get a hashed key from $url
     * -> $url gets normalized and GET params are stripped
     * -> $tag can be used to get multiple unique keys for same $url
     * @param string $tag
     * @param string $url
     * @return string
     */
    protected function cacheKeyFromUrl(string $url, string $tag = '') : string {
        return $this->hashKey($this->getNormalizedUrl($url) . $tag);
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

    /**
     * get valid PSR-6 key name
     * @see http://www.php-cache.com/en/latest/introduction/#cache-keys
     * @param string $key
     * @return string
     */
    protected function hashKey(string $key) : string {
        return sha1($key);
    }
}