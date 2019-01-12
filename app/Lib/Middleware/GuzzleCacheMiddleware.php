<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 07.01.2019
 * Time: 20:02
 */

namespace Exodus4D\ESI\Lib\Middleware;

use Exodus4D\ESI\Lib\Cache\CacheEntry;
use Exodus4D\ESI\Lib\Cache\Storage\CacheStorageInterface;
use Exodus4D\ESI\Lib\Cache\Strategy\CacheStrategyInterface;
use Exodus4D\ESI\Lib\Cache\Strategy\PrivateCacheStrategy;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleCacheMiddleware {

    /**
     * default for: global enable this middleware
     */
    const DEFAULT_CACHE_ENABLED             = true;

    /**
     * default for: callback function returns
     * @var CacheStorageInterface
     */
    const DEFAULT_CACHE_STORAGE_CALLBACK    = null;

    /**
     * default for: cacheable HTTP methods
     */
    const DEFAULT_CACHE_HTTP_METHODS        = ['GET'];

    /**
     * default for: cacheable HTTP response status codes
     */
    const DEFAULT_CACHE_ON_STATUS           = [200];

    /**
     * default for: enable debug HTTP headers
     */
    const DEFAULT_CACHE_DEBUG               = false;

    /**
     * default for: debug HTTP Header name
     */
    const DEFAULT_CACHE_DEBUG_HEADER        = 'X-Guzzle-Cache';

    /**
     * default for: debug HTTP Header value for cached responses
     */
    const DEFAULT_CACHE_DEBUG_HEADER_HIT    = 'HIT';

    /**
     * default for: debug HTTP Header value for not cached responses
     */
    const DEFAULT_CACHE_DEBUG_HEADER_MISS   = 'MISS';

    /**
     * default for: debug HTTP Header value for staled responses
     */
    const DEFAULT_CACHE_DEBUG_HEADER_STALE  = 'STALE';

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'cache_enabled'             => self::DEFAULT_CACHE_ENABLED,
        'cache_http_methods'        => self::DEFAULT_CACHE_HTTP_METHODS,
        'cache_on_status'           => self::DEFAULT_CACHE_ON_STATUS,
        'cache_debug'               => self::DEFAULT_CACHE_DEBUG,
        'cache_debug_header'        => self::DEFAULT_CACHE_DEBUG_HEADER
    ];

    /**
     * @var CacheStrategyInterface
     */
    protected $cacheStrategy;

    /**
     * @var callable
     */
    private $nextHandler;

    /**
     * GuzzleCacheMiddleware constructor.
     * @param callable $nextHandler
     * @param array $defaultOptions
     * @param CacheStrategyInterface|null $cacheStrategy
     */
    public function __construct(callable $nextHandler, array $defaultOptions = [], ?CacheStrategyInterface $cacheStrategy = null){
        $this->nextHandler = $nextHandler;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);

        // if no CacheStrategyInterface defined
        // -> use default PrivateCacheStrategy
        $this->cacheStrategy = !is_null($cacheStrategy) ? $cacheStrategy : new PrivateCacheStrategy();
    }

    /*
     * cache response data for successful requests
     * -> load data from cache rather than sending the request
     * @param RequestInterface $request
     * @param array $options
     * @return FulfilledPromise
     */
    /*
    public function __invoke(RequestInterface $request, array $options){
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        // TODO
        $cacheEntry = null;

        if(!$response = $this->fetch($request)){
            // response not cached
            return $next($request, $options)->then(
                $this->onFulfilled($request, $cacheEntry, $options),
                $this->onRejected($cacheEntry)
            );
        }

        $response = static::addDebugHeader($response, self::DEFAULT_CACHE_DEBUG_HEADER_HIT, $options);

        return new FulfilledPromise($response);
    }*/


    public function __invoke(RequestInterface $request, array $options){
        var_dump('__invoke() Cache');
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        $cacheEntry = null;

        return $next($request, $options)->then(
            $this->onFulfilled($request, $cacheEntry, $options),
            $this->onRejected($cacheEntry, $options)
        );

      /*
        return function (RequestInterface $request, array $options) use (&$handler) {
var_dump('__invoke() Cache');

            $cacheEntry = null;

            $promise = $handler($request, $options);

            return $promise->then(
                $this->onFulfilled($request, $cacheEntry, $options),
                $this->onRejected($cacheEntry, $options)
            );
        }; */
    }

    /**
     * No exceptions were thrown during processing
     * @param RequestInterface $request
     * @param CacheEntry $cacheEntry
     * @param array $options
     * @return \Closure
     */
    protected function onFulfilled(RequestInterface $request, ?CacheEntry $cacheEntry, array $options) : \Closure {
        return function (ResponseInterface $response) use ($request, $cacheEntry, $options) {
            var_dump('onFullFilled() Cache ');

            // Check if error and looking for a staled content --------------------------------------------------------
            if($response->getStatusCode() >= 500){
                $responseStale = static::getStaleResponse($cacheEntry, $options);
                if($responseStale instanceof ResponseInterface){
                    return $responseStale;
                }
            }

            $update = false;

            // check for "Not modified" -> cache entry is re-validate -------------------------------------------------
            if($response->getStatusCode() == 304 && $cacheEntry instanceof CacheEntry){
                $response = $response->withStatus($cacheEntry->getResponse()->getStatusCode());
                $response = $response->withBody($cacheEntry->getResponse()->getBody());

                // Merge headers of the "304 Not Modified" and the cache entry
                /**
                 * @var string $headerName
                 * @var string[] $headerValue
                 */
                foreach($cacheEntry->getOriginalResponse()->getHeaders() as $headerName => $headerValue){
                    if(!$response->hasHeader($headerName) && $headerName !== $options['cache_debug_header']){
                        $response = $response->withHeader($headerName, $headerValue);
                    }
                }

                $response = static::addDebugHeader($response, self::DEFAULT_CACHE_DEBUG_HEADER_HIT, $options);
                $update = true;
            }else{
                $response = static::addDebugHeader($response, self::DEFAULT_CACHE_DEBUG_HEADER_MISS, $options);
            }

            return static::addToCache($this->cacheStrategy, $request, $response, $update);
        };
    }

    /**
     * An exception or error was thrown during processing
     * @param CacheEntry|null $cacheEntry
     * @param array $options
     * @return \Closure
     */
    protected function onRejected(?CacheEntry $cacheEntry, array $options) : \Closure {
        return function ($reason) use ($cacheEntry, $options) {
            var_dump('onRejected() Cache');

            if($reason instanceof TransferException){
                $response = static::getStaleResponse($cacheEntry, $options);
                if(!is_null($response)){
                    return $response;
                }
            }

            return new RejectedPromise($reason);
        };
    }

    /**
     * try to fetch response data from cache for the $request
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    protected function fetch(RequestInterface $request) : ?ResponseInterface {
        return null;
    }

    /**
     * try to store response data in cache
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $options
     */
    /*
    protected function cache(RequestInterface $request, ResponseInterface $response, array $options) : void {
        $cacheObject = $this->getCacheObject($request, $response, $options);

        if($cacheObject instanceof CacheEntry){
            // response is cacheable
            var_dump('cache().......');
            var_dump(get_class($this->storage));
        }
    }

    protected function getCacheObject(RequestInterface $request, ResponseInterface $response, array $options) : ?CacheEntry {
        if( !$options['cache_enabled'] ){
            return null;
        }

        if(
            in_array($request->getMethod(), (array)$options['cache_http_methods']) &&
            in_array($response->getStatusCode(), (array)$options['cache_on_status']) &&
            $response->hasHeader('Cache-Control')
        ){
            //$response = $response->withHeader('Cache-Control', 'public, max-age=31536000');

            $cacheControlHeader = \GuzzleHttp\Psr7\parse_header($response->getHeader('Cache-Control'));

            if(self::inArrayDeep($cacheControlHeader, 'no-store')){
                return null;
            }elseif(self::inArrayDeep($cacheControlHeader, 'no-cache')){
                // Stale response see RFC7234 section 5.2.1.4
                // TODO
            }elseif(self::inArrayDeep($cacheControlHeader, 'public')){

            }

            // "max-age" in "Cache-Control" Header overwrites "Expire" Header
            if($maxAge = (int)self::arrayKeyDeep($cacheControlHeader, 'max-age')){
                return new CacheEntry($request, $response, new \DateTime('+' . $maxAge . 'seconds'));
            }elseif($response->hasHeader('Expires')){
                $expireAt = \DateTime::createFromFormat(\DateTime::RFC1123, $response->getHeaderLine('Expires'));
                if($expireAt !== false){
                    return new CacheEntry($request, $response, $expireAt);
                }
            }
        }

        return null;
    }*/

    /**
     * add debug HTTP header to $response
     * -> Header can be checked whether a $response was cached or not
     * @param ResponseInterface $response
     * @param string $value
     * @param array $options
     * @return ResponseInterface
     */
    protected static function addDebugHeader(ResponseInterface $response, string $value, array $options) : ResponseInterface {
        if($options['cache_enabled'] && $options['cache_debug']){
            $response = $response->withHeader($options['cache_debug_header'], $value);
        }
        return $response;
    }

    /**
     * @param CacheStrategyInterface $cacheStrategy
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param bool $update
     * @return ResponseInterface
     */
    protected static function addToCache(CacheStrategyInterface $cacheStrategy, RequestInterface $request, ResponseInterface $response, $update = false) : ResponseInterface {
        // If the body is not seekable, we have to replace it by a seekable one
        if(!$response->getBody()->isSeekable()){
            $response = $response->withBody(\GuzzleHttp\Psr7\stream_for($response->getBody()->getContents()));
        }

        if($update){
            $cacheStrategy->update($request, $response);
        }else{
            $cacheStrategy->cache($request, $response);
        }

        return $response;
    }

    /**
     * @param CacheEntry|null $cacheEntry
     * @param array $options
     * @return ResponseInterface|null
     * @throws \Exception
     */
    protected static function getStaleResponse(?CacheEntry $cacheEntry, array $options) : ?ResponseInterface {
        // Return staled cache entry if we can
        if(!is_null($cacheEntry) && $cacheEntry->serveStaleIfError()){
            return static::addDebugHeader($cacheEntry->getResponse(), self::DEFAULT_CACHE_DEBUG_HEADER_STALE, $options);
        }
        return null;
    }

    /**
     * check if $search value exists in "deep" nested Array
     * @param array $array
     * @param string $search
     * @return bool
     */
    public static function inArrayDeep(array $array, string $search) : bool {
        $found = false;
        array_walk($array, function($value, $key, $search) use (&$found) {
            if(!$found && is_array($value) && in_array($search, $value)){
                $found = true;
            }
        }, $search);
        return $found;
    }

    /**
     *
     * @param array $array
     * @param string $searchKey
     * @return string
     */
    public static function arrayKeyDeep(array $array, string $searchKey) : string {
        $found = '';
        array_walk($array, function($value, $key, $searchKey) use (&$found) {
            if(empty($found) && is_array($value) && array_key_exists($searchKey, $value)){
                $found = (string)$value[$searchKey];
            }
        }, $searchKey);
        return $found;
    }

    /**
     * flatten multidimensional array ignore keys
     * @param array $array
     * @return array
     */
    public static function arrayFlattenByValue(array $array) : array {
        $return = [];
        array_walk_recursive($array, function($value) use (&$return) {$return[] = $value;});
        return $return;
    }

    /**
     * @param array $defaultOptions
     * @param CacheStrategyInterface|null $cacheStrategy
     * @return \Closure
     */
    public static function factory(array $defaultOptions = [], ?CacheStrategyInterface $cacheStrategy = null) : \Closure {
        return function(callable $handler) use ($defaultOptions, $cacheStrategy){
            return new static($handler, $defaultOptions, $cacheStrategy);
        };
    }
}