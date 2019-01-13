<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:24
 */

namespace Exodus4D\ESI;


use Cache\Adapter\Redis\RedisCachePool;
use lib\logging\LogInterface;
use Exodus4D\ESI\Lib\Cache\Storage\CacheStorageInterface;
use Exodus4D\ESI\Lib\Cache\Storage\Psr6CacheStorage;
use Exodus4D\ESI\Lib\Cache\Strategy\CacheStrategyInterface;
use Exodus4D\ESI\Lib\Cache\Strategy\PrivateCacheStrategy;
use Exodus4D\ESI\Lib\Middleware\GuzzleLogMiddleware;
use Exodus4D\ESI\Lib\Middleware\GuzzleCacheMiddleware;
use Exodus4D\ESI\Lib\Middleware\GuzzleJsonMiddleware;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\StreamInterface;

abstract class Api extends \Prefab implements ApiInterface {

    /**
     * default for: accepted response type
     * -> Affects "Accept" HTTP Header
     */
    const DEFAULT_ACCEPT_TYPE                       = 'json';

    /**
     * default for: request timeout
     */
    const DEFAULT_TIMEOUT                           = 3.0;

    /**
     * default for: connect timeout
     */
    const DEFAULT_CONNECT_TIMEOUT                   = 3.0;

    /**
     * default for: read timeout
     */
    const DEFAULT_READ_TIMEOUT                      = 10.0;

    /**
     * default for: debug requests
     */
    const DEFAULT_DEBUG_REQUESTS                    = false;

    /**
     * default for: log level
     */
    const DEFAULT_DEBUG_LEVEL                       = 0;

    // Guzzle Retry Middleware defaults -------------------------------------------------------------------------------
    // -> https://packagist.org/packages/caseyamcl/guzzle_retry_middleware

    /**
     * default for: activate middleware "retry requests"
     */
    const DEFAULT_RETRY_ENABLED                     = true;

    /**
     * default for: retry request count
     */
    const DEFAULT_RETRY_COUNT_MAX                   = 2;

    /**
     * default for: retry request "on timeout"
     */
    const DEFAULT_RETRY_ON_TIMEOUT                  = true;

    /**
     * default for: retry requests "on status"
     */
    const DEFAULT_RETRY_ON_STATUS                   = [429, 503, 504];

    /**
     * default for: Retry request add "X-Retry-Counter" header
     */
    const DEFAULT_RETRY_EXPOSE_RETRY_HEADER         = false;

    // ================================================================================================================
    // API class properties
    // ================================================================================================================

    /**
     * WebClient instance
     * @var \Exodus4D\ESI\Lib\WebClient|null
     */
    private $client                                 = null;

    /**
     * base API URL
     * @var string
     */
    private $url                                    = '';

    /**
     * @var string
     */
    private $acceptType                             = self::DEFAULT_ACCEPT_TYPE;

    /**
     * Timeout of the request in seconds
     * Use 0 to wait indefinitely
     * @see https://guzzle.readthedocs.io/en/latest/request-options.html#timeout
     * @var float
     */
    private $timeout                                = self::DEFAULT_TIMEOUT;

    /**
     * Timeout for server connect in seconds
     * @see https://guzzle.readthedocs.io/en/latest/request-options.html#connect-timeout
     * @var float
     */
    private $connectTimeout                         = self::DEFAULT_CONNECT_TIMEOUT;

    /**
     * Read timeout for Streams
     * Should be less than "default_socket_timeout" PHP ini
     * @see https://guzzle.readthedocs.io/en/latest/request-options.html#read-timeout
     * @var float
     */
    private $readTimeout                            = self::DEFAULT_READ_TIMEOUT;

    /**
     * HTTP proxy
     * -> for debugging purpose it might help to proxy requests through a local proxy
     *    e.g. 127.0.0.1:8888 (check out Fiddler https://www.telerik.com/fiddler)
     *    this should be used with 'verify' == false for HTTPS requests
     * @see http://docs.guzzlephp.org/en/stable/request-options.html#proxy
     * @var null|string|array
     */
    private $proxy                                  = null;

    /**
     * SSL certificate verification behavior of a request
     * @see http://docs.guzzlephp.org/en/stable/request-options.html#verify
     * @var bool
     */
    private $verify                                 = true;

    /**
     * Debug requests if enabled
     * @see https://guzzle.readthedocs.io/en/latest/request-options.html#debug
     * @var bool
     */
    private $debugRequests                          = self::DEFAULT_DEBUG_REQUESTS;

    /**
     * Debug level for API requests
     * @var int
     */
    private $debugLevel                             = self::DEFAULT_DEBUG_LEVEL;

