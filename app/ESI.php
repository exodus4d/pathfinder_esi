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

    public function getCharacterAffiliationData(array $characterIds): array {
        $url = 'https://esi.tech.ccp.is/latest/characters/affiliation/?datasource=tranquility';

        $characterAffiliationData = [];

        $requestOptions = [
            'timeout' => 4,
            'method' => 'POST',
            'user_agent' => $this->getUserAgent(),
            'header' => [
                'Accept: application/json'
            ],
            'content' => json_encode($characterIds, JSON_UNESCAPED_SLASHES)
        ];

        $response = namespace\Lib\WebClient::instance()->request($url, $requestOptions);

        if( !empty($response) ){
            foreach((array)$response as $affiliationData){
                $characterAffiliationData[] = (new namespace\Mapper\CharacterAffiliation($affiliationData))->getData();
            }
        }

        return $characterAffiliationData;
    }

    public function getCharacterData(int $characterId){
        $url = 'https://esi.tech.ccp.is/latest/characters/' . $characterId . '/?datasource=tranquility';

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
        if( !empty($response) ){
            $esiCharacterData = (new namespace\Mapper\Character($response))->getData();

            var_dump('$esiCharacterData:');
            var_dump($esiCharacterData);
        }

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
        $url = 'https://esi.tech.ccp.is/latest/characters/' . $characterId . '/location/?datasource=tranquility';

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

        $response = namespace\Lib\WebClient::instance()->request($url, $requestOptions);

        if( !empty($response) ){
            $locationData = (new namespace\Mapper\Location($response))->getData();
        }


        return $locationData;
    }

    /**
     * @param int $corporationId
     * @return array
     */
    public function getCorporationData(int $corporationId): array {
        $url = 'https://esi.tech.ccp.is/latest/corporations/' . $corporationId . '/?datasource=tranquility';

        $corporationData = [];

        $requestOptions = [
            'timeout' => 4,
            'method' => 'GET',
            'user_agent' => $this->getUserAgent(),
            'header' => [
                'Accept: application/json'
            ]
        ];

        $response = namespace\Lib\WebClient::instance()->request($url, $requestOptions);

        if( !empty($response) ){
            $corporationData = (new namespace\Mapper\Corporation($response))->getData();
            $corporationData['id'] = $corporationId;
        }

        return $corporationData;
    }

    /**
     * @param int $allianceId
     * @return array
     */
    public function getAllianceData(int $allianceId): array {
        $url = 'https://esi.tech.ccp.is/latest/alliances/' . $allianceId . '/?datasource=tranquility';

        $allianceData = [];

        $requestOptions = [
            'timeout' => 4,
            'method' => 'GET',
            'user_agent' => $this->getUserAgent(),
            'header' => [
                'Accept: application/json'
            ]
        ];

        $response = namespace\Lib\WebClient::instance()->request($url, $requestOptions);

        if( !empty($response) ){
            $allianceData = (new namespace\Mapper\Alliance($response))->getData();
            $allianceData['id'] = $allianceId;
        }

        return $allianceData;
    }
}