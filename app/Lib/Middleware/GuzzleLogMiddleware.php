<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 05.01.2019
 * Time: 13:32
 */

namespace Exodus4D\ESI\Lib\Middleware;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleLogMiddleware {

    const DEFAULT_LOG_ENABLED       = true;
    const DEFAULT_LOG_ERROR         = true;
    const DEFAULT_LOG_STATS         = false;
    const DEFAULT_LOG_5XX           = true;
    const DEFAULT_LOG_4XX           = true;
    const DEFAULT_LOG_3XX           = false;
    const DEFAULT_LOG_2XX           = false;
    const DEFAULT_LOG_1XX           = false;
    const DEFAULT_LOG_ALL_STATUS    = false;
    const DEFAULT_LOG_ON_STATUS     = [];
    const DEFAULT_LOG_OFF_STATUS    = [];

    /**
     * default options can go here for middleware
     * @var array
     */
    private $defaultOptions = [
        'log_enabled'               => self::DEFAULT_LOG_ENABLED,
        'log_error'                 => self::DEFAULT_LOG_ERROR,
        'log_stats'                 => self::DEFAULT_LOG_STATS,
        'log_5xx'                   => self::DEFAULT_LOG_5XX,
        'log_4xx'                   => self::DEFAULT_LOG_4XX,
        'log_3xx'                   => self::DEFAULT_LOG_3XX,
        'log_2xx'                   => self::DEFAULT_LOG_2XX,
        'log_1xx'                   => self::DEFAULT_LOG_1XX,
        'log_all_status'            => self::DEFAULT_LOG_ALL_STATUS,
        'log_on_status'             => self::DEFAULT_LOG_ON_STATUS,
        'log_off_status'            => self::DEFAULT_LOG_OFF_STATUS
    ];

    /**
     * @var callable
     */
    private $nextHandler;

    /**
     * @var TransferStats|null
     */
    private $stats = null;

    /**
     * GuzzleLogMiddleware constructor.
     * @param callable $nextHandler
     * @param array $defaultOptions
     */
    public function __construct(callable $nextHandler, array $defaultOptions = []){
        $this->nextHandler = $nextHandler;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);
    }

    /**
     * log errors for requested URL
     * @param RequestInterface $request
     * @param array $options
     * @return mixed
     */
    public function __invoke(RequestInterface $request, array $options){
        // Combine options with defaults specified by this middleware
        $options = array_replace($this->defaultOptions, $options);

        $next = $this->nextHandler;

        // reset TransferStats
        $this->stats = null;

        // TransferStats can only be accessed through a callback -> 'on_stats' Core Guzzle option
        if($options['log_enabled'] && $options['log_stats'] && !isset($options['on_stats'])){
            $options['on_stats'] = function(TransferStats $stats){
                $this->stats = $stats;
            };
        }

        return $next($request, $options)->then(
            $this->onFulfilled($request, $options),
            $this->onRejected($request, $options)
        );
    }

    /**
     * No exceptions were thrown during processing
     * @param RequestInterface $request
     * @param array $options
     * @return \Closure
     */
    protected function onFulfilled(RequestInterface $request, array $options) : \Closure {
        return function (ResponseInterface $response) use ($request, $options) {
            var_dump('onFullFilled() Log ');

            if($options['log_enabled']){
                $this->log($options, $request, $response);
            }

            return $response;
        };
    }

    /**
     * An exception or error was thrown during processing
     * @param RequestInterface $request
     * @param array $options
     * @return \Closure
     */
    protected function onRejected(RequestInterface $request, array $options) : \Closure {
        return function ($reason) use ($request, $options) {
            var_dump('onRejected() Log ');
            var_dump(get_class($reason));
            if($options['log_enabled']){
                $response = null;
                //$handlerContext = [];

                if(($reason instanceof RequestException) && $reason->hasResponse()){
                    //$handlerContext = $reason->getHandlerContext();
                        $response = $reason->getResponse();
                }

                $this->log($options, $request, $response, $reason);
            }

            return \GuzzleHttp\Promise\rejection_for($reason);
        };
    }

    protected function log(array $options, RequestInterface $request, ?ResponseInterface $response, ?\Exception $exception){
        if($options['log_enabled']){
            $logData = [];

            if(is_null($response)){
                if($options['log_error']){
                    if(!empty($reasonData = $this->logReason($exception))){
                        $logData['reason'] = $reasonData;
                    }
                }
            }else{

            }

            var_dump('$logData');
            var_dump($logData);
        }
    }

    protected function logReason(?\Exception $exception) : array {
        $data = [];
        if($exception instanceof RequestException){
            $handlerContext = $exception->getHandlerContext();
            $data['errno'] = $handlerContext['errno'];
            $data['error'] = $handlerContext['error'];
        }
        return $data;
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