    /**
     * UserAgent send with requests
     * @var string
     */
    private $userAgent                              = '';

    /**
     * Callback function that returns new Log object
     * which extends logging\LogInterface class
     * @var null|callable
     */
    private $getLog                                 = null;

    // Guzzle Retry Middleware config ---------------------------------------------------------------------------------

    /**
     * Retry Middleware enabled for request
     * @var bool
     */
    private $retryEnabled                           = self::DEFAULT_RETRY_ENABLED;

    /**
     * Retry Middleware max retry count
     * @var int
     */
    private $retryCountMax                          = self::DEFAULT_RETRY_COUNT_MAX;

    /**
     * Retry Middleware retry on timeout
     * @var bool
     */
    private $retryOnTimeout                         = self::DEFAULT_RETRY_ON_TIMEOUT;

    /**
     * Retry Middleware retry on status
     * @var array
     */
    private $retryOnStatus                          = self::DEFAULT_RETRY_ON_STATUS;

    /**
     * @var bool
     */
    private $retryExposeRetryHeader                 = self::DEFAULT_RETRY_EXPOSE_RETRY_HEADER;

    /**
     * Api constructor.
     * @param string $url
     */
    public function __construct(string $url){
        $this->setUrl($url);
    }

    /**
     * @return Lib\WebClient
     */
    protected function getClient() : namespace\Lib\WebClient {
        if(!$this->client){
            $this->client = $this->initClient();
        }

        return $this->client;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url){
        $this->url = $url;
    }

    /**
     * @param string $acceptType
     */
    public function setAcceptType(string $acceptType = self::DEFAULT_ACCEPT_TYPE){
        $this->acceptType = $acceptType;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout = self::DEFAULT_TIMEOUT){
        $this->timeout = $timeout;
    }

