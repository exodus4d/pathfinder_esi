<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 31.12.2018
 * Time: 18:41
 */

namespace Exodus4D\ESI;

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
        $middleware['resource_legacy'] = Middleware::mapResponse(function(ResponseInterface $response) : ResponseInterface {
            var_dump('legacy.....');
            var_dump($response->getHeaders());
            var_dump($response->getHeaderLine('X-Esi-Error-Limit-Remain'));
            var_dump($response->hasHeader('X-Esi-Error-Limit-Remain'));
            return $response;
        });

        return $middleware;
    }
}