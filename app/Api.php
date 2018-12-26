<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:24
 */

namespace Exodus4D\ESI;


abstract class Api implements ApiInterface {

    const DEFAULT_TIMEOUT = 3;

    private $url = '';

    private $timeout = self::DEFAULT_TIMEOUT;

    private $userAgent = '';

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
     * @return string
     */
    public function getUserAgent() : string {
        return $this->userAgent;
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

    protected function request(string $method, string $url, array $options = [], array $additionalOptions = []){
        $method = strtoupper($method);

        $requestOptions = [
            'timeout' => $this->getTimeout(),
            'method' => $method,
            'user_agent' => $this->getUserAgent(),
            /*
            'header' => [
                'Accept' => 'application/json',
                'Expect' => ''
            ] */
        ];

        $requestOptions = self::array_merge_recursive_distinct($requestOptions, $options);

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

        var_dump('LALA ----');
        var_dump($requestOptions);
        var_dump($options);

        var_dump('Merge ----');
        var_dump($requestOptions);

        $combine = function($val, $key, $test){
            var_dump('==');
            var_dump($val);
            var_dump($key);
            var_dump($test);
            return 11;
        };

        $test = range(0, count($requestOptions['header']));
        $testHeader = array_map($combine, $test, array_keys($requestOptions['header']), $requestOptions['header']);

        var_dump('Header ----');
        var_dump($testHeader);
    }
}