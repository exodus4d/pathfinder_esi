<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 07.01.2019
 * Time: 20:02
 */

namespace Exodus4D\ESI\Lib\Middleware;

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleCacheMiddleware {

    /**
     * default for: cacheable HTTP methods
     */
    const DEFAULT_HTTP_METHODS      = ['GET'];

    /**
     * default for: cacheable HTTP response status codes
     */
    const DEFAULT_ON_STATUS         = [200];

    /**
     * default for: enable debug HTTP headers
     */
    const DEFAULT_DEBUG             = false;

    /**
     * default for: debug HTTP Header name
     */
    const DEFAULT_DEBUG_HEADER      = 'X-Guzzle-Cache';

    /**
     * default for: debug HTTP Header value for cached responses
     */
    const DEFAULT_DEBUG_HEADER_HIT  = 'HIT';

    /**
     * default for: debug HTTP Header value for not cached responses
     */
    const DEFAULT_DEBUG_HEADER_MISS = 'MISS';

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'cache_http_methods'        => self::DEFAULT_HTTP_METHODS,
        'cache_on_status'           => self::DEFAULT_ON_STATUS,
        'cache_debug'               => self::DEFAULT_DEBUG,
        'cache_debug_header'        => self::DEFAULT_DEBUG_HEADER
    ];

    /**
     * @var callable
     */
    private $nextHandler;

    /**
     * GuzzleCacheMiddleware constructor.
     * @param callable $nextHandler
     * @param array $defaultOptions
     */
    public function __construct(callable $nextHandler, array $defaultOptions = []){
        $this->nextHandler = $nextHandler;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);
    }

    /**
     * cache response data for successful requests
     * -> load data from cache rather than sending the request
     * @param RequestInterface $request
     * @param array $options
     * @return FulfilledPromise
     */
    public function __invoke(RequestInterface $request, array $options){
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        if(!$response = $this->fetch($request)){
            // response not cached
            return $next($request, $options)->then(
                $this->onFulfilled($request, $options)
            );
        }

        $response = $this->addDebugHeader($response, self::DEFAULT_DEBUG_HEADER_HIT, $options);

        return new FulfilledPromise($response);
    }

    /**
     * No exceptions were thrown during processing
     * @param RequestInterface $request
     * @param array $options
     * @return \Closure
     */
    protected function onFulfilled(RequestInterface $request, array $options) : \Closure {
        return function (ResponseInterface $response) use ($request, $options) {
            var_dump('onFullFilled() Cache ');
            $this->save($request, $response, $options);

            $response = $this->addDebugHeader($response, self::DEFAULT_DEBUG_HEADER_MISS, $options);

            return $response;
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
    protected function save(RequestInterface $request, ResponseInterface $response, array $options) : void {
        $cacheInfo = $this->getCacheInfo($request, $response, $options);

        /*
        $statusCode = $response->getStatusCode();
        if(in_array($statusCode, (array)$options['cache_on_status'])){
            var_dump(\GuzzleHttp\Psr7\parse_header($response->getHeader('Cache-Control')));
            var_dump(\GuzzleHttp\Psr7\parse_header($response->getHeader('Cache-Controlff')));
            var_dump(\GuzzleHttp\Psr7\parse_header($response->getHeader('Strict-Transport-Security')));
        }*/
    }

    protected function getCacheInfo(RequestInterface $request, ResponseInterface $response, array $options) : array {
        $info = [];

        if(
            in_array($request->getMethod(), (array)$options['cache_http_methods']) &&
            in_array($response->getStatusCode(), (array)$options['cache_on_status'])
        ){
            $cacheControl = $this->getCacheControl($response);
            var_dump('22: $cacheControl');
            var_dump($cacheControl);
        }

        return $info;
    }

    protected function getCacheControl(ResponseInterface $response) : string {
        $cacheControl = '';
        if($response->hasHeader('Cache-Control')){
            $cacheControlHeader = \GuzzleHttp\Psr7\parse_header($response->getHeader('Cache-Control'));

            $test = self::inArrayRecursive($cacheControlHeader, 'public');
            var_dump('11: $test');
            var_dump($test);
        }
        return $cacheControl;
    }

    /**
     * add debug HTTP header to $response
     * -> Header can be checked whether a $response was cached or not
     * @param ResponseInterface $response
     * @param string $value
     * @param array $options
     * @return ResponseInterface
     */
    protected function addDebugHeader(ResponseInterface $response, string $value, array $options) : ResponseInterface {
        return $options['cache_debug'] ? $response->withHeader($options['cache_debug_header'], $value) : $response;
    }

    public static function inArrayRecursive(array $array, string $search) : bool {
        $found = false;
        array_walk($array, function($value, $key, $search) use (&$found) {
            if(is_array($value) && in_array($search, $value)){
                $found = true;
            }
        }, $search);
        return $found;
    }

    /**
     * @param array $defaultOptions
     * @return \Closure
     */
    public static function factory(array $defaultOptions = []) : \Closure {
        return function(callable $handler) use ($defaultOptions){
            return new static($handler, $defaultOptions);
        };
    }
}