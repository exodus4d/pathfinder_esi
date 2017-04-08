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

    const ESI_TIMEOUT                           = 4;

    const ERROR_ESI_URL                         = 'Invalid ESI API url. %s';
    const ERROR_ESI_METHOD                      = 'Invalid ESI API HTTP request method. %s: %s';

    private $esiUrl                             = '';
    private $esiDatasource                      = '';
    private $userAgent                          = '';

    /**
     * ESI constructor.
     */
    public function __construct(){
    }

    /**
     * @param string $esiUrl
     */
    public function setEsiUrl(string $esiUrl){
        $this->esiUrl = $esiUrl;
    }

    /**
     * @param string $esiDatasource
     */
    public function setEsiDatasource(string $esiDatasource){
        $this->esiDatasource = $esiDatasource;
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
    public function getEsiUrl(): string{
        return $this->esiUrl;
    }

    /**
     * @return string
     */
    public function getEsiDatasource(): string{
        return $this->esiDatasource;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string{
        return $this->userAgent;
    }

    /**
     * @param array $characterIds
     * @return array
     */
    public function getCharacterAffiliationData(array $characterIds): array {
        $url = $this->getEndpointURL(['characters', 'affiliation', 'POST']);
        $characterAffiliationData = [];

        $additionalOptions = [
            'content' => $characterIds
        ];
        $response = $this->request($url, 'POST', '', $additionalOptions);

        if( !empty($response) ){
            foreach((array)$response as $affiliationData){
                $characterAffiliationData[] = (new namespace\Mapper\CharacterAffiliation($affiliationData))->getData();
            }
        }

        return $characterAffiliationData;
    }

    /**
     * @param int $characterId
     * @return array
     */
    public function getCharacterData(int $characterId): array{
        $url = $this->getEndpointURL(['characters', 'GET'], [$characterId]);
        $characterData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $characterData = (new namespace\Mapper\Character($response))->getData();
            $characterData['id'] = $characterId;
        }

        return $characterData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterLocationData(int $characterId, string $accessToken, array $additionalOptions = []): array{
        $url = $this->getEndpointURL(['characters', 'location', 'GET'], [$characterId]);
        $locationData = [];
        $response = $this->request($url, 'GET', $accessToken, $additionalOptions);

        if( !empty($response) ){
            $locationData = (new namespace\Mapper\Location($response))->getData();
        }

        return $locationData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterShipData(int $characterId, string $accessToken, array $additionalOptions = []): array{
        $url = $this->getEndpointURL(['characters', 'ship', 'GET'], [$characterId]);
        $shipData = [];
        $response = $this->request($url, 'GET', $accessToken, $additionalOptions);

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
        $url = $this->getEndpointURL(['corporations', 'GET'], [$corporationId]);
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
        $url = $this->getEndpointURL(['alliances', 'GET'], [$allianceId]);
        $allianceData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $allianceData = (new namespace\Mapper\Alliance($response))->getData();
            $allianceData['id'] = $allianceId;
        }

        return $allianceData;
    }

    public function isNpcCorporation(int $corporationId): bool {
        $isNPC = false;

        $npcCorporations = $this->getNpcCorporations();

        return $isNPC;
    }

    protected function getNpcCorporations(): array{
        $url = $this->getEndpointURL(['corporations', 'npccorps', 'GET']);

        $response = $this->request($url, 'GET');

        var_dump($response);

        return [];
    }

    /**
     * get/build endpoint URL
     * @param array $path
     * @param array $params
     * @return string
     */
    protected function getEndpointURL($path = [], $params = []): string{
        $url = $this->getEsiUrl() . Config\ESIConf::getEndpoint($path, $params);

        if( !empty($datasource = $this->getEsiDatasource()) ){
            $params = [
                'datasource' => $datasource
            ];
            $url .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986 );
        }

        return $url;
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $accessToken
     * @param array $additionalOptions
     * @return null|array|\stdClass
     */
    protected function request(string $url, string $method = 'GET', string $accessToken = '', array $additionalOptions = []) {
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

                if( !empty($additionalOptions['content']) ){
                    $requestOptions['content'] =  json_encode($additionalOptions['content'], JSON_UNESCAPED_SLASHES);
                    unset($additionalOptions['content']);
                }

                $responseBody = $webClient->request($url, $requestOptions, $additionalOptions);
            }else{
                $webClient->getLogger('err_server')->write(sprintf(self::ERROR_ESI_METHOD, $method, $url));
            }
        }else{
            $webClient->getLogger('err_server')->write(sprintf(self::ERROR_ESI_URL, $url));
        }

        return $responseBody;
    }
}