<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 16:37
 */

namespace Exodus4D\ESI;

class ESI implements ApiInterface {

    private $userAgent = '';

    /**
     * ESI constructor.
     */
    public function __construct(){
    }

    /**
     * @return string
     */
    public function getUserAgent(): string{
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent){
        $this->userAgent = $userAgent;
    }

    public function getCharacterLocationData($characterId, $accessToken){

        $url = 'https://esi.tech.ccp.is/latest/characters/1946320202/location/?datasource=tranquility';

        $requestOptions = [
            'timeout' => 4,
            'method' => 'GET',
            'header' => [
                'User-Agent: ' . $this->getUserAgent(),
               // 'Accept: application/json'
            ]
        ];


        $requestOptions['header'][] = 'Authorization: Bearer ' . $accessToken;

        var_dump($requestOptions);

        $response = namespace\Lib\WebClient::instance()->request($url, $requestOptions);

        return $response;
    }
}