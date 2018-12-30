<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:24
 */

namespace Exodus4D\ESI;


use Exodus4D\ESI\Lib\Stream\JsonStream;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
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

        if($this->getAcceptType() == 'json'){
            // set "Accept" header json
            $middleware['request_json'] = Middleware::mapRequest(function(ResponseInterface $request){
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

    protected function request(string $method, string $url, array $options = [], array $additionalOptions = []){
        var_dump('start ---------------------------------');
        var_dump('$method : ' . $method);
        var_dump('$url : ' . $url);
        var_dump('$options');
        var_dump($options);
        var_dump('$additionalOptions');
        var_dump($additionalOptions);
        /**
         * @var $response Response
         */
        $request = $this->getClient()->newRequest($method, $url);
        var_dump('request: ----');
        var_dump($request->getHeaders());
        $response = $this->getClient()->send($request);

        var_dump('request final: ----');
        //var_dump($mockHandler->getLastRequest()->getHeaders());
        //$response = $this->getClient()->request($method, $url);

        var_dump('response: ----');
        var_dump($response->getStatusCode());
        var_dump($response->getBody()->getContents());
        var_dump($response->getReasonPhrase());
        var_dump($response->getHeaders());
        die();
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