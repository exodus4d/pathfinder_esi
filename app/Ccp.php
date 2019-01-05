<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 31.12.2018
 * Time: 18:41
 */

namespace Exodus4D\ESI;

use Exodus4D\ESI\Lib\Middleware\GuzzleCcpErrorLimitMiddleware;
use Exodus4D\ESI\Lib\Middleware\GuzzleCcpLoggingMiddleware;
use lib\Config;
use lib\logging\LogInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Ccp extends Api {

    // loggable limits ================================================================================================
    // ESI endpoints that return warning headers (e.g. "resource_legacy", "resource_deprecated") will get logged
    // To prevent big file I/O on these log files, errors get "throttled" and not all of them get logged

    /**
     * Cache key for "loggable limits"
     */
    const CACHE_KEY_LOGGABLE_LIMIT              = 'CACHED_LOGGABLE_LIMIT';

    /**
     * Time interval used for error inspection (seconds)
     */
    const LOGGABLE_COUNT_INTERVAL               = 60;

    /**
     * Log first "2" errors that occur for an endpoint within "60" (LOGGABLE_COUNT_INTERVAL) seconds interval
     */
    const LOGGABLE_COUNT_MAX_URL                = 2;

    /**
     * add some middleware for all CCP related API calls
     * @return array
     */
    protected function getClientMiddleware(): array {
        $middleware = parent::getClientMiddleware();

        // check response headers for ESI error limits
        $middleware['ccp_error_limit'] = GuzzleCcpErrorLimitMiddleware::factory($this->getCcpErrorLimitMiddlewareConfig());

        // log "warning" headers from response -> "deprecated" or "legacy" endpoint request
        $middleware['ccp_resource_warning'] = GuzzleCcpLoggingMiddleware::factory($this->getCcpLoggingMiddlewareConfig());

        /*
        // test "ccp_resource_warning" middleware. Legacy endpoint
        $middleware['test_resource_legacy'] = Middleware::mapResponse(function(ResponseInterface $response){
            return $response->withHeader('warning', '199 - This endpoint has been updated.');
        });

        // test "ccp_resource_warning" middleware. Deprecated endpoint
        $middleware['test_resource_deprecated'] = Middleware::mapResponse(function(ResponseInterface $response){
            return $response->withHeader('warning', '299 - This endpoint is deprecated.');
        });

        // test "ccp_error_limit" middleware
        $middleware['test_error_limit'] = Middleware::mapResponse(function(ResponseInterface $response){
            return $response->withStatus(400)               // 4xx or 5xx response is important
                ->withHeader('X-Esi-Error-Limit-Reset', 50) // error window reset in s
                ->withHeader('X-Esi-Error-Limit-Remain', 8) // errors possible in current error window
                ->withHeader('X-Esi-Error-Limited', '');    // endpoint blocked
        });
        */

        return $middleware;
    }

    /**
     * get configuration for GuzzleCcpErrorLimitMiddleware Middleware
     * @return array
     */
    protected function getCcpErrorLimitMiddlewareConfig() : array {
        return [
            'set_cache_value' => function(string $key, array $value, int $ttl = 0){
                // clear existing cache key first -> otherwise f3 updates existing key and ignores new $ttl
                $f3 = \Base::instance();
                $f3->clear($key);
                $f3->set($key, $value, $ttl);
            },
            'get_cache_value' => function(string $key){
                return \Base::instance()->get($key);
            },
            'log_callback' => function(string $type, string $message, array $data){
                if(is_callable($newLog = $this->getNewLog())){
                    /**
                     * @var LogInterface $log
                     */
                    $log = $newLog('esi_resource_' . $type, 'warning');
                    $log->setMessage($message);
                    $log->setData($data);
                    $log->buffer();
                }
            }
        ];
    }

    /**
     * get configuration for GuzzleCcpLoggingMiddleware Middleware
     * @return array
     */
    protected function getCcpLoggingMiddlewareConfig() : array {
        return [
            'is_loggable_callback' => function(string $type, RequestInterface $request, ResponseInterface $response = null) : bool {
                $loggable = true;
                if(Config::inDownTimeRange() || !$this->isLoggableEndpoint($type, $request->getUri()->__toString())){
                    $loggable = false;
                }
                return $loggable;
            },
            'log_callback' => function(string $type, string $message, array $data){
                if(is_callable($newLog = $this->getNewLog())){
                    /**
                     * @var LogInterface $log
                     */
                    $log = $newLog('esi_resource_' . $type, 'warning');
                    $log->setMessage($message);
                    $log->setData($data);
                    $log->buffer();
                }
            }
        ];
    }

    /**
     * checks whether a request should be logged or not
     * -> if a request url is already logged with a certain $type,
     *      it will not get logged the next time until self::LOGGABLE_COUNT_INTERVAL
     *      expires (this helps to reduce log file I/O)
     * @param string $type
     * @param string $urlPath
     * @return bool
     */
    protected function isLoggableEndpoint(string $type, string $urlPath) : bool {
        $loggable = false;

        $f3 = \Base::instance();
        if(!$f3->exists(self::CACHE_KEY_LOGGABLE_LIMIT, $loggableLimit)){
            $loggableLimit = [];
        }

        // increase counter
        $count = (int)$loggableLimit[$urlPath][$type]['count']++;

        // check counter for given $urlPath
        if($count < self::LOGGABLE_COUNT_MAX_URL){
            // loggable error count exceeded...
            $loggable = true;
            $f3->set(self::CACHE_KEY_LOGGABLE_LIMIT, $loggableLimit, self::LOGGABLE_COUNT_INTERVAL);
        }

        return $loggable;
    }
}