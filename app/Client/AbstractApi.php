<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:24
 */

namespace Exodus4D\ESI\Client;


use lib\logging\LogInterface;
use Exodus4D\ESI\Lib\WebClient;
use Exodus4D\ESI\Lib\RequestConfig;
use Exodus4D\ESI\Lib\Stream\JsonStreamInterface;
use Exodus4D\ESI\Lib\Middleware\GuzzleJsonMiddleware;
use Exodus4D\ESI\Lib\Middleware\GuzzleLogMiddleware;
use Exodus4D\ESI\Lib\Middleware\GuzzleCacheMiddleware;
use Exodus4D\ESI\Lib\Middleware\GuzzleRetryMiddleware;
use Exodus4D\ESI\Lib\Middleware\Cache\Storage\CacheStorageInterface;
use Exodus4D\ESI\Lib\Middleware\Cache\Storage\Psr6CacheStorage;
use Exodus4D\ESI\Lib\Middleware\Cache\Strategy\CacheStrategyInterface;
use Exodus4D\ESI\Lib\Middleware\Cache\Strategy\PrivateCacheStrategy;
use Exodus4D\ESI\Config\ConfigInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\StreamInterface;

abstract class AbstractApi extends \Prefab implements ApiInterface {

    /**
     * default for: accepted response type
     * -> affects "Accept" request HTTP Header
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
     * default for: max count of parallel requests (batch request)
     */
    const DEFAULT_BATCH_CONCURRENCY                 = 5;

    /**
     * default for: auto decode responses with encoded body
     * -> checks "Content-Encoding" response HTTP Header for 'gzip' or 'deflate' value
     * @see http://docs.guzzlephp.org/en/stable/request-options.html#decode-content
     */
    const DEFAULT_DECODE_CONTENT                    = true;

    /**
     * default for: debug requests
     */
    const DEFAULT_DEBUG_REQUESTS                    = false;

    /**
     * default for: log level
     */
    const DEFAULT_DEBUG_LEVEL                       = 0;

    /**
     * error message for invalid request config
     * -> e.g. method name not callable
     */
    const ERROR_INVALID_REQUEST_CONFIG              = 'Invalid request config';

    // ================================================================================================================
    // API class properties
    // ================================================================================================================

    /**
     * WebClient instance
     * @var WebClient|null
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
     * Max count of parallel requests (batch request)
     * @var int
     */
    private $batchConcurrency                       = self::DEFAULT_BATCH_CONCURRENCY;

    /**
     * decode response body
     * @see http://docs.guzzlephp.org/en/stable/request-options.html#decode-content
     * @var bool|array|string
     */
    private $decodeContent                          = self::DEFAULT_DECODE_CONTENT;

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
     * @var bool|resource  e.g. fopen('php://stderr', 'w')
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
     * Callback function that returns new CacheItemPoolInterface
     * -> This is a PSR-6 compatible Cache pool
     *    Used as Cache Backend in this API
     *    e.g. RedisCachePool() or FilesystemCachePool()
     * @see http://www.php-cache.com
     * @var null|\Closure
     */
    private $getCachePool                           = null;

    /**
     * Callback function that returns new Log object
     * that extends logging\LogInterface class
     * @var null|callable
     */
    private $getLog                                 = null;

    /**
     * Callback function that returns true|false
     * if a $request should be logged
     * @var null|callable
     */
    private $isLoggable                             = null;

    /**
     * Endpoint config for this API
     * @var ConfigInterface
     */
    protected $config;

    // Guzzle Log Middleware config -----------------------------------------------------------------------------------

    /**
     * @see GuzzleLogMiddleware::DEFAULT_LOG_ENABLED
     * @var bool
     */
    private $logEnabled                             = GuzzleLogMiddleware::DEFAULT_LOG_ENABLED;

    /**
     * @see GuzzleLogMiddleware::DEFAULT_LOG_STATS
     * @var bool
     */
    private $logStats                               = GuzzleLogMiddleware::DEFAULT_LOG_STATS;

