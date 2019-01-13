<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 27.03.2017
 * Time: 16:06
 */

namespace Exodus4D\ESI\Lib;

use Exodus4D\ESI\Api;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class WebClient {

    /**
     * @var Client|null
     */
    private $client                             = null;

    /**
     * debugLevel used for internal error/warning logging
     * @var int
     */
    protected $debugLevel                       = Api::DEFAULT_DEBUG_LEVEL;

    /**
     * if true any requests gets logged in log file
     * @var bool
     */
    protected $debugLogRequests                 = Api::DEFAULT_DEBUG_REQUESTS;

    /**
     * WebClient constructor.
     * @param string $baseUri
     * @param array $config
     * @param callable|null $initStack modify handler Stack by ref
     */
    public function __construct(string $baseUri, array $config = [], ?callable $initStack = null){
        // use cURLHandler for all requests
        $handler = new CurlHandler();
        // new Stack for the Handler, manages Middleware for requests
        $stack = HandlerStack::create($handler);

        // init stack by reference
        if(is_callable($initStack)){
            $initStack($stack);
        }
/*
        $stack->push(Middleware::tap(function($request){
            var_dump('tab middleware ---');
            var_dump($request->getHeaders());
        }));
*/
        // Client default configuration
        $config['handler'] = $stack;
        $config['base_uri'] = $baseUri;

        // init client
        $this->client = new Client($config);


    }

    /**
     * @param string $method
     * @param string $uri
     * @return Request
     */
    public function newRequest(string $method, string $uri) : Request {
        return new Request($method, $uri);
    }

    /**
     * get new Response object
     * @param int $status
     * @param array $headers
     * @param null $body
     * @param string $version
     * @param string|null $reason
     * @return Response
     */
    public function newResponse(int $status = 200, array $headers = [], $body = null, string $version = '1.1', ?string $reason = null) : Response {
        return new Response($status, $headers, $body, $version, $reason);
    }

    public function __call(string $name, array $arguments = []){
        $return = [];

        if(is_object($this->client)){
            if( method_exists($this->client, $name) ){
                $return  = call_user_func_array([$this->client, $name], $arguments);
            }else{
                // TODO
                /*
                $errorMsg = $this->getMissingMethodError(get_class($this->client), $name);
                $this->getLogger('ERROR')->write($errorMsg);
                \Base::instance()->error(501, $errorMsg);*/
            }
        }else{
            // TODO
            //\Base::instance()->error(501, self::ERROR_CLIENT_INVALID);
        }

        return $return;
    }


}

class WebClientOld extends \Web {

    const CACHE_KEY_ERROR_LIMIT                 = 'CACHED_ERROR_LIMIT';
    const CACHE_KEY_LOGGABLE_LIMIT              = 'CACHED_LOGGABLE_LIMIT';

    const ERROR_REQUEST_URL                     = 'Invalid HTTP request url: \'%s\'';
    const ERROR_REQUEST_METHOD                  = 'Invalid HTTP request method: \'%s\'| url: \'%s\'';

    const ERROR_STATUS_LOG                      = 'HTTP %s: \'%s\' | url: %s \'%s\'%s';
    const ERROR_RESOURCE_LEGACY                 = 'Resource: \'%s\' has been marked as legacy. url: \'%s\' | header: \'%s\'';
    const ERROR_RESOURCE_DEPRECATED             = 'Resource: \'%s\' has been marked as deprecated. url: \'%s\' | header: \'%s\'';
    const ERROR_LIMIT_CRITICAL                  = 'Error rate reached critical amount. url: \'%s\' | errorCount: %s | errorRemainCount: %s';
    const ERROR_LIMIT_EXCEEDED                  = 'Error rate limit exceeded! We are blocked for (%s seconds)';
    const DEBUG_URI_BLOCKED                     = 'Debug request blocked. Error limit exceeded. url: \'%s\' blocked for %2ss';
    const DEBUG_REQUEST                         = 'Debug request. url: \'%s\' data: %s';

    const REQUEST_METHODS                       = ['GET', 'POST', 'PUT', 'DELETE'];

    // request failure ================================================================================================

    /**
     * Failed requests will be re-send up to X times until give up
     * -> This might help in case of a timeout or temporary unavailable endpoint
     */
    const RETRY_COUNT_MAX                       = 2;

    // error limits ===================================================================================================
    // ESI calls will return special headers in case a client hits a "error limit" for a single endpoint

    /**
     * log error when this error count is reached for a single API endpoint in the current error window
     */
    const ERROR_COUNT_MAX_URL                   = 30;

