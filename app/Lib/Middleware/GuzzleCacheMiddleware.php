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
    const HTTP_METHODS              = ['GET'];

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
        'cache_http_methods'        => self::HTTP_METHODS,
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