    /**
     * @see GuzzleLogMiddleware::DEFAULT_LOG_CACHE
     * @var bool
     */
    private $logCache                               = GuzzleLogMiddleware::DEFAULT_LOG_CACHE;

    /**
     * @see GuzzleLogMiddleware::DEFAULT_LOG_CACHE_HEADER
     * @var string
     */
    private $logCacheHeader                         = GuzzleLogMiddleware::DEFAULT_LOG_CACHE_HEADER;

    /**
     * @see GuzzleLogMiddleware::DEFAULT_LOG_ALL_STATUS
     * @var bool
     */
    private $logAllStatus                           = GuzzleLogMiddleware::DEFAULT_LOG_ALL_STATUS;

    /**
     * @see GuzzleLogMiddleware::DEFAULT_LOG_FILE
     * @var string
     */
    private $logFile                                = GuzzleLogMiddleware::DEFAULT_LOG_FILE;

    // Guzzle Cache Middleware config ---------------------------------------------------------------------------------

    /**
     * @see GuzzleCacheMiddleware::DEFAULT_CACHE_ENABLED
     * @var bool
     */
    private $cacheEnabled                           = GuzzleCacheMiddleware::DEFAULT_CACHE_ENABLED;

    /**
     * @see GuzzleCacheMiddleware::DEFAULT_CACHE_DEBUG
     * @var bool
     */
    private $cacheDebug                             = GuzzleCacheMiddleware::DEFAULT_CACHE_DEBUG;

    /**
     * @see GuzzleCacheMiddleware::DEFAULT_CACHE_DEBUG_HEADER
     * @var string
     */
    private $cacheDebugHeader                       = GuzzleCacheMiddleware::DEFAULT_CACHE_DEBUG_HEADER;

    // Guzzle Retry Middleware config ---------------------------------------------------------------------------------

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_ENABLED
     * @var bool
     */
    private $retryEnabled                           = GuzzleRetryMiddleware::DEFAULT_RETRY_ENABLED;

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_MAX_ATTEMPTS
     * @var int
     */
    private $retryMaxAttempts                       = GuzzleRetryMiddleware::DEFAULT_RETRY_MAX_ATTEMPTS;

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_MULTIPLIER
     * @var float
     */
    private $retryMultiplier                        = GuzzleRetryMiddleware::DEFAULT_RETRY_MULTIPLIER;

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_ON_TIMEOUT
     * @var bool
     */
    private $retryOnTimeout                         = GuzzleRetryMiddleware::DEFAULT_RETRY_ON_TIMEOUT;

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_ON_STATUS
     * @var array
     */
    private $retryOnStatus                          = GuzzleRetryMiddleware::DEFAULT_RETRY_ON_STATUS;

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_EXPOSE_RETRY_HEADER
     * @var bool
     */
    private $retryExposeRetryHeader                 = GuzzleRetryMiddleware::DEFAULT_RETRY_EXPOSE_RETRY_HEADER;

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_LOG_ERROR
     * @var bool
     */
    private $retryLogError                          = GuzzleRetryMiddleware::DEFAULT_RETRY_LOG_ERROR;

    /**
     * @see GuzzleRetryMiddleware::DEFAULT_RETRY_LOG_FILE
     * @var string
     */
    private $retryLogFile                           = GuzzleRetryMiddleware::DEFAULT_RETRY_LOG_FILE;

    // ================================================================================================================
    // API class methods
    // ================================================================================================================

    /**
     * Api constructor.
     * @param string $url
     */
    public function __construct(string $url){
        $this->setUrl($url);
    }