    /**
     * log error if less then this errors remain in current error window (all endpoints)
     */
    const ERROR_COUNT_REMAIN_TOTAL              = 10;

    // loggable limits ================================================================================================
    // ESI endpoints that return warning headers (e.g. "resource_legacy", "resource_deprecated") will get logged
    // To prevent big file I/O on these log files, errors get "throttled" and not all of them get logged

    /**
     * Time interval used for error inspection (seconds)
     */
    const LOGGABLE_COUNT_INTERVAL               = 60;

    /**
     * Log first "2" errors that occur for an endpoint within "60" (LOGGABLE_COUNT_INTERVAL) seconds interval
     */
    const LOGGABLE_COUNT_MAX_URL                = 2;


    /**
     * debugLevel used for internal error/warning logging
     * @var int
     */
    protected $debugLevel                       = Api::DEFAULT_DEBUG_LEVEL;

    /**
     * if true any requests gets logged in log file
     * @var bool
     */
    protected $debugLogRequests                 = Api::DEFAULT_DEBUG_LOG_REQUESTS;

/*
    public function __construct(int $debugLevel = Api::DEFAULT_DEBUG_LEVEL, bool $debugLogRequests = Api::DEFAULT_DEBUG_LOG_REQUESTS){
        $this->debugLevel = $debugLevel;
        $this->debugLogRequests = $debugLogRequests;
    }
*/
    /**
     * parse array with HTTP header data
     * @param array $headers
     * @return array
     */
    /*
    protected function parseHeaders(array $headers = []) : array {
        $parsedHeaders = [];
        foreach($headers as $header){
            $parts = explode(':', $header, 2);
            $parsedHeaders[strtolower(trim($parts[0]))] = isset($parts[1]) ? trim($parts[1]) :  '';
        }
        return $parsedHeaders;
    }*/

    /**
     * @param array $headers
     * @return int
     */
    /*
    protected function getStatusCodeFromHeaders(array $headers = []) : int {
        $statusCode = 0;
        foreach($headers as $key => $value){
            if(preg_match('/http\/1\.\d (\d{3}?)/i', $key, $matches)){
                $statusCode = (int)$matches[1];
                break;
            }
        }
        return $statusCode;
    }*/

    /**
     * get HTTP status type from HTTP status code (e.g. 404 )> 'err_client')
     * @param int $statusCode
     * @return string
     */
    protected function getStatusType(int $statusCode) : string{
        $typeLevel = (int)substr($statusCode, 0, 1);
        switch($typeLevel){
            case 1:
                $statusType = 'info';
                break;
            case 2:
                $statusType = 'ok';
                break;
            case 3:
                $statusType = 'redirect';
                break;
            case 4:
                $statusType = 'err_client';
                break;
            case 5:
                $statusType = 'err_server';
                break;
            default:
                $statusType = 'unknown';
        }

        return $statusType;
    }

    /**
     * @param int $code
     * @param string $method
     * @param string $url
     * @param null $responseBody
     * @return string
     */
    protected function getErrorMessageFromJsonResponse(int $code, string $method, string $url, $responseBody = null) : string {
        $message = empty($responseBody->message) ?  @constant('Base::HTTP_' . $code) : $responseBody->message;
        $body = !is_null($responseBody) ? ' | body: ' . print_r($responseBody, true) : '';

        return sprintf(self::ERROR_STATUS_LOG, $code, $message, $method, $url, $body);
    }

    /**
     * get Logger obj for given status type
     * @param string $statusType
     * @return \Log
     */
    protected function getLogger(string $statusType) : \Log{
        switch($statusType){
            case 'err_server':
                $logfile = 'esi_error_server';
                break;
            case 'err_client':
                $logfile = 'esi_error_client';
                break;
            case 'resource_legacy':
                $logfile = 'esi_resource_legacy';
                break;
            case 'resource_deprecated':
                $logfile = 'esi_resource_deprecated';
                break;
            case 'debug_request':
                $logfile = 'esi_debug_request';
                break;
            default:
                $logfile = 'esi_error_unknown';
        }
        return new \Log($logfile . '.log');
    }

