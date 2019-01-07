<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 07.01.2019
 * Time: 20:02
 */

namespace Exodus4D\ESI\Lib\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleCacheMiddleware {

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [

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

    public function __invoke(RequestInterface $request, array $options){
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        return $next($request, $options)->then(
            $this->onFulfilled($request, $options)
        );
    }

    protected function onFulfilled(RequestInterface $request, array $options) : \Closure{
        return function (ResponseInterface $response) use ($request, $options) {
            var_dump('onFullFilled() Cache ');

            return $response;
        };
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