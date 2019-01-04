<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 31.12.2018
 * Time: 18:41
 */

namespace Exodus4D\ESI;

use Exodus4D\ESI\Lib\Middleware\GuzzleCcpLoggingMiddleware;
use GuzzleHttp\Middleware;
use lib\Config;
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


        // log "legacy" endpoints
        $middleware['resource_legacy'] = GuzzleCcpLoggingMiddleware::factory($this->getCcpLoggingMiddlewareConfig());

        $middleware['test'] = Middleware::mapResponse(function(ResponseInterface $response){
            return $response->withHeader('warning', '199 - This endpoint has been updated.');
        });



/*
        $middleware['resource_legacy'] = Middleware::mapResponse(function(ResponseInterface $response) : ResponseInterface {
            $headerName = 'warning';
            var_dump('legacy.....');
            if(
                !empty($value = $response->getHeaderLine($headerName)) &&
                preg_match('/^199/i', $value) &&
                $this->isLoggable('legacy', $response->getUr)
            ){

            }
            var_dump($response->getHeaders());
            var_dump($response->getHeaderLine('X-Esi-Error-Limit-Remain'));
            var_dump($response->hasHeader('X-Esi-Error-Limit-Remain'));
            return $response;
        });
*/
        return $middleware;
    }

    protected function getCcpLoggingMiddlewareConfig() : array {
        return [
            'is_loggable_callback' => function(string $type, RequestInterface $request, ResponseInterface $response = null){
                $loggable = true;
                if(Config::inDownTimeRange() || !$this->isLoggableEndpoint($type, 'myURL')){
                    $loggable = false;
                }
                return $loggable;
            },
            'log_callback' => function(string $type, RequestInterface $request, ResponseInterface $response = null){
                var_dump('logg this request!');
                var_dump($request->getUri()->__toString());
            }
        ];
    }

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