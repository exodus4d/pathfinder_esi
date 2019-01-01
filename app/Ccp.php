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
        $middleware['resource_legacy'] = Middleware::tap(null, function(RequestInterface $request, array $options, ResponseInterface $response){
            var_dump('legacy.....');
            var_dump(gettype($response));
            var_dump($response->getHeaders());
            var_dump($response->getHeaderLine('test'));
            var_dump($response->hasHeader('test'));
        });

        return $middleware;
    }
}