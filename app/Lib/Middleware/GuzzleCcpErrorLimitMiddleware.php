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
     * default for: log error when this error count is reached for a single API endpoint in the current error window
     */
    const DEFAULT_ERROR_COUNT_MAX_URL           = 30;

    /**
     * default for: log error if less then this errors remain in current error window (all endpoints)
     */
    const DEFAULT_ERROR_COUNT_REMAIN_TOTAL      = 10;

    /**
     * default for: name for log file width "critical" error limit warnings
     */
    const DEFAULT_LOG_FILE_CRITICAL             = 'esi_resource_critical';

    /**
     * default for: name for log file with "blocked" errors
     */
    const DEFAULT_LOG_FILE_BLOCKED              = 'esi_resource_blocked';
    /**
     * error message for endpoints that hit "critical" amount of error responses
     */
    const ERROR_LIMIT_CRITICAL                  = 'Error rate reached critical amount';

    /**
     * error message for blocked endpoints
     */
    const ERROR_LIMIT_EXCEEDED                  = 'Error rate limit exceeded! API endpoint blocked';

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'set_cache_value'           => null,
        'get_cache_value'           => null,
        'log_callback'              => null,
        'error_count_max_url'       => self::DEFAULT_ERROR_COUNT_MAX_URL,
        'error_count_remain_total'  => self::DEFAULT_ERROR_COUNT_REMAIN_TOTAL,
        'log_file_critical'         => self::DEFAULT_LOG_FILE_CRITICAL,
        'log_file_blocked'          => self::DEFAULT_LOG_FILE_BLOCKED
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
     * @param RequestInterface $request
     * @param array $options
     * @return \Closure
     */
    protected function onFulfilled(RequestInterface $request, array $options) : \Closure{
        return function (ResponseInterface $response) use ($request, $options) {
            $statusCode = $response->getStatusCode();

            // client or server error responses are relevant for error limits
            // check for existing x-esi-error headers
            if(
                $statusCode >= 400 && $statusCode <= 599 &&
                $response->hasHeader('x-esi-error-limit-reset')
            ){
                $esiErrorLimitReset = (int)$response->getHeaderLine('x-esi-error-limit-reset');

                // block further api calls for this URL until error limit is reset/clear
                $blockUrl = false;

                // get "normalized" url path without params/placeholders
                $urlPath = $this->getNormalizedUrlPath($request->getUri()->__toString());
                $cacheKey = self::CACHE_KEY_PREFIX_ERROR_LIMIT . $urlPath;

                $esiErrorRate = [];
                if(is_callable($getCacheValue = $options['get_cache_value'])){
                    $esiErrorRate = $getCacheValue($cacheKey);
                }

                // increase error count for this $url
                $errorCount = (int)$esiErrorRate['count'] + 1;
                $esiErrorRate['count'] = $errorCount;

                if($response->hasHeader('x-esi-error-limited')){
                    // request url is blocked until new error limit becomes reset
                    // -> this should never happen

                    if(is_callable($log = $options['log_callback'])){
                        $logData = [
                            'url'           => $request->getUri()->__toString(),
                            'errorCount'    => $errorCount,
                            'esiLimitReset' => $esiErrorLimitReset
                        ];

                        $log($options['log_file_blocked'], 'critical', self::ERROR_LIMIT_EXCEEDED, $logData, 'danger');
                    }
                }

                if($response->hasHeader('x-esi-error-limit-remain')){
                    // remaining errors left until reset/clear
                    $esiErrorLimitRemain = (int)$response->getHeaderLine('x-esi-error-limit-remain');

                    if(
                        $errorCount > (int)$options['error_count_max_url'] ||
                        $esiErrorLimitRemain < (int)$options['error_count_remain_total']
                    ){
                        $blockUrl = true;

                        // log critical limit reached
                        if(is_callable($log = $options['log_callback'])){
                            $logData = [
                                'url'               => $request->getUri()->__toString(),
                                'errorCount'        => $errorCount,
                                'esiLimitReset'     => $esiErrorLimitReset,
                                'esiLimitRemain'    => $esiErrorLimitRemain
                            ];

                            $log($options['log_file_critical'], 'warning', self::ERROR_LIMIT_CRITICAL, $logData, 'warning');
                        }
                    }
                }

                if($blockUrl){
                    // to many error, block uri until error limit reset
                    $esiErrorRate['blocked'] = true;
                }

                if(is_callable($setCacheValue = $options['set_cache_value'])){
                    $setCacheValue($cacheKey, $esiErrorRate, $esiErrorLimitReset);
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
        return preg_replace('/\//', '_', parse_url(strtok(preg_replace('/\/(\d+)\//', '/x/', $url), '?'), PHP_URL_PATH));
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