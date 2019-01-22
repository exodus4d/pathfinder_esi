<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 31.12.2018
 * Time: 18:41
 */

namespace Exodus4D\ESI;

use Exodus4D\ESI\Lib\Middleware\GuzzleCcpErrorLimitMiddleware;
use Exodus4D\ESI\Lib\Middleware\GuzzleCcpLogMiddleware;
use GuzzleHttp\HandlerStack;

abstract class AbstractCcp extends AbstractApi {

    // loggable limits ================================================================================================
    // ESI endpoints that return warning headers (e.g. "resource_legacy", "resource_deprecated") will get logged
    // To prevent big file I/O on these log files, errors get "throttled" and not all of them get logged

    /**
     * see parent
     * @param HandlerStack $stack
     */
    protected function initStack(HandlerStack &$stack): void {
        parent::initStack($stack);

        // log "warning" headers from response -> "deprecated" or "legacy" endpoint request
        $stack->after('cache', GuzzleCcpLogMiddleware::factory($this->getCcpLogMiddlewareConfig()), 'ccp_log');

        // check response headers for ESI error limits
        $stack->after('retry', GuzzleCcpErrorLimitMiddleware::factory($this->getCcpErrorLimitMiddlewareConfig()), 'ccp_error_limit');

        /*
        // test "ccp_log" middleware. Legacy endpoint
        $stack->after('ccp_log', \GuzzleHttp\Middleware::mapResponse(function(\Psr\Http\Message\ResponseInterface $response){
            return $response->withHeader('warning', '199 - This endpoint has been updated.');
        }), 'test_ccp_log_legacy');

        // test "ccp_log" middleware. Deprecated endpoint
        $stack->after('ccp_log', \GuzzleHttp\Middleware::mapResponse(function(\Psr\Http\Message\ResponseInterface $response){
            return $response->withHeader('warning', '299 - This endpoint is deprecated.');
        }), 'test_ccp_log_deprecated');

        // test "ccp_error_limit" middleware
        $stack->after('ccp_error_limit', \GuzzleHttp\Middleware::mapResponse(function(\Psr\Http\Message\ResponseInterface $response){
            return $response->withStatus(400)           // 4xx or 5xx response is important
            ->withHeader('X-Esi-Error-Limit-Reset', 50) // error window reset in s
            ->withHeader('X-Esi-Error-Limit-Remain', 8) // errors possible in current error window
            ->withHeader('X-Esi-Error-Limited', '');    // endpoint blocked
        }), 'test_ccp_error_limit');
        */
    }

    /**
     * get configuration for GuzzleCcpLogMiddleware Middleware
     * @return array
     */
    protected function getCcpLogMiddlewareConfig() : array {
        return [
            'ccp_log_loggable_callback' => $this->getIsLoggable(),
            'ccp_log_callback' => $this->log()
        ];
    }

    /**
     * get configuration for GuzzleCcpErrorLimitMiddleware Middleware
     * @return array
     */
    protected function getCcpErrorLimitMiddlewareConfig() : array {
        return [
            'ccp_limit_log_callback' => $this->log()
        ];
    }
}