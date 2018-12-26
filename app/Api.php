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

    protected function request(string $method, string $url, array $options = [], array $additionalOptions = []){
        $method = strtoupper($method);

        $requestOptions = [
            'timeout' => $this->getTimeout(),
            'method' => $method,
            'user_agent' => $this->getUserAgent(),
            'header' => [
                'Accept: application/json',
                'Expect:',
                'Content-Type' => 'abvddd',
            ]
        ];

        var_dump('LALA----');
        var_dump($requestOptions);
        var_dump($options);

        var_dump('Merge----');
        var_dump(array_merge_recursive($requestOptions, $options));
        var_dump($this->array_merge_recursive_distinct($requestOptions, $options));
    }

    function array_merge_recursive_distinct(array &$array1, array &$array2){
        $merged = $array1;
        foreach ($array2 as $key => &$value){
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            }else{
                $merged[$key] = $value;
            }
        }
        return $merged;
    }
}