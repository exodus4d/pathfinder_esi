<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 01.01.2019
 * Time: 22:39
 */

namespace Exodus4D\ESI\Lib\Middleware;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleCcpLoggingMiddleware {

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'is_loggable_callback' => true
    ];

    /**
     * @var callable
     */
    private $nextHandler;

    /**
     * GuzzleCcpLoggingMiddleware constructor.
     * @param callable $nextHandler
     * @param array $defaultOptions
     */
    public function __construct(callable $nextHandler, array $defaultOptions = []){
        $this->nextHandler = $nextHandler;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);
    }

    /**
     * log warnings for some ESI specific response headers
     * @param RequestInterface $request
     * @param array $options
     * @return mixed
     */
    public function __invoke(RequestInterface $request, array $options){

        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;
        return $next($request, $options)->then(function(ResponseInterface $response) use ($request, $options){
var_dump( 'loggingMiddleWareTest...');
var_dump( $response->getHeaderLine('warning'));
            // check response for "warning" headers
            if(!empty($value = $response->getHeaderLine('warning'))){

                if(preg_match('/199/i', $value)){
                    // "legacy" warning found in response headers
                    if(is_callable($loggable = $options['is_loggable_callback']) ? $loggable($request, $response) : (bool)$loggable){
                        // warning for legacy endpoint should be logged
                    }
                }
            }

            return $response->withAddedHeader($this->headerName, $request->getUri()->__toString());
        });
    }

    /**
     * @param array $defaultOptions
     * @return \Closure
     */
    public static function factory(array $defaultOptions = []){
        return function(callable $handler) use ($defaultOptions){
            return new static($handler, $defaultOptions);
        };
    }
}