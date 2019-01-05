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
     * error message for legacy endpoints
     */
    const ERROR_RESOURCE_LEGACY                 = 'Resource has been marked as legacy';

    /**
     * error message for deprecated endpoints
     */
    const ERROR_RESOURCE_DEPRECATED             = 'Resource has been marked as deprecated';

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'is_loggable_callback' => true,
        'log_callback' => false
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

        return $next($request, $options)
            ->then(
                $this->onFulfilled($request, $options)
            );
    }

    /**
     * No exceptions were thrown during processing
     *
     * @param RequestInterface $request
     * @param array $options
     * @return \Closure
     */
    protected function onFulfilled(RequestInterface $request, array $options) : \Closure {
        return function (ResponseInterface $response) use ($request, $options) {

            // check response for "warning" headers
            if(!empty($value = $response->getHeaderLine('warning'))){
                // check header value for 199 code
                if(preg_match('/199/i', $value)){
                    // "legacy" warning found in response headers
                    if(is_callable($loggable = $options['is_loggable_callback']) ? $loggable('legacy', $request, $response) : (bool)$loggable){
                        // warning for legacy endpoint should be logged
                        if(is_callable($log = $options['log_callback'])){
                            $log('legacy', $value ? : self::ERROR_RESOURCE_LEGACY, [
                                'url' => $request->getUri()->__toString()
                            ]);
                        }
                    }
                }

                // check header value for 299 code
                if(preg_match('/299/i', $value)){
                    // "deprecated" warning found in response headers
                    if(is_callable($loggable = $options['is_loggable_callback']) ? $loggable('deprecated', $request, $response) : (bool)$loggable){
                        // warning for deprecated endpoint should be logged
                        if(is_callable($log = $options['log_callback'])){
                            $log('deprecated', $value ? : self::ERROR_RESOURCE_DEPRECATED, [
                                'url' => $request->getUri()->__toString()
                            ]);
                        }
                    }
                }
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