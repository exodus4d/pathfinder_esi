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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Ccp extends Api {

    /**
     * add some middleware for all CCP related API calls
     * @return array
     */
    protected function getClientMiddleware(): array {
        $middleware = parent::getClientMiddleware();

        // log "legacy" endpoints
        $middleware['test'] = Middleware::mapResponse(function(ResponseInterface $response){
            return $response->withHeader('warning', '199 - This endpoint has been updated.');
        });


        $middleware['resource_legacy'] = GuzzleCcpLoggingMiddleware::factory($this->getCcpLoggingMiddlewareConfig());

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
            'is_loggable_callback' => function(RequestInterface $request, ResponseInterface $response = null){
                // todo
                var_dump('is_loggable_callback()...');
                return true;
            }
        ];
    }
}