    /**
     * @return WebClient
     */
    protected function getClient() : WebClient {
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
     * @param int $batchConcurrency
     */
    public function setBatchConcurrency(int $batchConcurrency = self::DEFAULT_BATCH_CONCURRENCY){
        $this->batchConcurrency = $batchConcurrency;
    }

    /**
     * @param float $readTimeout
     */
    public function setReadTimeout(float $readTimeout = self::DEFAULT_READ_TIMEOUT){
        $this->readTimeout = $readTimeout;
    }

    /**
     * @param array|bool|string $decodeContent
     */
    public function setDecodeContent($decodeContent = self::DEFAULT_DECODE_CONTENT){
        $this->decodeContent = $decodeContent;
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
     * @param bool|resource $debugRequests
     */
    public function setDebugRequests($debugRequests = self::DEFAULT_DEBUG_REQUESTS){
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
     * set a callback that returns instance of
     * @param \Closure $cachePool
     */
    public function setCachePool(\Closure $cachePool){
        $this->getCachePool = $cachePool;
    }

    /**
     * set a callback that returns an new Log object that implements LogInterface
     * @param \Closure $newLog
     */
    public function setNewLog(\Closure $newLog){
        $this->getLog = $newLog;
    }

    /**
     * set a callback that returns true/false, param: ResponseInterface
     * @param \Closure $isLoggable
     */
    public function setIsLoggable(\Closure $isLoggable){
        $this->isLoggable = $isLoggable;
    }

    /**
     * GuzzleLogMiddleware config
     * @param bool $logEnabled
     */
    public function setLogEnabled(bool $logEnabled = GuzzleLogMiddleware::DEFAULT_LOG_ENABLED){
        $this->logEnabled = $logEnabled;
    }

    /**
     * GuzzleLogMiddleware config
     * @param bool $logStats
     */
    public function setLogStats(bool $logStats = GuzzleLogMiddleware::DEFAULT_LOG_STATS){
        $this->logStats = $logStats;
    }

    /**
     * GuzzleLogMiddleware config
     * @param bool $logCache
     */
    public function setLogCache(bool $logCache = GuzzleLogMiddleware::DEFAULT_LOG_CACHE){
        $this->logCache = $logCache;
    }

    /**
     * GuzzleLogMiddleware config
     * @param string $logCacheHeader
     */
    public function setLogCacheHeader(string $logCacheHeader = GuzzleLogMiddleware::DEFAULT_LOG_CACHE_HEADER){
        $this->logCacheHeader = $logCacheHeader;
    }

    /**
     * @param bool $logAllStatus
     */
    public function setLogAllStatus(bool $logAllStatus = GuzzleLogMiddleware::DEFAULT_LOG_ALL_STATUS){
        $this->logAllStatus = $logAllStatus;
    }

    /**
     * GuzzleLogMiddleware config
     * @param string $logFile
     */
    public function setLogFile(string $logFile = GuzzleLogMiddleware::DEFAULT_LOG_FILE){
        $this->logFile = $logFile;
    }

    /**
     * GuzzleCacheMiddleware
     * @param bool $cacheEnabled
     */
    public function setCacheEnabled(bool $cacheEnabled = GuzzleCacheMiddleware::DEFAULT_CACHE_ENABLED){
        $this->cacheEnabled = $cacheEnabled;
    }

    /**
     * GuzzleCacheMiddleware config
     * @param bool $cacheDebug
     */
    public function setCacheDebug(bool $cacheDebug = GuzzleCacheMiddleware::DEFAULT_CACHE_DEBUG){
        $this->cacheDebug = $cacheDebug;
    }

    /**
     * GuzzleCacheMiddleware config
     * @param string $cacheDebugHeader
     */
    public function setCacheDebugHeader(string $cacheDebugHeader = GuzzleCacheMiddleware::DEFAULT_CACHE_DEBUG_HEADER){
        $this->cacheDebugHeader = $cacheDebugHeader;
    }

    /**
     * @param bool $retryEnabled
     */
    public function setRetryEnabled(bool $retryEnabled = GuzzleRetryMiddleware::DEFAULT_RETRY_ENABLED){
        $this->retryEnabled = $retryEnabled;
    }

    /**
     * GuzzleRetryMiddleware config
     * @param string $logFile
     */
    public function setRetryLogFile(string $logFile = GuzzleRetryMiddleware::DEFAULT_RETRY_LOG_FILE){
        $this->retryLogFile = $logFile;
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
     * @return int
     */
    public function getBatchConcurrency() : int {
        return $this->batchConcurrency;
    }

    /**
     * @return array|bool|string
     */
    public function getDecodeContent(){
        return $this->decodeContent;
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
     * @return bool|resource
     */
    public function getDebugRequests(){
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
     * @return \Closure|null
     */
    public function getCachePool() : ?\Closure {
        return $this->getCachePool;
    }

    /**
     * @return callable|null
     */
    public function getNewLog() : ?\Closure {
        return $this->getLog;
    }

    /**
     * @return callable|null
     */
    public function getIsLoggable() : ?callable {
        return $this->isLoggable;
    }

    /**
     * log callback function
     * @return \Closure
     */
    protected function log() : \Closure {
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
     * @return WebClient
     */
    protected function initClient() : WebClient {
        return new WebClient(
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
            'decode_content'    => $this->getDecodeContent(),
            'proxy'             => $this->getProxy(),
            'verify'            => $this->getVerify(),
            'debug'             => $this->getDebugRequests(),
            'headers'           => [
                'User-Agent'    => $this->getUserAgent()
            ],

            // custom config
            'get_cache_pool'    => $this->getCachePool()    // make cachePool available in Middlewares
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
            $stack->push(GuzzleJsonMiddleware::factory(), 'json');
        }

        // error log middleware logs all request errors
        // -> add somewhere to stack BOTTOM so that it runs at the end catches errors from previous middlewares
        $stack->push(GuzzleLogMiddleware::factory($this->getLogMiddlewareConfig()), 'log');

        // cache responses based on the response Headers and cache configuration
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
            'log_enabled'               => $this->logEnabled,
            'log_stats'                 => $this->logStats,
            'log_cache'                 => $this->logCache,
            'log_cache_header'          => $this->logCacheHeader,
            'log_5xx'                   => true,
            'log_4xx'                   => true,
            'log_all_status'            => $this->logAllStatus,
            'log_off_status'            => [420],                   // error rate limit -> logged by other middleware
            'log_loggable_callback'     => $this->getIsLoggable(),
            'log_callback'              => $this->log(),
            'log_file'                  => $this->logFile
        ];
    }

    /**
     * get configuration for GuzzleCacheMiddleware Middleware
     * @return array
     */
    protected function getCacheMiddlewareConfig() : array {
        return [
            'cache_enabled'             => $this->cacheEnabled,
            'cache_debug'               => $this->cacheDebug,
            'cache_debug_header'        => $this->cacheDebugHeader
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
        if(is_callable($this->getCachePool) && !is_null($cachePool = ($this->getCachePool)())){
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
            'max_retry_attempts'        => $this->retryMaxAttempts,
            'default_retry_multiplier'  => $this->retryMultiplier,
            'retry_on_status'           => $this->retryOnStatus,
            'retry_on_timeout'          => $this->retryOnTimeout,
            'expose_retry_header'       => $this->retryExposeRetryHeader,

            'retry_log_error'           => $this->retryLogError,
            'retry_loggable_callback'   => $this->getIsLoggable(),
            'retry_log_callback'        => $this->log(),
            'retry_log_file'            => $this->retryLogFile
        ];
    }

    /**
     * get config for API request call from config
     * @param string $requestHandler
     * @param mixed  ...$handlerParams
     * @return RequestConfig|null
     */
    protected function getRequestConfig(string $requestHandler, ...$handlerParams) : ?RequestConfig {
        if(is_callable([$this, $requestHandler])){
            return call_user_func_array([$this, $requestHandler], $handlerParams);
        }
        return null;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return JsonStreamInterface|StreamInterface|null
     */
    protected function request(string $method, string $uri, array $options = []) : ?StreamInterface {
        $body = null;

        try{
            $request = WebClient::newRequest($method, $uri);
            $response = $this->getClient()->send($request, $options);
            $body = $response->getBody();
        }catch(TransferException $e){
            // Base Exception of Guzzle errors
            // -> this includes "expected" errors like 4xx responses (ClientException)
            //    and "unexpected" errors like cURL fails (ConnectException)...
            // -> error is already logged by LogMiddleware
            $body = WebClient::newErrorResponse($e, $this->getAcceptType())->getBody();
        }catch(\Exception $e){
            // Hard fail! Any other type of error
            // -> e.g. RuntimeException,...
            $body = WebClient::newErrorResponse($e, $this->getAcceptType())->getBody();
        }

        return $body;
    }

    public function send(string $requestHandler, ...$handlerParams){
        $bodyContent = null;

        if($requestConfig = $this->getRequestConfig($requestHandler, ...$handlerParams)){
            try{
                $response = $this->getClient()->send($requestConfig->getRequest(), $requestConfig->getOptions());
            }catch(TransferException $e){
                // Base Exception of Guzzle errors
                // -> this includes "expected" errors like 4xx responses (ClientException)
                //    and "unexpected" errors like cURL fails (ConnectException)...
                // -> error is already logged by LogMiddleware
                $response = WebClient::newErrorResponse($e, $this->getAcceptType());
            }catch(\Exception $e){
                // Hard fail! Any other type of error
                // -> e.g. RuntimeException,...
                $response = WebClient::newErrorResponse($e, $this->getAcceptType());
            }

            $body = $response->getBody();
            $bodyContent = $body->getContents();
        }

        return $bodyContent;
    }



    /**
     * send batch requests (parallel async requests)
     * @param array $configs
     * @return array
     */
    public function sendBatch(array $configs) : array {

        /**
         * @var RequestConfig[] $requestConfigs
         */
        $requestConfigs = array_map(function(array $config){
            if($requestConfig = $this->getRequestConfig(...$config)){
                return $requestConfig;
            }
            // invalid config
            throw new \InvalidArgumentException(self::ERROR_INVALID_REQUEST_CONFIG);
        }, $configs);

        // $requests must be 'Traversable of Promises'
        // So we’ll create a generator method which will only start the async request when the promise is grabbed
        $requests = (function() use ($requestConfigs) {
            foreach($requestConfigs as $requestConfig){
                // don't forget using generator
                yield function() use ($requestConfig) {
                    return $this->getClient()->sendAsync(
                        $requestConfig->getRequest(),
                        $requestConfig->getOptions()
                    );
                };
            }
        })();

        // run requests async (parallel)
        // -> but wait()´s until all requests are either "fulfilled" or "rejected"
        $results = $this->getClient()->runBatch($requests, [
            'concurrency' => $this->getBatchConcurrency()
        ]);

        return array_map(function($result, $key) use ($requestConfigs) {
            // check result for valid responses
            // -> wrap rejected requests into errorResponses
            if($result instanceof Response){
                $response = $result;
            }elseif($result instanceof \Exception){
                $response = WebClient::newErrorResponse($result, $this->getAcceptType());
            }else{
                // invalid result type. Hard fail!
                $response = WebClient::newErrorResponse(
                    new \InvalidArgumentException('Invalid result type: ' . gettype($result)),
                    $this->getAcceptType()
                );
            }

            $body = $response->getBody();
            $bodyContent = $body->getContents();

            // call custom formatter for current $result (same $key)
            return is_callable($formatter = $requestConfigs[$key]->getFormatter()) ? $formatter($bodyContent) : $bodyContent;
        }, $results, array_keys($results));
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
     * get Config instance for this API
     * @return ConfigInterface
     */
    abstract protected function getConfig() : ConfigInterface;
}