    /**
     * @param float $connectTimeout
     */
    public function setConnectTimeout(float $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT){
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * @param float $readTimeout
     */
    public function setReadTimeout(float $readTimeout = self::DEFAULT_READ_TIMEOUT){
        $this->readTimeout = $readTimeout;
    }

    /**
     * @param null|string|array $proxy
     */
    public function setProxy($proxy){
        $this->proxy = $proxy;
    }

    /**
     * @param bool $verify
     */
    public function setVerify(bool $verify){
        $this->verify = $verify;
    }

    /**
     * debug requests
     * @param bool $debugRequests
     */
    public function setDebugRequests(bool $debugRequests = self::DEFAULT_DEBUG_REQUESTS){
        $this->debugRequests  = $debugRequests;
    }

    /**
     * @param int $debugLevel
     */
    public function setDebugLevel(int $debugLevel = self::DEFAULT_DEBUG_LEVEL){
        $this->debugLevel = $debugLevel;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent){
        $this->userAgent = $userAgent;
    }

    /**
     * set a callback that returns an new Log object that implements LogInterface
     * @param callable $newLog
     */
    public function setNewLog(callable $newLog){
        $this->getLog = $newLog;
    }

    /**
     * @return string
     */
    public function getUrl() : string {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getAcceptType() : string {
        return $this->acceptType;
    }

    /**
     * @return float
     */
    public function getTimeout() : float {
        return $this->timeout;
    }

    /**
     * @return float
     */
    public function getConnectTimeout() : float {
        return $this->connectTimeout;
    }

    /**
     * @return float
     */
    public function getReadTimeout() : float {
        return $this->readTimeout;
    }

    /**
     * @return array|string|null
     */
    public function getProxy(){
        return $this->proxy;
    }

    /**
     * @return bool
     */
    public function getVerify(): bool {
        return $this->verify;
    }

    /**
     * @return bool
     */
    public function getDebugRequests() : bool {
        return $this->debugRequests;
    }

    /**
     * @return int
     */
    public function getDebugLevel() : int {
        return $this->debugLevel;
    }

    /**
     * @return string
     */
    public function getUserAgent() : string {
        return $this->userAgent;
    }

    /**
     * @return callable|null
     */
    public function getNewLog() : ?callable {
        return $this->getLog;
    }

    /**
     * @return callable|null
     */
    public function getCachePool() : ?callable {
        return function() : ?CacheItemPoolInterface {
            $client = new \Redis();
            $client->connect('localhost', 6379, 2);
            $client->select(2);
            return new RedisCachePool($client);
        };
    }

    /**
     * log callback function
     * @return \Closure
     */
    protected function log() : callable {
        return function(string $action, string $level, string $message, array $data = [], string $tag = 'default'){
            if(is_callable($newLog = $this->getNewLog())){
                /**
                 * @var LogInterface $log
                 */
                $log = $newLog($action, $level);
                $log->setMessage($message);
                $log->setData($data);
                $log->setTag($tag);
                $log->buffer();
            }
        };
    }

    /**
     * get HTTP request Header for Authorization
     * @param string $credentials
     * @param string $type
     * @return array
     */
    protected function getAuthHeader(string $credentials, string $type = 'Basic') : array {
        return ['Authorization' => ucfirst($type) . ' ' . $credentials];
    }

    /**
     * init new webClient for this Api
     * @return Lib\WebClient
     */
    protected function initClient() : namespace\Lib\WebClient {
        return new namespace\Lib\WebClient(
            $this->getUrl(),
            $this->getClientConfig(),
            function(HandlerStack &$stack){
                $this->initStack($stack);
            }
        );
    }

    /**
     * get webClient config based on current Api settings
     * @return array
     */
    protected function getClientConfig() : array {
        return [
            'timeout'           => $this->getTimeout(),
            'connect_timeout'   => $this->getConnectTimeout(),
            'read_timeout'      => $this->getReadTimeout(),
            'proxy'             => $this->getProxy(),
            'verify'            => $this->getVerify(),
            'debug'             => $this->getDebugRequests(),
            'headers'           => [
                'User-Agent'    => $this->getUserAgent()
            ]
        ];
    }

    /**
     * modify HandlerStack by ref
     * -> use this to manipulate the Stack and add/remove custom Middleware
     * -> order of Stack is important! Execution order of each Middleware depends on Stack order:
     * @see https://guzzle.readthedocs.io/en/stable/handlers-and-middleware.html#handlerstack
     * @param HandlerStack $stack
     */
    protected function initStack(HandlerStack &$stack) : void {

        if($this->getAcceptType() == 'json'){
            // json middleware prepares request and response for JSON data
            $stack->push( GuzzleJsonMiddleware::factory(), 'json');
        }

        // error log middleware logs all request errors
        // -> add somewhere to stack BOTTOM so that it runs at the end catches errors from previous middlewares
        $stack->push(GuzzleLogMiddleware::factory($this->getLogMiddlewareConfig()), 'log');

        // cache responses based on the response Headers and cache configuration6
        $stack->push(GuzzleCacheMiddleware::factory(
            $this->getCacheMiddlewareConfig(),
            $this->getCacheMiddlewareStrategy()
        ), 'cache');

        // retry failed requests should be on TOP of stack
        // -> in case of retry other middleware don´t need to know about the failed attempts
        $stack->push(GuzzleRetryMiddleware::factory($this->getRetryMiddlewareConfig()), 'retry');
    }

    /**
     * get configuration for GuzzleLogMiddleware Middleware
     * @return array
     */
    protected function getLogMiddlewareConfig() : array {
        return [
            'log_enabled'               => true,
            'log_stats'                 => true,
            'log_5xx'                   => true,
            'log_4xx'                   => true,
            'log_callback'              => $this->log(),
            'log_file'                  => 'esi_requests'
        ];
    }

    /**
     * get configuration for GuzzleCacheMiddleware Middleware
     * @return array
     */
    protected function getCacheMiddlewareConfig() : array {
        return [
            'cache_enabled'             => true,
            'cache_debug'               => true
        ];
    }

    /**
     * @return CacheStrategyInterface
     */
    protected function getCacheMiddlewareStrategy() : CacheStrategyInterface {
        return new PrivateCacheStrategy($this->getCacheMiddlewareStorage());
    }

    /**
     * get instance of a CacheStore that is used in GuzzleCacheMiddleware
     * -> we use a PSR-6 compatible CacheStore that can handle any $cachePool
     *    that implements the PSR-6 CacheItemPoolInterface
     *    (e.g. an adapter for Redis -> more adapters here: http://www.php-cache.com)
     * @return CacheStorageInterface|null
     */
    protected function getCacheMiddlewareStorage() : ?CacheStorageInterface {
        if(is_callable($this->getCachePool()) && !is_null($cachePool = $this->getCachePool()())){
            return new Psr6CacheStorage($cachePool);
        }
        return null;
    }

    /**
     * get configuration GuzzleRetryMiddleware Retry Middleware
     * @see https://packagist.org/packages/caseyamcl/guzzle_retry_middleware
     * @return array
     */
    protected function getRetryMiddlewareConfig() : array {
        return [
            'retry_enabled'             => $this->retryEnabled,
            'max_retry_attempts'        => $this->retryCountMax,
            'retry_on_timeout'          => $this->retryOnTimeout,
            'retry_on_status'           => $this->retryOnStatus,
            'expose_retry_header'       => $this->retryExposeRetryHeader,
            'default_retry_multiplier'  => 0.5
        ];
    }

    /**
     * same as PHP´s array_merge_recursive() function except of "distinct" array values in return
     * -> works like jQuery extend()
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected static function array_merge_recursive_distinct(array &$array1, array &$array2) : array {
        $merged = $array1;
        foreach($array2 as $key => &$value){
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
                $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
            }else{
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * get error response with error message in body
     * -> wraps a GuzzleException (or any other Exception) into an error response
     * -> this class should handle any Exception thrown within Guzzle Context
     * @see http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions
     * @param \Exception $e
     * @return Response
     */
    protected function getErrorResponse(\Exception $e) : Response {
        $message = [get_class($e)];

        if($e instanceof ConnectException){
            // hard fail! e.g. cURL connect error
            $message[] = $e->getMessage();
        }elseif($e instanceof ClientException){
            // 4xx response (e.g. 404 URL not found)
            $message[] = 'HTTP ' . $e->getCode();
            $message[] = $e->getMessage();
        }elseif($e instanceof ServerException){
            // 5xx response
            $message[] = 'HTTP ' . $e->getCode();
            $message[] = $e->getMessage();
        }elseif($e instanceof RequestException){
            // hard fail! e.g. cURL errors (connection timeout, DNS errors, etc.)
            $message[] = $e->getMessage();
        }elseif($e instanceof \Exception){
            // any other Exception type
            $message[] = $e->getMessage();
        }

        $body = (object)[];
        $body->error = implode(', ', $message);

        $bodyStream = \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\json_encode($body));

        $response = $this->getClient()->newResponse();
        $response = $response->withStatus(200, 'Error Response');
        $response = $response->withBody($bodyStream);

        return $response;
    }

    protected function request(string $method, string $uri, array $options = [], array $additionalOptions = []) : ?StreamInterface {
        var_dump('start ---------------------------------');
        var_dump('$method : ' . $method);
        var_dump('$uri : ' . $uri);
        //var_dump($additionalOptions);

        $body = null;

        try{
            // get new request
            $request = $this->getClient()->newRequest($method, $uri);

            /**
             * @var $response Response
             */
            $response = $this->getClient()->send($request, $options);

            $body = $response->getBody();

            //$body = $bodyStream->getContents();

            var_dump('response: ----');
            var_dump('statuscode: ' . $response->getStatusCode());
            var_dump('getReasonPhrase: ' . $response->getReasonPhrase());
            var_dump($response->getHeader('X-Guzzle-Cache'));
            //var_dump($response->getHeaders());
            //var_dump($body);

        }catch(TransferException $e){
            // Base Exception of Guzzle errors
            // -> this includes "expected" errors like 4xx responses (ClientException)
            //    and "unexpected" errors like cURL fails (ConnectException)...
            // -> error is already logged by LogMiddleware
            $res = $this->getErrorResponse($e);
            $body = $res->getBody();

            var_dump('eeeeeeee');
            var_dump($res->getStatusCode());
            var_dump($res->getReasonPhrase());
            var_dump($body);
            var_dump($body->getContents());
        }catch(\Exception $e){
            // Hard fail! Any other type of error
            // -> e.g. RuntimeException,...
            // TODO trigger Error...

            $body = $this->getErrorResponse($e)->getBody();
        }

        return $body;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @param array $additionalOptions
     * @return mixed|null
     */
    /*
    protected function request(string $method, string $url, array $options = [], array $additionalOptions = []){
        $method = strtoupper($method);

        // default request options
        $requestOptions = [
            'timeout' => $this->getTimeout(),
            'method' => $method,
            'user_agent' => $this->getUserAgent()
        ];

        // extend/overwrite request options with custom options
        $requestOptions = self::array_merge_recursive_distinct($requestOptions, $options);

        // format content and set 'Content-Type' header
        if( !empty($requestOptions['content']) ){
            if(empty($contentType = $requestOptions['header']['Content-Type'])){
                $contentType = 'application/json';
                $requestOptions['header']['Content-Type'] = $contentType;
            }

            switch($contentType){
                case 'application/x-www-form-urlencoded':
                    $requestOptions['content'] =  http_build_query($requestOptions['content']);
                    break;
                case 'application/json':
                default:
                    $requestOptions['content'] =  json_encode($requestOptions['content'], JSON_UNESCAPED_SLASHES);
            }
        }else{
            unset($requestOptions['content']);
        }

        // format Header array into plain array
        if( !empty($requestOptions['header']) ){
            $requestOptions['header'] = $this->formatHeaders($requestOptions['header']);
        }

        $webClient = namespace\Lib\WebClient::instance($this->getDebugLevel(), $this->getDebugLogRequests());

        $responseBody = $webClient->request($url, $requestOptions, $additionalOptions);

        return $responseBody;
    }*/
}