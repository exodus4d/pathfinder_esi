<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 11.01.2019
 * Time: 22:58
 */

namespace Exodus4D\ESI\Lib\Middleware\Cache\Strategy;


use Exodus4D\ESI\Lib\Middleware\Cache\CacheEntry;
use Exodus4D\ESI\Lib\Middleware\Cache\Storage\CacheStorageInterface;
use Exodus4D\ESI\Lib\Middleware\Cache\Storage\VolatileRuntimeStorage;
use Exodus4D\ESI\Lib\Middleware\GuzzleCacheMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PrivateCacheStrategy implements CacheStrategyInterface {

    /**
     * @var CacheStorageInterface
     */
    protected $storage;

    /**
     * @var int[]
     */
    protected $statusAccepted = [
        200 => 200,
        203 => 203,
        204 => 204,
        300 => 300,
        301 => 301,
        404 => 404,
        405 => 405,
        410 => 410,
        414 => 414,
        418 => 418,
        501 => 501
    ];

    /**
     * @var string[]
     */
    protected $ageKey = [
        'max-age'
    ];

    /**
     * PrivateCacheStrategy constructor.
     * @param CacheStorageInterface|null $cache
     */
    public function __construct(CacheStorageInterface $cache = null){
        // if no CacheStorageInterface (e.g. Psr6CacheStorage) defined
        // -> use default VolatileRuntimeStorage (store data in temp array)
        $this->storage = !is_null($cache) ? $cache : new VolatileRuntimeStorage();
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return CacheEntry|null entry to save, null if can't cache it
     */

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return CacheEntry|null -> entry to save, null if can't cache it
     * @throws \Exception
     */
    protected function getCacheObject(RequestInterface $request, ResponseInterface $response) : ?CacheEntry {
        if(!isset($this->statusAccepted[$response->getStatusCode()])){
            // Don't cache it
            return null;
        }

        if($response->hasHeader('Vary')){
            $varyHeader = \GuzzleHttp\Psr7\parse_header($response->getHeader('Vary'));
            if(GuzzleCacheMiddleware::inArrayDeep($varyHeader, '*')){
                // This will never match with a request
                return null;
            }
        }

        if($response->hasHeader('Cache-Control')){
            $cacheControlHeader = \GuzzleHttp\Psr7\parse_header($response->getHeader('Cache-Control'));

            if(GuzzleCacheMiddleware::inArrayDeep($cacheControlHeader, 'no-store')){
                // No store allowed (maybe some sensitives data...)
                return null;
            }

            if(GuzzleCacheMiddleware::inArrayDeep($cacheControlHeader, 'no-cache')){
                // Stale response see RFC7234 section 5.2.1.4
                $cacheEntry = new CacheEntry($request, $response, new \DateTime('-1 seconds'));
                return $cacheEntry->hasValidationInformation() ? $cacheEntry : null;
            }

            if($maxAge = (int)GuzzleCacheMiddleware::arrayKeyDeep($cacheControlHeader, 'max-age')){
                // Proper max-age send in response (preferred)
                return new CacheEntry($request, $response, new \DateTime('+' . $maxAge . 'seconds'));
            }

            if($response->hasHeader('Expires')){
                // Expire Header is the last possible header that effects caching (better to use max-age)
                $expireAt = \DateTime::createFromFormat(\DateTime::RFC1123, $response->getHeaderLine('Expires'));
                if($expireAt !== false){
                    return new CacheEntry($request, $response, $expireAt);
                }
            }
        }

        return new CacheEntry($request, $response, new \DateTime('-1 seconds'));
    }

    /**
     * Generate a key for the response cache
     * @param RequestInterface $request
     * @param array $varyHeaders $varyHeaders The vary headers which should be honoured by the cache (optional)
     * @return string
     */
    protected function getCacheKey(RequestInterface $request, array $varyHeaders = []){
        if(empty($varyHeaders)){
            return hash('sha256', $request->getMethod() . $request->getUri());
        }

        $cacheHeaders = [];
        foreach($varyHeaders as $varyHeader){
            if($request->hasHeader($varyHeader)){
                $cacheHeaders[$varyHeader] = $request->getHeader($varyHeader);
            }
        }

        return hash('sha256', $request->getMethod() . $request->getUri() . json_encode($cacheHeaders));
    }

    /**
     * Return a CacheEntry or null if no cache
     * @param RequestInterface $request
     * @return CacheEntry|null
     */
    public function fetch(RequestInterface $request) : ?CacheEntry {
        /**
         * @var int|null $maxAge
         */
        $maxAge = null;
        if($request->hasHeader('Cache-Control')){
            $reqCacheControl = \GuzzleHttp\Psr7\parse_header($request->getHeader('Cache-Control'));
            if(GuzzleCacheMiddleware::inArrayDeep($reqCacheControl, 'no-cache')){
                // Can't return cache
                return null;
            }
            $maxAge = (int)GuzzleCacheMiddleware::arrayKeyDeep($reqCacheControl, 'max-age') ? : null;
        }elseif($request->hasHeader('Pragma')){
            $pragma = \GuzzleHttp\Psr7\parse_header($request->getHeader('Pragma'));
            if(GuzzleCacheMiddleware::inArrayDeep($pragma, 'no-cache')){
                // Can't return cache
                return null;
            }
        }

        $cache = $this->storage->fetch($this->getCacheKey($request));

        if(!is_null($cache)){
            $varyHeaders = $cache->getVaryHeaders();
            // vary headers exist from a previous response, check if we have a cache that matches those headers
            if(!empty($varyHeaders)){
                $cache = $this->storage->fetch($this->getCacheKey($request, $varyHeaders));
                if(!$cache){
                    return null;
                }
            }

            if((string)$cache->getOriginalRequest()->getUri() !== (string)$request->getUri()){
                return null;
            }

            if(!is_null($maxAge)){
                if($cache->getAge() > $maxAge){
                    // Cache entry is too old for the request requirements!
                    return null;
                }
            }

            if(!$cache->isVaryEquals($request)){
                return null;
            }
        }
        return $cache;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool true if success
     * @throws \Exception
     */
    public function cache(RequestInterface $request, ResponseInterface $response) : bool {
        if($request->hasHeader('Cache-Control')){
            $reqCacheControl = \GuzzleHttp\Psr7\parse_header($request->getHeader('Cache-Control'));
            if(GuzzleCacheMiddleware::inArrayDeep($reqCacheControl, 'no-store')){
                // No caching allowed
                return false;
            }
        }

        $cacheObject = $this->getCacheObject($request, $response);
        if(!is_null($cacheObject)){
            // store the cache against the URI-only key
            $success = $this->storage->save($this->getCacheKey($request), $cacheObject);

            $varyHeaders = $cacheObject->getVaryHeaders();
            if(!empty($varyHeaders)){
                // also store the cache against the vary headers based key
                $success = $this->storage->save($this->getCacheKey($request, $varyHeaders), $cacheObject);
            }
            return $success;
        }
        return false;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool true if success
     * @throws \Exception
     */
    public function update(RequestInterface $request, ResponseInterface $response) : bool {
        return $this->cache($request, $response);
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function delete(RequestInterface $request) : bool {
        return $this->storage->delete($this->getCacheKey($request));
    }
}