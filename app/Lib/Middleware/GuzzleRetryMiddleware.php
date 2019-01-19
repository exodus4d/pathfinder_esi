<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 19.01.2019
 * Time: 11:34
 */

namespace Exodus4D\ESI\Lib\Middleware;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleRetryMiddleware extends \GuzzleRetry\GuzzleRetryMiddleware {

    /**
     * error message for exceeded max retry count
     */
    const ERROR_RETRY_COUNT_EXCEEDED = 'Max retry count of %s exceeded. %s $s HTTP/%s → {code} {phrase}';

    public function __construct(callable $nextHandler, array $defaultOptions = []){

        $defaultOptions['on_retry_callback'] = $this->retryCallback();

        parent::__construct($nextHandler, $defaultOptions);
    }

    /**
     * get callback function for 'on_retry_callback' option
     * @see https://packagist.org/packages/caseyamcl/guzzle_retry_middleware
     * @return callable
     */
    protected function retryCallback() : callable {
        return function(
            int $attemptNumber,
            float $delay,
            RequestInterface $request,
            array $options,
            ?ResponseInterface $response = null
        ) : void {
            var_dump('retry callback... ' . $attemptNumber);
        };
    }
}