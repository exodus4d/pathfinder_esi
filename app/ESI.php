<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 16:37
 */

namespace Exodus4D\ESI;

class ESI implements ApiInterface {

    const ESI_URL                               = 'https://esi.tech.ccp.is';
    const ESI_CHARACTERS_LOCATION               = '/characters/%s/location/';

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

    public function getCharacterData($characterId){
        $url = 'https://esi.tech.ccp.is/latest/characters/1946320202/?datasource=tranquility';

        $characterData = [];

        $requestOptions = [
            'timeout' => 4,
            'method' => 'GET',
            'user_agent' => $this->getUserAgent(),
            'header' => [
                'Accept: application/json'
            ]
        ];

        $response = namespace\Lib\WebClient::instance()->request($url, $requestOptions);

        var_dump($response);
        var_dump('end');
        die();


        return $characterData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterLocationData( $characterId, $accessToken){
        $url = 'https://esi.tech.ccp.is/latest/characters/1946320202/location/?datasource=tranquility';

        $locationData = [];

        $requestOptions = [
            'timeout' => 4,
            'method' => 'GET',
            'user_agent' => $this->getUserAgent(),
            'header' => [
                'Accept: application/json'
            ]
        ];

        $requestOptions['header'][] = 'Authorization: Bearer ' . $accessToken;

        var_dump($requestOptions);

        $response = namespace\Lib\WebClient::instance()->request($url, $requestOptions);

        var_dump($response);




        return $locationData;
    }
}