    /**
     * check response headers for warnings/errors and log them
     * @param array $headers
     * @param string $url
     */
    /*
    protected function checkResponseHeaders(array $headers, string $url){
        $statusCode = $this->getStatusCodeFromHeaders($headers);

        // check ESI warnings -----------------------------------------------------------------------------------------
        // extract ESI related headers
        $warningHeaders = array_filter($headers, function($key){
            return preg_match('/^warning/i', $key);
        }, ARRAY_FILTER_USE_KEY);

        if(count($warningHeaders)){
            // get "normalized" url path without params/placeholders
            $urlPath = $this->getNormalizedUrlPath($url);
            foreach($warningHeaders as $key => $value){
                if( preg_match('/^199/i', $value) && $this->isLoggable('legacy', $url) ){
                    $this->getLogger('resource_legacy')->write(sprintf(self::ERROR_RESOURCE_LEGACY, $urlPath, $url, $key . ': ' . $value));
                }
                if( preg_match('/^299/i', $value) && $this->isLoggable('deprecated', $url) ){
                     $this->getLogger('resource_deprecated')->write(sprintf(self::ERROR_RESOURCE_DEPRECATED, $urlPath, $url, $key . ': ' . $value));
                }
            }
        }

        // check ESI error limits -------------------------------------------------------------------------------------
        if($statusCode >= 400 && $statusCode <= 599){
            // extract ESI related headers
            $esiHeaders = array_filter($headers, function($key){
                return preg_match('/^x-esi-/i', $key);
            }, ARRAY_FILTER_USE_KEY);

            if(array_key_exists('x-esi-error-limit-reset', $esiHeaders)){
                // time in seconds until current error limit "windows" reset
                $esiErrorLimitReset = (int)$esiHeaders['x-esi-error-limit-reset'];

                // block further api calls for this URL until error limit is reset/clear
                $blockUrl = false;

                // get "normalized" url path without params/placeholders
                $urlPath = $this->getNormalizedUrlPath($url);

                $f3 = \Base::instance();
                if(!$f3->exists(self::CACHE_KEY_ERROR_LIMIT, $esiErrorRate)){
                    $esiErrorRate = [];
                }
                // increase error count for this $url
                $errorCount = (int)$esiErrorRate[$urlPath]['count'] + 1;
                $esiErrorRate[$urlPath]['count'] = $errorCount;

                // sort by error count desc
                uasort($esiErrorRate, function($a, $b) {
                    return $b['count'] <=> $a['count'];
                });

                if(array_key_exists('x-esi-error-limited', $esiHeaders)){
                    // we are blocked until new error limit window opens this should never happen
                    $blockUrl = true;
                    $this->getLogger('err_server')->write(sprintf(self::ERROR_LIMIT_EXCEEDED, $esiErrorLimitReset));
                }

                if(array_key_exists('x-esi-error-limit-remain', $esiHeaders)){
                    // remaining errors left until reset/clear
                    $esiErrorLimitRemain = (int)$esiHeaders['x-esi-error-limit-remain'];

                    if(
                        $errorCount > self::ERROR_COUNT_MAX_URL ||
                        $esiErrorLimitRemain < self::ERROR_COUNT_REMAIN_TOTAL
                    ){
                        $blockUrl = true;
                        $this->getLogger('err_server')->write(sprintf(self::ERROR_LIMIT_CRITICAL, $urlPath, $errorCount, $esiErrorLimitRemain));
                    }
                }

                if($blockUrl){
                    // to many error, block uri until error limit reset
                    $esiErrorRate[$urlPath]['blocked'] = true;
                }

                $f3->set(self::CACHE_KEY_ERROR_LIMIT, $esiErrorRate, $esiErrorLimitReset);
            }
        }
    }*/

    /**
     * get URL path from $url, removes path IDs, parameters, scheme and domain
     * @param $url
     * @return string
     */
    protected function getNormalizedUrlPath($url): string {
        return parse_url(strtok(preg_replace('/\/(\d+)\//', '/{x}/', $url), '?'), PHP_URL_PATH);
    }

    /**
     * @param string $type
     * @param string $urlPath
     * @return bool
     */
    /*
    protected function isLoggable(string $type, string $urlPath) : bool {
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
    }*/

    /**
     * check whether a HTTP request method is valid/given
     * @param string $method
     * @return bool
     */
    protected function checkRequestMethod(string $method): bool {
      return in_array($method, self::REQUEST_METHODS);
    }

    /**
     * check API url against blocked API endpoints blacklist
     * @param string $url
     * @return bool
     */
    protected function isBlockedUrl(string $url): bool {
        $isBlocked = false;
        $f3 = \Base::instance();
        if($ttlData = $f3->exists(self::CACHE_KEY_ERROR_LIMIT, $esiErrorRate)){
            // check url path if blocked
            $urlPath = $this->getNormalizedUrlPath($url);
            $esiErrorData = array_filter($esiErrorRate, function($value, $key) use (&$urlPath){
                return ($key === $urlPath && $value['blocked']);
            }, ARRAY_FILTER_USE_BOTH);

            if(!empty($esiErrorData)){
                $isBlocked = true;
                if($this->debugLevel === 3){
                    // log debug information
                    $this->getLogger('err_server')->write(sprintf(
                        self::DEBUG_URI_BLOCKED,
                        $urlPath,
                        round($ttlData[0] + $ttlData[1] - time())
                    ));
                }
            }
        }

        return $isBlocked;
    }

