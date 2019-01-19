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
     * default for: activate middleware "retry requests"
     */
    const DEFAULT_RETRY_ENABLED                 = true;

    /**
     * default for: retry request count
     */
    const DEFAULT_RETRY_MAX_ATTEMPTS            = 2;

    /**
     * default for: retry multiplier
     */
    const DEFAULT_RETRY_MULTIPLIER              = 0.5;

    /**
     * default for: retry request "on timeout"
     */
    const DEFAULT_RETRY_ON_TIMEOUT              = true;

    /**
     * default for: retry requests "on status"
     */
    const DEFAULT_RETRY_ON_STATUS               = [429, 503, 504];

    /**
     * default for: retry request add "X-Retry-Counter" header
     */
    const DEFAULT_RETRY_EXPOSE_RETRY_HEADER     = false;

    // Custom config options ------------------------------------------------------------------------------------------

    /**
     * default for: log requests that exceed "retryCountMax"
     */
    const DEFAULT_RETRY_LOG_ERROR               = true;

    /**
     * default for: callback function that checks a $request
     * -> can be used to "exclude" some requests from been logged (e.g. on expected downtime)
     */
    const DEFAULT_RETRY_LOGGABLE_CALLBACK       = null;

    /**
     * default for: callback function for logging
     */
    const DEFAULT_RETRY_LOG_CALLBACK            = null;

    /**
     * error message for exceeded max retry count
     */
    const ERROR_RETRY_COUNT_EXCEEDED            = 'Max retry count of %s exceeded. %s $s HTTP/%s → {code} {phrase}';

    private $defaultOptions = [
        'retry_enabled'                         => self::DEFAULT_RETRY_ENABLED,
        'max_retry_attempts'                    => self::DEFAULT_RETRY_MAX_ATTEMPTS,
        'default_retry_multiplier'              => self::DEFAULT_RETRY_MULTIPLIER,
        'retry_on_status'                       => self::DEFAULT_RETRY_ON_STATUS,
        'retry_on_timeout'                      => self::DEFAULT_RETRY_ON_TIMEOUT,
        'expose_retry_header'                   => self::DEFAULT_RETRY_EXPOSE_RETRY_HEADER
    ];

    public function __construct(callable $nextHandler, array $defaultOptions = []){
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);

        if($defaultOptions['retry_log_error']){
            // add callback function for error logging
            $defaultOptions['on_retry_callback'] = $this->retryCallback();
        }



        parent::__construct($nextHandler, $this->defaultOptions);
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