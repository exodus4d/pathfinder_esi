<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 16:37
 */

namespace Exodus4D\ESI;

use Exodus4D\ESI\Config;

class ESI implements ApiInterface {

    const ESI_TIMEOUT                           = 3;
    const ESI_URL                               = 'https://esi.tech.ccp.is';
    const ESI_CHARACTERS_LOCATION               = '/characters/%s/location/';

    const ERROR_ESI_URL                         = 'Invalid ESI API url. %s';
    const ERROR_ESI_METHOD                      = 'Invalid ESI API HTTP request method. %s: %s';

    private $f3 = null;
    private $userAgent = '';

    /**
     * ESI constructor.
     * @param \Base $f3
     */
    public function __construct(\Base $f3){
        $this->f3 = $f3;
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

        $response = $this->request($url, 'GET');
var_dump('getCharacterData');
var_dump($response);
        if( !empty($response) ){
            $characterData = (new namespace\Mapper\Character($response))->getData();
        }

        return $characterData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterLocationData(int $characterId, string $accessToken): array{

        $url = Config\ESIConf::getEndpointURL(['characters', 'location', 'GET'], [$characterId]);

        $url = 'https://esi.tech.ccp.is/latest/characters/' . $characterId . '/location/?datasource=tranquility';

        $locationData = [];

        $response = $this->request($url, 'GET', $accessToken);

        if( !empty($response) ){
            $locationData = (new namespace\Mapper\Location($response))->getData();
        }

        var_dump('getCharacterLocationData');
        var_dump($locationData);



        return $locationData;
    }

    /**
     * @param $characterId
     * @param $accessToken
     * @return array
     */
    public function getCharacterShipData(int $characterId, string $accessToken): array{
        $url = 'https://esi.tech.ccp.is/latest/characters/' . $characterId . '/ship/?datasource=tranquility';

        $shipData = [];

        $response = $this->request($url, 'GET', $accessToken);

        if( !empty($response) ){
            $shipData = (new namespace\Mapper\Ship($response))->getData();
        }

        return $shipData;
    }

    /**
     * @param int $corporationId
     * @return array
     */
    public function getCorporationData(int $corporationId): array {
        $url = 'https://esi.tech.ccp.is/latest/corporations/' . $corporationId . '/?datasource=tranquility';

        $corporationData = [];

        $response = $this->request($url, 'GET');

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

        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $allianceData = (new namespace\Mapper\Alliance($response))->getData();
            $allianceData['id'] = $allianceId;
        }

        return $allianceData;
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $accessToken
     * @param array $content
     * @return null|array|\stdClass
     */
    protected function request(string $url, string $method = 'GET', string $accessToken = '', array $content = []) {
        $responseBody = null;
        $method = strtoupper($method);

        $webClient = namespace\Lib\WebClient::instance();

        if( \Audit::instance()->url($url) ){
            if( $webClient->checkRequestMethod($method) ){
                $requestOptions = [
                    'timeout' => self::ESI_TIMEOUT,
                    'method' => $method,
                    'user_agent' => $this->getUserAgent(),
                    'header' => [
                        'Accept: application/json'
                    ]
                ];

                // add auth token if available (required for some endpoints)
                if( !empty($accessToken) ){
                    $requestOptions['header'][] = 'Authorization: Bearer ' . $accessToken;
                }

                $responseBody = $webClient->request($url, $requestOptions);
            }else{
                $webClient->getLogger('err_server')->write(sprintf(self::ERROR_ESI_METHOD, $method, $url));
            }
        }else{
            $webClient->getLogger('err_server')->write(sprintf(self::ERROR_ESI_URL, $url));
        }

        return $responseBody;
    }
}