    /**
     * write request information into logfile
     * @param string $url
     * @param $response
     */
    /*
    protected function debugLogRequest(string $url, $response){
        if($this->debugLogRequests){
            $this->getLogger('debug_request')->write(sprintf(self::DEBUG_REQUEST, $url, print_r($response, true)));
        }
    }*/

    /**
     * get maxRetry count
     * @param mixed $retryCount
     * @return int
     */
    /*
    protected function getMaxRetryCount($retryCount) : int {
        return is_int($retryCount) ? max($retryCount, 0) :  self::RETRY_COUNT_MAX;
    }*/

    /**
     * @param string $url
     * @param array|null $options
     * @param array $additionalOptions
     * @param int $retryCount
     * @return mixed|null
     */
    public function request($url, array $options = null, array $additionalOptions = [], int $retryCount = 0){
        $responseBody = null;

        if(\Audit::instance()->url($url)){
            // check if url is blocked (error limit exceeded)
            if(!$this->isBlockedUrl($url)){
                if($this->checkRequestMethod($options['method'])){
                    // set max retry count in case of request error
                    $retryCountMax = $this->getMaxRetryCount($additionalOptions['retryCountMax']);

                    // retry request in case of error until request limit exceeds
                    $retry = false;

                    $response = parent::request($url, $options);

                    // write log file in debug mode
                    $this->debugLogRequest($url, $response);

                    // check cURL errors (e.g. timeout, ...)
                    /*
                    if(empty($curlErr = $response['error'])){

                    }else{
                        // handle cURL errors
                        switch($curlErrCode = $curlErr['code']){
                            case CURLE_OPERATION_TIMEOUTED:
                                $retry = true;
                                break;
                        }
                    }*/

                    $responseHeaders    = (array)$response['headers'];
                    $responseBody       = json_decode($response['body']);

                    // make sure return type is correct
                    if(
                        !is_array($responseBody) &&
                        !is_bool($responseBody) &&
                        !($responseBody instanceof \stdClass)
                    ){
                        $responseBody = null;
                    }

                    if( !empty($responseHeaders)){
                        $parsedResponseHeaders = $this->parseHeaders($responseHeaders);
                        // check response headers
                        $this->checkResponseHeaders($parsedResponseHeaders, $url);
                        $statusCode = $this->getStatusCodeFromHeaders($parsedResponseHeaders);
                        $statusType = $this->getStatusType($statusCode);

                        switch($statusType){
                            case 'info':                                                // HTTP 1xx
                            case 'ok':                                                  // HTTP 2xx
                                break;
                            case 'err_client':                                          // HTTP 4xx
                                if( !in_array($statusCode, (array)$additionalOptions['suppressHTTPLogging']) ){
                                    $errorMsg = $this->getErrorMessageFromJsonResponse(
                                        $statusCode,
                                        $options['method'],
                                        $url,
                                        $responseBody
                                    );
                                    $this->getLogger($statusType)->write($errorMsg);
                                }
                                break;
                            case 'err_server':                                          // HTTP 5xx
                                $retry = true;

                                if($retryCount == $retryCountMax){
                                    $errorMsg = $this->getErrorMessageFromJsonResponse(
                                        $statusCode,
                                        $options['method'],
                                        $url,
                                        $responseBody
                                    );
                                    $this->getLogger($statusType)->write($errorMsg);

                                    // trigger error
                                    if($additionalOptions['suppressHTTPErrors'] !== true){
                                        $f3 = \Base::instance();
                                        $f3->error($statusCode, $errorMsg);
                                    }
                                }
                                break;
                            default:
                        }

                        if(
                            $retry &&
                            $retryCount < $retryCountMax
                        ){
                            $retryCount++;
                            $this->request($url, $options, $additionalOptions, $retryCount);
                        }
                    }
                }else{
                    $this->getLogger('err_server')->write(sprintf(self::ERROR_REQUEST_METHOD, $options['method'], $url));
                }
            }
        }else{
            $this->getLogger('err_server')->write(sprintf(self::ERROR_REQUEST_URL, $url));
        }

        return $responseBody;
    }
}