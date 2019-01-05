<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 04.01.2019
 * Time: 18:34
 */

namespace Exodus4D\ESI\Lib\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleCcpErrorLimitMiddleware {

    /**
     * cache key prefix for error limits
     */
    const CACHE_KEY_PREFIX_ERROR_LIMIT          = 'CACHED_ERROR_LIMIT_';

    /**
     * log error when this error count is reached for a single API endpoint in the current error window
     */
    const ERROR_COUNT_MAX_URL                   = 30;

    /**
     * log error if less then this errors remain in current error window (all endpoints)
     */
    const ERROR_COUNT_REMAIN_TOTAL              = 10;

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'set_cache_value'           => null,
        'get_cache_value'           => null,
        'error_count_max_url'       => self::ERROR_COUNT_MAX_URL,
        'error_count_remain_total'  => self::ERROR_COUNT_REMAIN_TOTAL
    ];

    /**
     * @var callable
     */
    private $nextHandler;

    /**
     * GuzzleCcpErrorLimitMiddleware constructor.
     * @param callable $nextHandler
     * @param array $defaultOptions
     */
    public function __construct(callable $nextHandler, array $defaultOptions = []){
        $this->nextHandler = $nextHandler;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);
    }

    /**
     * check error limits for requested URL (ESI specific response headers)
     * @see https://developers.eveonline.com/blog/article/esi-error-limits-go-live
     * @param RequestInterface $request
     * @param array $options
     * @return mixed
     */
    public function __invoke(RequestInterface $request, array $options){

        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        return $next($request, $options)->then(
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
    protected function onFulfilled(RequestInterface $request, array $options) : \Closure{
        return function (ResponseInterface $response) use ($request, $options) {
            var_dump('onFullFilled() LIMIT ');
            var_dump($response->getHeaders());
            var_dump($response->getStatusCode());
            var_dump($response->getHeaderLine('x-esi-error-limit-reset'));
            $statusCode = $response->getStatusCode();

            // client or server error responses are relevant for error limits
            if($statusCode >= 400 && $statusCode <= 599){
                // check for existing x-esi-error headers
                if($response->hasHeader('x-esi-error-limit-reset')){
                    $esiErrorLimitReset = (int)$response->getHeaderLine('x-esi-error-limit-reset');

                    // block further api calls for this URL until error limit is reset/clear
                    $blockUrl = false;
                    var_dump('onFulfilled() ERRORLIMIT');
                    var_dump('$esiErrorLimitReset : ' . $esiErrorLimitReset);
                    var_dump('full url : ' . $request->getUri()->__toString());
                    // get "normalized" url path without params/placeholders
                    $urlPath = $this->getNormalizedUrlPath($request->getUri()->__toString());
                    $cacheKey = self::CACHE_KEY_PREFIX_ERROR_LIMIT . $urlPath;

                    var_dump('$cacheKey : ' . $cacheKey);
                    $esiErrorRate = [];
                    if(is_callable($getCacheValue = $options['get_cache_value'])){
                        $esiErrorRate = $getCacheValue($cacheKey);
                    }

                    // increase error count for this $url
                    $errorCount = (int)$esiErrorRate['count'] + 1;
                    $esiErrorRate['count'] = $errorCount;

                    // sort by error count desc
                    //uasort($esiErrorRate, function($a, $b) {
                    //    return $b['count'] <=> $a['count'];
                    //});

                    if($response->hasHeader('x-esi-error-limited')){
                        // request url is blocked until new error limit becomes reset
                        // -> this should never happen

                        // todo log blocked
                    }

                    if($response->hasHeader('x-esi-error-limit-remain')){
                        // remaining errors left until reset/clear
                        $esiErrorLimitRemain = (int)$response->getHeaderLine('x-esi-error-limit-remain');

                        if(
                            $errorCount > (int)$options['error_count_max_url'] ||
                            $esiErrorLimitRemain < (int)$options['error_count_remain_total']
                        ){
                            $blockUrl = true;

                            // todo log limit critical
                        }
                    }

                    if($blockUrl){
                        // to many error, block uri until error limit reset
                        $esiErrorRate['blocked'] = true;
                    }
var_dump($blockUrl);
var_dump('$esiErrorRate ');
var_dump($esiErrorRate);
                    if(is_callable($setCacheValue = $options['set_cache_value'])){
                        $setCacheValue($cacheKey, $esiErrorRate, $esiErrorLimitReset);
                    }
                }
            }


            return $response;
        };
    }

    /**
     * convert $url into normalized URL for cache key
     * @param string $url
     * @return string
     */
    protected function getNormalizedUrlPath(string $url) : string {
        return preg_replace('/\//', '_', parse_url(strtok(preg_replace('/\/(\d+)\//', '/{x}/', $url), '?'), PHP_URL_PATH));
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