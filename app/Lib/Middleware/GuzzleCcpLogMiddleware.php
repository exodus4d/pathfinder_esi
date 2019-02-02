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

/**
 * ESI endpoints that return warning headers (e.g. "resource_legacy", "resource_deprecated") will get logged
 *  To prevent big file I/O on these log files, errors get "throttled" and not all of them get logged
 * Class GuzzleCcpLogMiddleware
 * @package Exodus4D\ESI\Lib\Middleware
 */
class GuzzleCcpLogMiddleware extends AbstractGuzzleMiddleware {

    /**
     * cache tag for legacy warnings limits
     */
    const CACHE_TAG_LEGACY_LIMIT        = 'LEGACY_LIMIT';

    /**
     * cache tag for deprecated warnings limits
     */
    const CACHE_TAG_DEPRECATED_LIMIT    = 'DEPRECATED_LIMIT';

    /**
     * default for: global enable this middleware
     */
    const DEFAULT_LOG_ENABLED           = true;

    /**
     * default for: Log first "2" errors that occur for an endpoint within "X" seconds
     * @see DEFAULT_LOG_LIMIT_COUNT_TTL
     */
    const DEFAULT_LOG_COUNT_MAX         = 2;

    /**
     * default for: logging limit
     */
    const DEFAULT_LOG_LIMIT_COUNT_TTL   = 60;

    /**
     * default for: callback function that checks a $request
     * -> can be used to "exclude" some requests from been logged (e.g. on expected downtime)
     */
    const DEFAULT_LOG_LOGGABLE_CALLBACK = null;

    /**
     * default for: callback function for logging
     */
    const DEFAULT_LOG_CALLBACK          = null;

    /**
     * default for: name for log file with endpoints marked as "legacy" in response Headers
     */
    const DEFAULT_LOG_FILE_LEGACY       = 'esi_resource_legacy';

    /**
     * default for: name for log file with endpoints marked as "deprecated" in response Headers
     */
    const DEFAULT_LOG_FILE_DEPRECATED   = 'esi_resource_deprecated';

    /**
     * error message for legacy endpoints
     */
    const ERROR_RESOURCE_LEGACY         = 'Resource has been marked as legacy';

    /**
     * error message for deprecated endpoints
     */
    const ERROR_RESOURCE_DEPRECATED     = 'Resource has been marked as deprecated';

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'ccp_log_enabled'               => self::DEFAULT_LOG_ENABLED,
        'ccp_log_count_max'             => self::DEFAULT_LOG_COUNT_MAX,
        'ccp_log_limit_count_ttl'       => self::DEFAULT_LOG_LIMIT_COUNT_TTL,
        'ccp_log_loggable_callback'     => self::DEFAULT_LOG_LOGGABLE_CALLBACK,
        'ccp_log_callback'              => self::DEFAULT_LOG_CALLBACK,
        'ccp_log_file_legacy'           => self::DEFAULT_LOG_FILE_LEGACY,
        'ccp_log_file_deprecated'       => self::DEFAULT_LOG_FILE_DEPRECATED
    ];

    /**
     * @var callable
     */
    private $nextHandler;

    /**
     * GuzzleCcpLogMiddleware constructor.
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

        if(!$options['ccp_log_enabled']){
            // middleware disabled -> skip
            return $next($request, $options);
        }

        parent::__invoke($request, $options);

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
                    if(is_callable($loggable = $options['ccp_log_loggable_callback']) ? $loggable($request) : (bool)$loggable){
                        // warning for legacy endpoint -> check log limit (throttle)
                        if($this->isLoggableRequest($request, self::CACHE_TAG_LEGACY_LIMIT, $options)){
                            if(is_callable($log = $options['ccp_log_callback'])){
                                $logData = [
                                    'url' => $request->getUri()->__toString()
                                ];

                                $log($options['ccp_log_file_legacy'], 'info', $value ? : self::ERROR_RESOURCE_LEGACY, $logData, 'information');
                            }
                        }
                    }
                }

                // check header value for 299 code
                if(preg_match('/299/i', $value)){
                    // "deprecated" warning found in response headers
                    if(is_callable($loggable = $options['ccp_log_loggable_callback']) ? $loggable($request) : (bool)$loggable){
                        // warning for deprecated -> check log limit (throttle)
                        if($this->isLoggableRequest($request, self::CACHE_TAG_DEPRECATED_LIMIT, $options)){
                            if(is_callable($log = $options['ccp_log_callback'])){
                                $logData = [
                                    'url' => $request->getUri()->__toString()
                                ];

                                $log($options['ccp_log_file_deprecated'], 'warning', $value ? : self::ERROR_RESOURCE_DEPRECATED, $logData, 'warning');
                            }
                        }
                    }
                }
            }

            return $response;
        };
    }

    /**
     * checks whether a request should be logged or not
     * -> if a request url is already logged with a certain $type,
     *      it will not get logged the next time until self::DEFAULT_LOG_LIMIT_COUNT_TTL
     *      expires (this helps to reduce log file I/O)
     * @param RequestInterface $request
     * @param string $tag
     * @param array $options
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function isLoggableRequest(RequestInterface $request, string $tag, array $options) : bool {
        $loggable = false;

        $cacheKey = $this->cacheKeyFromRequestUrl($request, $tag);
        $cacheItem = $this->cache()->getItem($cacheKey);
        $legacyLimit = (array)$cacheItem->get();
        $count = (int)$legacyLimit['count']++;

        if($count < $options['ccp_log_count_max']){
            // loggable error count exceeded..
            $loggable = true;

            if(!$cacheItem->isHit()){
                $cacheItem->expiresAfter($options['ccp_log_limit_count_ttl']);
            }
            $cacheItem->set($legacyLimit);
            $this->cache()->save($cacheItem);
        }

        return $loggable;
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