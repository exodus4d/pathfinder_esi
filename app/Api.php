<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:24
 */

namespace Exodus4D\ESI;


use Exodus4D\ESI\Lib\Stream\JsonStream;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleRetry\GuzzleRetryMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * default for: log level
     */
    const DEFAULT_DEBUG_LEVEL                       = 0;

    /**
     * default for: debug requests
     */
    const DEFAULT_DEBUG_REQUESTS                    = false;

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

    // API class properties ===========================================================================================

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
     * @var float
     */
    private $timeout                                = self::DEFAULT_TIMEOUT;

    /**
     * Timeout for server connect in seconds
     * @var float
     */
    private $connectTimeout                         = self::DEFAULT_CONNECT_TIMEOUT;

    /**
     * Read timeout for Streams
     * Should be less than "default_socket_timeout" PHP ini
     * @var float
     */
    private $readTimeout                            = self::DEFAULT_READ_TIMEOUT;

    /**
     * Debug level for API requests
     * @var int
     */
    private $debugLevel                             = self::DEFAULT_DEBUG_LEVEL;

    /**
     * Debug requests if enabled
     * @var bool
     */
    private $debugRequests                          = self::DEFAULT_DEBUG_REQUESTS;

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
     * @param int $debugLevel
     */
    public function setDebugLevel(int $debugLevel = self::DEFAULT_DEBUG_LEVEL){
        $this->debugLevel = $debugLevel;
    }

    /**
     * debug requests
     * https://guzzle.readthedocs.io/en/latest/request-options.html#debug
     * @param bool $debugRequests
     */
    public function setDebugRequests(bool $debugRequests = self::DEFAULT_DEBUG_REQUESTS){
        $this->debugRequests  = $debugRequests;
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
     * @return int
     */
    public function getDebugLevel() : int {
        return $this->debugLevel;
    }

    /**
     * @return bool
     */
    public function getDebugRequests() : bool {
        return $this->debugRequests;
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
            $this->getClientMiddleware()
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
            'debug'             => $this->getDebugRequests(),
            'headers'           => [
                'User-Agent'    => $this->getUserAgent()
            ]
        ];
    }

    /**
     * get all "Middleware" used in GuzzleHttp\HandlerStack() config
     * for this GuzzleHttp\Client()
     * @return callable[]
     */
    protected function getClientMiddleware() : array {
        $middleware = [];

        $middleware['retry'] = GuzzleRetryMiddleware::factory($this->getRetryMiddlewareConfig());

        if($this->getAcceptType() == 'json'){
            // set "Accept" header json
            $middleware['request_json'] = Middleware::mapRequest(function(RequestInterface $request){
                return $request->withHeader('Accept', 'application/json');
            });

            // decode Json response body
            $middleware['response_json'] = Middleware::mapResponse(function(ResponseInterface $response){
                $jsonStream = new JsonStream($response->getBody());
                return $response->withBody($jsonStream);
            });
        }

        return $middleware;
    }

    /**
     * get configuration for Retry Middleware
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

    protected function isLoggableError(TransferException $e) : bool {
        return true;
    }

    protected function logError(TransferException $e){
        if($this->isLoggableError($e)){

        }
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
     * format Header data array with ['name' => 'value',...] into plain array:
     * -> ['name: value', ...]
     * @param array $headers
     * @return array
     */
    protected function formatHeaders(array $headers) : array {
        $combine = function($oldVal, $key, $val){
            return trim($key) . ': ' . trim($val);
        };

        return array_map($combine, range(0, count($headers) - 1), array_keys($headers), array_values($headers));
    }

    /**
     * get error response as return object for failed requests
     * @param string $errorMessage
     * @return \stdClass
     */
    protected function getErrorResponse(string $errorMessage) : \stdClass {
        $body = (object)[];
        $body->error = $errorMessage;
        return $body;
    }

    protected function request(string $method, string $uri, array $options = [], array $additionalOptions = []){
        var_dump('start ---------------------------------');
        var_dump('$method : ' . $method);
        var_dump('$uri : ' . $uri);
        //var_dump('$options');
        //var_dump($options);
        //var_dump('$additionalOptions');
        //var_dump($additionalOptions);

        $body = null;


        try{
            // get new request
            $request = $this->getClient()->newRequest($method, $uri);

            /**
             * @var $response Response
             */
            $response = $this->getClient()->send($request, $options);

            $bodyStream = $response->getBody();

            $body = $bodyStream->getContents();

            var_dump('response: ----');
            var_dump('statuscode: ' . $response->getStatusCode());
            var_dump('getReasonPhrase: ' . $response->getReasonPhrase());


        }catch(ConnectException $e){
            var_dump('ConnectException --------');
            var_dump($e->getCode() . ': ' . $e->getMessage());
        }catch(ClientException $e){
            var_dump('ClientException --------');
            // 4xx response (e.g. 404 URL not found)
            var_dump($e->getCode() . ': ' . $e->getMessage());
            $body = $this->getErrorResponse($e->getMessage());
        }catch(ServerException $e){
            var_dump('ServerException --------');
            var_dump($e->getCode() . ': ' . $e->getMessage());
        }catch(BadResponseException $e){
            var_dump('BadResponseException --------');
            var_dump($e->getCode() . ': ' . $e->getMessage());
        }catch(TooManyRedirectsException $e){
            var_dump('TooManyRedirectsException --------');
            var_dump($e->getCode() . ': ' . $e->getMessage());
        }catch(RequestException $e){
            var_dump('RequestException --------');
            // hard fail! e.g. cURL errors (connection timeout, DNS errors, etc.)
            var_dump($e->getCode() . ': ' . $e->getMessage());
            $body = $this->getErrorResponse($e->getMessage());
            // $e->getHandlerContext(); // cURL errors
        }catch(TransferException $e){
            var_dump('TransferException --------');
            // hard fail! Base Exception of "Guzzle" errors
            // -> All Errors should already be catch above
            var_dump($e->getCode() . ': ' . $e->getMessage());
            $body = $this->getErrorResponse($e->getMessage());
        }catch(\Exception $e){
            var_dump('Exception --------');
            // hard fail! Any other type of error
            var_dump($e->getCode() . ': ' . $e->getMessage());
        }

        var_dump($body);

        die('END');

/*
        var_dump('request: ----');
        var_dump($request->getHeaders());
        $response = $this->getClient()->send($request, $options);

        //var_dump('request final: ----');
        //var_dump($mockHandler->getLastRequest()->getHeaders());
        //$response = $this->getClient()->request($method, $uri);

        var_dump('response: ----');
        var_dump($response->getStatusCode());


        //var_dump($response->getBody()->getContents());
        $test = $response->getBody();
        var_dump($test->getContents());


        var_dump($response->getReasonPhrase());
        var_dump($response->getHeaders());
*/
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