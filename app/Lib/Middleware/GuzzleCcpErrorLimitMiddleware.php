<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 04.01.2019
 * Time: 18:34
 */

namespace Exodus4D\ESI\Lib\Middleware;


use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleCcpErrorLimitMiddleware extends AbstractGuzzleMiddleware {

    /**
     * cache tag for error limits
     */
    const CACHE_TAG_ERROR_LIMIT             = 'ERROR_LIMIT';

    /**
     * default for: global enable this middleware
     */
    const DEFAULT_LIMIT_ENABLED             = true;

    /**
     * default for: HTTP status response code for requests to "blocked" endpoints
     * @see https://esi.evetech.net status codes
     */
    const DEFAULT_LIMIT_HTTP_STATUS         = 420;

    /**
     * default for: HTTP status phrase for DEFAULT_LIMIT_HTTP_STATUS code
     */
    const DEFAULT_LIMIT_HTTP_PHRASE         = 'Error limited';

    /**
     * default for: log error for endpoint if error count exceeds limit in the current error window
     * -> CCP blocks endpoint           -> after 100 error responses within 60s
     *    we log warnings for endpoints -> after  80 error responses within 60s
     */
    const DEFAULT_LIMIT_COUNT_MAX           = 80;

    /**
     * default for: log error and block endpoint if
     * -> less then 10 errors remain left in current error window
     */
    const DEFAULT_LIMIT_COUNT_REMAIN        = 10;

    /**
     * default for: callback function for logging
     */
    const DEFAULT_LOG_CALLBACK              = null;

    /**
     * default for: name for log file width "critical" error limit warnings
     */
    const DEFAULT_LOG_FILE_CRITICAL         = 'esi_resource_critical';

    /**
     * default for: name for log file with "blocked" errors
     */
    const DEFAULT_LOG_FILE_BLOCKED          = 'esi_resource_blocked';

    /**
     * error message for response HTTP header "x-esi-error-limited" - Blocked endpoint
     */
    const ERROR_RESPONSE_BLOCKED            = "Response error: Blocked for (%ss)";

    /**
     * error message for response HTTP header "x-esi-error-limit-remain" that:
     * -> falls below "critical" DEFAULT_LIMIT_COUNT_REMAIN limit
     */
    const ERROR_RESPONSE_LIMIT_BELOW        = 'Response error: [%2s < %2s] Rate falls below critical limit. Blocked for (%ss)';

    /**
     * error message for response HTTP header "x-esi-error-limit-remain" that:
     * -> exceed "critical" DEFAULT_LIMIT_COUNT_MAX limit
     */
    const ERROR_RESPONSE_LIMIT_ABOVE        = 'Response error: [%2s > %2s] Rate exceeded critical limit. Blocked for (%ss)';

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'ccp_limit_enabled'                 => self::DEFAULT_LIMIT_ENABLED,
        'ccp_limit_http_status'             => self::DEFAULT_LIMIT_HTTP_STATUS,
        'ccp_limit_http_phrase'             => self::DEFAULT_LIMIT_HTTP_PHRASE,
        'ccp_limit_error_count_max'         => self::DEFAULT_LIMIT_COUNT_MAX,
        'ccp_limit_error_count_remain'      => self::DEFAULT_LIMIT_COUNT_REMAIN,
        'ccp_limit_log_callback'            => self::DEFAULT_LOG_CALLBACK,
        'ccp_limit_log_file_critical'       => self::DEFAULT_LOG_FILE_CRITICAL,
        'ccp_limit_log_file_blocked'        => self::DEFAULT_LOG_FILE_BLOCKED
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
     * @throws \Psr\Cache\InvalidArgumentException
     * @return mixed
     */
    public function __invoke(RequestInterface $request, array $options){
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        if(!$options['ccp_limit_enabled']){
            // middleware disabled -> skip
            return $next($request, $options);
        }

        parent::__invoke($request, $options);

        // check if Request Endpoint is blocked
        if(!is_null($blockedUntil = $this->isBlockedUntil($request))){

            return new FulfilledPromise(
                new Response(
                    $options['ccp_limit_http_status'],
                    [],
                    null,
                    '1.1',
                    $options['ccp_limit_http_phrase']
                )
            );
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
        return function (ResponseInterface $response) use ($request, $options) {
            $statusCode = $response->getStatusCode();

            // client or server error responses are relevant for error limits
            // check for existing x-esi-error headers
            if(
                $statusCode >= 400 && $statusCode <= 599 &&
                $response->hasHeader('x-esi-error-limit-reset')
            ){
                $esiErrorLimitReset = (int)$response->getHeaderLine('x-esi-error-limit-reset');

                // get cache key from request URL
                $cacheKey = $this->cacheKeyFromRequestUrl($request, self::CACHE_TAG_ERROR_LIMIT);
                $cacheItem = $this->cache()->getItem($cacheKey);
                $esiErrorRate = (array)$cacheItem->get();

                // increase error count for this $url
                $errorCount = (int)$esiErrorRate['count'] + 1;
                $esiErrorRate['count'] = $errorCount;

                // default log data
                $action = $level = $tag = $message = '';
                $esiErrorLimitRemain = 0;
                $blockUrl = false;

                // check blocked HTTP Header --------------------------------------------------------------------------
                if($response->hasHeader('x-esi-error-limited')){
                    // request url is blocked until new error limit becomes reset
                    // -> this should never happen
                    $blockUrl = true;

                    $action     = $options['ccp_limit_log_file_blocked'];
                    $level      = 'alert';
                    $tag        = 'danger';
                    $message    = sprintf(self::ERROR_RESPONSE_BLOCKED, $esiErrorLimitReset);

                    // the expected response HTTP status 420 is "unofficial", add proper phrase
                    $response = $response->withStatus($response->getStatusCode(), $options['ccp_limit_http_phrase']);
                }

                // check limits HTTP Header ---------------------------------------------------------------------------
                if( !$blockUrl && $response->hasHeader('x-esi-error-limit-remain')){
                    // remaining errors left until reset/clear
                    $esiErrorLimitRemain = (int)$response->getHeaderLine('x-esi-error-limit-remain');

                    $belowCriticalLimit = $esiErrorLimitRemain < (int)$options['ccp_limit_error_count_remain'];
                    $aboveCriticalLimit = $errorCount > (int)$options['ccp_limit_error_count_max'];

                    if($belowCriticalLimit){
                        // ... falls below critical limit
                        // requests to this endpoint might be blocked soon!
                        // -> pre-block future requests to this endpoint on our side
                        //    this should help to block requests for e.g. specific user
                        $blockUrl = true;

                        $action     = $options['ccp_limit_log_file_blocked'];
                        $level      = 'alert';
                        $tag        = 'danger';
                        $message    = sprintf(self::ERROR_RESPONSE_LIMIT_BELOW,
                            $esiErrorLimitRemain,
                            $options['ccp_limit_error_count_remain'],
                            $esiErrorLimitReset
                        );
                    }elseif($aboveCriticalLimit){
                        // ... above critical limit

                        $action     = $options['ccp_limit_log_file_critical'];
                        $level      = 'critical';
                        $tag        = 'warning';
                        $message    = sprintf(self::ERROR_RESPONSE_LIMIT_ABOVE,
                            $errorCount,
                            $options['ccp_limit_error_count_max'],
                            $esiErrorLimitReset
                        );
                    }
                }

                // log ------------------------------------------------------------------------------------------------
                if(
                    !empty($action) &&
                    is_callable($log = $options['ccp_limit_log_callback'])
                ){
                    $logData = [
                        'url'               => $request->getUri()->__toString(),
                        'errorCount'        => $errorCount,
                        'esiLimitReset'     => $esiErrorLimitReset,
                        'esiLimitRemain'    => $esiErrorLimitRemain
                    ];

                    $log($action, $level, $message, $logData, $tag);
                }

                // update cache ---------------------------------------------------------------------------------------
                if($blockUrl){
                    // to many error, block uri until error limit reset
                    $esiErrorRate['blocked'] = true;
                }

                $expiresAt = new \DateTime('+' . $esiErrorLimitReset . 'seconds');

                // add expire time to cache item
                // -> used to get left ttl for item
                //    and/or for throttle write logs
                $esiErrorRate['expiresAt']  = $expiresAt;

                $cacheItem->set($esiErrorRate);
                $cacheItem->expiresAt($expiresAt);
                $this->cache()->save($cacheItem);
            }

            return $response;
        };
    }

    /**
     * @param RequestInterface $request
     * @return \DateTime|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function isBlockedUntil(RequestInterface $request) : ?\DateTime {
        $blockedUntil = null;

        $cacheKey = $this->cacheKeyFromRequestUrl($request, self::CACHE_TAG_ERROR_LIMIT);
        $cacheItem = $this->cache()->getItem($cacheKey);
        if($cacheItem->isHit()){
            // check if it is blocked
            $esiErrorRate = (array)$cacheItem->get();
            if($esiErrorRate['blocked']){
                $blockedUntil = $esiErrorRate['expiresAt'];
            }
        }

        return $blockedUntil;
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