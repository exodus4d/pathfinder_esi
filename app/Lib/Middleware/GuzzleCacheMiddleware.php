<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 07.01.2019
 * Time: 20:02
 */

namespace Exodus4D\ESI\Lib\Middleware;

use Exodus4D\ESI\Lib\Middleware\Cache\CacheEntry;
use Exodus4D\ESI\Lib\Middleware\Cache\Strategy\CacheStrategyInterface;
use Exodus4D\ESI\Lib\Middleware\Cache\Strategy\PrivateCacheStrategy;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleCacheMiddleware {

    /**
     * default for: global enable this middleware
     */
    const DEFAULT_CACHE_ENABLED                 = true;

    /**
     * default for: cacheable HTTP methods
     */
    const DEFAULT_CACHE_HTTP_METHODS            = ['GET'];

    /**
     * default for: enable debug HTTP headers
     */
    const DEFAULT_CACHE_DEBUG                   = false;

    /**
     * default for: revalidate cache HTTP headers
     */
    const DEFAULT_CACHE_RE_VALIDATION_HEADER    = 'X-Guzzle-Cache-ReValidation';

    /**
     * default for: debug HTTP Header name
     */
    const DEFAULT_CACHE_DEBUG_HEADER            = 'X-Guzzle-Cache';

    /**
     * default for: debug HTTP Header value for cached responses
     */
    const DEFAULT_CACHE_DEBUG_HEADER_HIT        = 'HIT';

    /**
     * default for: debug HTTP Header value for not cached responses
     */
    const DEFAULT_CACHE_DEBUG_HEADER_MISS       = 'MISS';

    /**
     * default for: debug HTTP Header value for staled responses
     */
    const DEFAULT_CACHE_DEBUG_HEADER_STALE      = 'STALE';

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'cache_enabled'             => self::DEFAULT_CACHE_ENABLED,
        'cache_http_methods'        => self::DEFAULT_CACHE_HTTP_METHODS,
        'cache_debug'               => self::DEFAULT_CACHE_DEBUG,
        'cache_debug_header'        => self::DEFAULT_CACHE_DEBUG_HEADER
    ];

    /**
     * @var Promise[]
     */
    protected $waitingRevalidate = [];

    /**
     * @var Client
     */
    protected $client;

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

        register_shutdown_function([$this, 'purgeReValidation']);
    }

    /**
     * Will be called at the end of the script
     */
    public function purgeReValidation() : void {
        \GuzzleHttp\Promise\inspect_all($this->waitingRevalidate);
    }

    /**
     * cache response data for successful requests
     * -> load data from cache rather than sending the request
     * @param RequestInterface $request
     * @param array $options
     * @return FulfilledPromise
     * @throws \Exception
     */
    public function __invoke(RequestInterface $request, array $options){
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        if(!$options['cache_enabled']){
            // middleware disabled -> skip
            return $next($request, $options);
        }

        // check if request HTTP Method can be cached -----------------------------------------------------------------
        if(!in_array(strtoupper($request->getMethod()), (array)$options['cache_http_methods'])){
            // No caching for this method allowed
            return $next($request, $options)->then(
                function(ResponseInterface $response) use ($options) {
                    return static::addDebugHeader($response, self::DEFAULT_CACHE_DEBUG_HEADER_MISS, $options);
                }
            );
        }

        // check if it´s is a re-validation request, so bypass the cache! ---------------------------------------------
        if($request->hasHeader(self::DEFAULT_CACHE_RE_VALIDATION_HEADER)){
            // It's a re-validation request, so bypass the cache!
            return $next($request->withoutHeader(self::DEFAULT_CACHE_RE_VALIDATION_HEADER), $options);
        }

        // Retrieve information from request (Cache-Control) ----------------------------------------------------------
        $onlyFromCache  = false;
        $staleResponse  = false;
        $maxStaleCache  = null;
        $minFreshCache  = null;

        if($request->hasHeader('Cache-Control')){
            $reqCacheControl = \GuzzleHttp\Psr7\parse_header($request->getHeader('Cache-Control'));

            if(GuzzleCacheMiddleware::inArrayDeep($reqCacheControl, 'only-if-cached')){
                $onlyFromCache = true;
            }
            if(GuzzleCacheMiddleware::inArrayDeep($reqCacheControl, 'max-stale')){
                $staleResponse = true;
            }
            if($maxStale = (int)GuzzleCacheMiddleware::arrayKeyDeep($reqCacheControl, 'max-stale')){
                $maxStaleCache = $maxStale;
            }
            if($minFresh = (int)GuzzleCacheMiddleware::arrayKeyDeep($reqCacheControl, 'min-fresh')){
                $minFreshCache = $minFresh;
            }
        }

        // If cache => return new FulfilledPromise(...) with response -------------------------------------------------
        $cacheEntry = $this->cacheStrategy->fetch($request);

        if($cacheEntry instanceof CacheEntry){
            $body = $cacheEntry->getResponse()->getBody();
            if($body->tell() > 0){
                $body->rewind();
            }

            if(
                $cacheEntry->isFresh() &&
                ($minFreshCache === null || $cacheEntry->getStaleAge() + (int)$minFreshCache <= 0)
            ){
                // Cache HIT!
                return new FulfilledPromise(
                    static::addDebugHeader($cacheEntry->getResponse(), self::DEFAULT_CACHE_DEBUG_HEADER_HIT, $options)
                );
            }elseif(
                $staleResponse ||
                ($maxStaleCache !== null && $cacheEntry->getStaleAge() <= $maxStaleCache)
            ){
                // Staled cache!
                return new FulfilledPromise(
                    static::addDebugHeader($cacheEntry->getResponse(), self::DEFAULT_CACHE_DEBUG_HEADER_HIT, $options)
                );
            }elseif($cacheEntry->hasValidationInformation() && !$onlyFromCache){
                // Re-validation header
                $request = static::getRequestWithReValidationHeader($request, $cacheEntry);

                if($cacheEntry->staleWhileValidate()){
                    static::addReValidationRequest($request, $this->cacheStrategy, $cacheEntry);

                    return new FulfilledPromise(
                        static::addDebugHeader($cacheEntry->getResponse(), self::DEFAULT_CACHE_DEBUG_HEADER_STALE, $options)
                    );
                }
            }
        }else{
            $cacheEntry = null;
        }

        // explicit asking of a cached response -> 504 ----------------------------------------------------------------
        if(is_null($cacheEntry) && $onlyFromCache){
            return new FulfilledPromise(
                new Response(504)
            );
        }

        return $next($request, $options)->then(
            $this->onFulfilled($request, $cacheEntry, $options),
            $this->onRejected($cacheEntry, $options)
        );
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
                $response = static::addDebugHeader($response, self::DEFAULT_CACHE_DEBUG_HEADER_HIT, $options);

                /**
                 * Merge headers of the "304 Not Modified" and the cache entry
                 * @var string $headerName
                 * @var string[] $headerValue
                 */
                foreach($cacheEntry->getOriginalResponse()->getHeaders() as $headerName => $headerValue){
                    if(!$response->hasHeader($headerName) && $headerName !== $options['cache_debug_header']){
                        $response = $response->withHeader($headerName, $headerValue);
                    }
                }

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
        return function ($reason) use ($cacheEntry, $options){

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
     * @param RequestInterface $request
     * @param CacheStrategyInterface $cacheStrategy
     * @param CacheEntry $cacheEntry
     * @return bool if added
     */
    protected function addReValidationRequest(RequestInterface $request, CacheStrategyInterface &$cacheStrategy, CacheEntry $cacheEntry) : bool {
        // Add the promise for revalidate
        if(!is_null($this->client)){
            $request = $request->withHeader(self::DEFAULT_CACHE_RE_VALIDATION_HEADER, '1');
            $this->waitingRevalidate[] = $this->client
                ->sendAsync($request)
                ->then(function(ResponseInterface $response) use ($request, &$cacheStrategy, $cacheEntry){
                    $update = false;
                    if($response->getStatusCode() == 304){
                        // Not modified => cache entry is re-validate
                        $response = $response->withStatus($cacheEntry->getResponse()->getStatusCode());
                        $response = $response->withBody($cacheEntry->getResponse()->getBody());
                        // Merge headers of the "304 Not Modified" and the cache entry
                        foreach($cacheEntry->getResponse()->getHeaders() as $headerName => $headerValue){
                            if(!$response->hasHeader($headerName)){
                                $response = $response->withHeader($headerName, $headerValue);
                            }
                        }
                        $update = true;
                    }
                    static::addToCache($cacheStrategy, $request, $response, $update);
                });
            return true;
        }
        return false;
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
     * @param RequestInterface $request
     * @param CacheEntry $cacheEntry
     * @return RequestInterface
     */
    protected static function getRequestWithReValidationHeader(RequestInterface $request, CacheEntry $cacheEntry) : RequestInterface {
        if($cacheEntry->getResponse()->hasHeader('Last-Modified')){
            $request = $request->withHeader(
                'If-Modified-Since',
                $cacheEntry->getResponse()->getHeader('Last-Modified')
            );
        }
        if($cacheEntry->getResponse()->hasHeader('Etag')){
            $request = $request->withHeader(
                'If-None-Match',
                $cacheEntry->getResponse()->getHeader('Etag')
            );
        }
        return $request;
    }

    /**
     * check if $search value exists in "deep" nested Array
     * @param array $array
     * @param string $search
     * @return bool
     */
    public static function inArrayDeep(array $array, string $search) : bool {
        $found = false;
        array_walk($array, function($value, /** @noinspection PhpUnusedParameterInspection */
                                    $key, $search) use (&$found) {
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
        array_walk($array, function($value, /** @noinspection PhpUnusedParameterInspection */
                                    $key, $searchKey) use (&$found) {
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