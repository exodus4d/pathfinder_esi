<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 04.01.2019
 * Time: 20:02
 */

namespace Exodus4D\ESI\Lib\Middleware;

use Exodus4D\ESI\Lib\Stream\JsonStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleJsonMiddleware {

    /**
     * default for: global enable this middleware
     */
    const DEFAULT_JSON_ENABLED          = true;

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'json_enabled'                  => self::DEFAULT_JSON_ENABLED
    ];

    /**
     * @var callable
     */
    private $nextHandler;

    /**
     * GuzzleJsonMiddleware constructor.
     * @param callable $nextHandler
     * @param array $defaultOptions
     */
    public function __construct(callable $nextHandler, array $defaultOptions = []){
        $this->nextHandler = $nextHandler;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);
    }

    /**
     * add "JSON support" for request
     * -> add "Accept" header for requests
     * -> wrap response in response with JsonStream body
     * @param RequestInterface $request
     * @param array $options
     * @return mixed
     */
    public function __invoke(RequestInterface $request, array $options){
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        // set "Accept" header json
        if($options['json_enabled']){
            $request = $request->withHeader('Accept', 'application/json');
        }

        return $next($request, $options)->then(
            $this->onFulfilled($request, $options)
        );
    }

    /**
     * No exceptions were thrown during processing
     * @param RequestInterface $request
     * @param array $options
     * @return \Closure
     */
    protected function onFulfilled(RequestInterface $request, array $options) : \Closure{
        return function (ResponseInterface $response) use ($request, $options){
            // decode Json response body
            if($options['json_enabled']){
                $jsonStream = new JsonStream($response->getBody());
                $response = $response->withBody($jsonStream);
            }
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