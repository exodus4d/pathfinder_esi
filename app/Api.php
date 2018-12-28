<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:24
 */

namespace Exodus4D\ESI;

abstract class Api implements ApiInterface {

    /**
     * default for: request timeout
     */
    const DEFAULT_TIMEOUT                           = 3;

    /**
     * default for: log level
     */
    const DEFAULT_DEBUG_LEVEL                       = 0;

    /**
     * default for: log any request to log file
     */
    const DEFAULT_DEBUG_LOG_REQUESTS                = false;


    private $url                                    = '';

    private $timeout                                = self::DEFAULT_TIMEOUT;

    private $debugLevel                             = self::DEFAULT_DEBUG_LEVEL;

    private $debugLogRequests                       = self::DEFAULT_DEBUG_LOG_REQUESTS;

    private $userAgent                              = '';

    /**
     * @param string $url
     */
    public function setUrl(string $url){
        $this->url = $url;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout = self::DEFAULT_TIMEOUT){
        $this->timeout = $timeout;
    }

    /**
     * @param int $debugLevel
     */
    public function setDebugLevel(int $debugLevel = self::DEFAULT_DEBUG_LEVEL){
        $this->debugLevel = $debugLevel;
    }

    /**
     * log any requests to log file
     * @param bool $logRequests
     */
    public function setDebugLogRequests(bool $logRequests = self::DEFAULT_DEBUG_LOG_REQUESTS){
        $this->debugLogRequests = $logRequests;
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
     * @return int
     */
    public function getTimeout() : int {
        return $this->timeout;
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
    public function getDebugLogRequests() : bool {
        return $this->debugLogRequests;
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
     * @param string $method
     * @param string $url
     * @param array $options
     * @param array $additionalOptions
     * @return mixed|null
     */
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
    }
}