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

    const ESI_TIMEOUT                               = 4;

    const ERROR_ESI_URL                             = 'Invalid ESI API url. %s';
    const ERROR_ESI_METHOD                          = 'Invalid ESI API HTTP request method. %s: %s';
    const ERROR_ESI_WAYPOINT                        = 'Could not set waypoint.';
    const ERROR_ESI_WINDOW                          = 'Could not open client window.';

    /**
     * @var string $esiUrl                          Base ESI Domain (required)
     * @var string $esiUserAgent                    User-Agent Header (required)
     * @var string $esiDatasource                   Datasource 'singularity' || 'tranquility'
     * @var string $endpointVersion                 Overwrite versioned endpoint URL (for testing)
     */
    private $esiUrl, $esiUserAgent, $esiDatasource, $endpointVersion   = '';

    /**
     * debugLevel
     * @var int
     */
    private $debugLevel = 0;

    /**
     * ESI constructor.
     */
    public function __construct(){
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url){
        $this->esiUrl = $url;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent){
        $this->esiUserAgent = $userAgent;
    }

    /**
     * @param string $datasource
     */
    public function setDatasource(string $datasource){
        $this->esiDatasource = $datasource;
    }

    /**
     * @param int $debug
     */
    public function setDebugLevel(int $debug){
        $this->debugLevel = $debug;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version){
        $this->endpointVersion = $version;
    }

    /**
     * @return string
     */
    public function getUrl(): string{
        return $this->esiUrl;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string{
        return $this->esiUserAgent;
    }

    /**
     * @return string
     */
    public function getDatasource(): string{
        return $this->esiDatasource;
    }

    /**
     * @return int
     */
    public function getDebugLevel(): int {
        return $this->debugLevel;
    }

    /**
     * @return string
     */
    public function getVersion(): string{
        return $this->endpointVersion;
    }

    /**
     * @return array
     */
    public function getServerStatus(): array{
        $url = $this->getEndpointURL(['status', 'GET']);
        $serverStatus = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $serverStatus = (new namespace\Mapper\ServerStatus($response))->getData();
        }

        return $serverStatus;
    }

    /**
     * @param array $characterIds
     * @return array
     */
    public function getCharacterAffiliationData(array $characterIds): array{
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
            if( !empty($characterData) ){
                $characterData['id'] = $characterId;
            }
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
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterOnlineData(int $characterId, string $accessToken, array $additionalOptions = []): array{
        $url = $this->getEndpointURL(['characters', 'online', 'GET'], [$characterId]);
        $onlineData = [];
        $response = $this->request($url, 'GET', $accessToken, $additionalOptions);

        if( !empty($response) ){
            $onlineData = (new namespace\Mapper\Online($response))->getData();
        }

        return $onlineData;
    }

    /**
     * @param int $corporationId
     * @return array
     */
    public function getCorporationData(int $corporationId): array{
        $url = $this->getEndpointURL(['corporations', 'GET'], [$corporationId]);
        $corporationData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $corporationData = (new namespace\Mapper\Corporation($response))->getData();
            if( !empty($corporationData) ){
                $corporationData['id'] = $corporationId;
            }
        }

        return $corporationData;
    }

    /**
     * @param int $allianceId
     * @return array
     */
    public function getAllianceData(int $allianceId): array{
        $url = $this->getEndpointURL(['alliances', 'GET'], [$allianceId]);
        $allianceData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $allianceData = (new namespace\Mapper\Alliance($response))->getData();
            if( !empty($allianceData) ){
                $allianceData['id'] = $allianceId;
            }
        }

        return $allianceData;
    }

    /**
     * @param int $systemId
     * @param string $accessToken
     * @param array $options
     * @return array
     */
    public function setWaypoint(int $systemId, string $accessToken, array $options = []): array{
        $urlParams = [
            'add_to_beginning'      => var_export( (bool)$options['addToBeginning'], true),
            'clear_other_waypoints' => var_export( (bool)$options['clearOtherWaypoints'], true),
            'destination_id'        => $systemId
        ];

        $url = $this->getEndpointURL(['ui', 'autopilot', 'waypoint', 'POST'], [], $urlParams);
        $waypointData = [];

        // need to be send in "content" vars as well! Otherwise "Content-Length" header is not send
        $additionalOptions = [
            'content' => $urlParams
        ];

        $response = $this->request($url, 'POST', $accessToken, $additionalOptions);

        // "null" === success => There is no response body send...
        if( !is_null($response) ){
            $waypointData['error'] = self::ERROR_ESI_WAYPOINT;
        }

        return $waypointData;
    }

    /**
     * @param int $corporationId
     * @param string $accessToken
     * @return array
     */
    public function getCorporationRoles(int $corporationId, string $accessToken): array{
        $url = $this->getEndpointURL(['corporations', 'roles', 'GET'], [$corporationId]);
        $rolesData = [];
        $response = $this->request($url, 'GET', $accessToken);

        if( !empty($response) ){
            foreach((array)$response as $characterRuleData){
                $rolesData[(int)$characterRuleData->character_id] = array_map('strtolower', (array)$characterRuleData->roles);
            }
        }

        return $rolesData;
    }

    /**
     * @return array
     */
    public function getRegions(): array{
        $url = $this->getEndpointURL(['universe', 'regions', 'list', 'GET']);
        $regionData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $regionData = array_unique( array_map('intval', $response) );
        }

        return $regionData;
    }

    /**
     * @param int $regionId
     * @return array
     */
    public function getRegionData(int $regionId): array{
        $url = $this->getEndpointURL(['universe', 'regions', 'GET'], [$regionId]);
        $regionData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $regionData = (new namespace\Mapper\Region($response))->getData();
        }

        return $regionData;
    }

    /**
     * @return array
     */
    public function getConstellations(): array{
        $url = $this->getEndpointURL(['universe', 'constellations', 'list', 'GET']);
        $constellationData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $constellationData = array_unique( array_map('intval', $response) );
        }

        return $constellationData;
    }

    /**
     * @param int $constellationId
     * @return array
     */
    public function getConstellationData(int $constellationId): array{
        $url = $this->getEndpointURL(['universe', 'constellations', 'GET'], [$constellationId]);
        $constellationData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $constellationData = (new namespace\Mapper\Constellation($response))->getData();
        }

        return $constellationData;
    }

    /**
     * @param array $universeIds
     * @param array $additionalOptions
     * @return array
     */
    public function getUniverseNamesData(array $universeIds, array $additionalOptions = []): array{
        $url = $this->getEndpointURL(['universe', 'names', 'POST']);
        $universeData = [];

        $additionalOptions['content'] = $universeIds;

        $response = $this->request($url, 'POST', '', $additionalOptions);

        if( !empty($response) ){
            foreach((array)$response as $data){
                switch($data->category){
                    case 'station':
                        $categoryData = (new namespace\Mapper\Station($data))->getData();
                        break;
                    case 'solar_system':
                        $categoryData = (new namespace\Mapper\System($data))->getData();
                        break;
                    case 'inventory_type':
                        $categoryData = (new namespace\Mapper\InventoryType($data))->getData();
                        break;
                    default:
                        $categoryData = [];
                }
                $universeData += $categoryData;
            }
        }

        return $universeData;
    }

    /**
     * @return array
     */
    public function getUniverseJumps(): array{
        $url = $this->getEndpointURL(['universe', 'system_jumps', 'GET']);
        $systemJumps = [];

        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            foreach((array)$response as $jumpData){
                $systemJumps[$jumpData->system_id]['jumps'] = (int)$jumpData->ship_jumps;
            }
        }

        return $systemJumps;
    }

    /**
     * @return array
     */
    public function getUniverseKills(): array{
        $url = $this->getEndpointURL(['universe', 'system_kills', 'GET']);
        $systemKills = [];

        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            foreach((array)$response as $killData){
                $systemKills[$killData->system_id] = [
                    'npc_kills' => (int)$killData->npc_kills,
                    'pod_kills' => (int)$killData->pod_kills,
                    'ship_kills' => (int)$killData->ship_kills
                ];
            }
        }

        return $systemKills;
    }

    /**
     * @return array
     */
    public function getUniverseCategories(): array{
        $url = $this->getEndpointURL(['universe', 'categories', 'list', 'GET']);
        $categoryData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $categoryData = array_unique( array_map('intval', $response) );
        }

        return $categoryData;
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getUniverseCategoryData(int $categoryId) : array {
        $url = $this->getEndpointURL(['universe', 'categories', 'GET'], [$categoryId]);
        $categoryData = [];

        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $categoryData = (new namespace\Mapper\Universe\Category($response))->getData();
            if( !empty($categoryData) ){
                $categoryData['id'] = $categoryId;
            }
        }

        return $categoryData;
    }

    /**
     * @return array
     */
    public function getUniverseGroups(): array{
        $url = $this->getEndpointURL(['universe', 'groups', 'list', 'GET']);
        $groupData = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $groupData = array_unique( array_map('intval', $response) );
        }

        return $groupData;
    }

    /**
     * @param int $groupId
     * @return array
     */
    public function getUniverseGroupData(int $groupId) : array {
        $url = $this->getEndpointURL(['universe', 'groups', 'GET'], [$groupId]);
        $groupData = [];

        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $groupData = (new namespace\Mapper\Universe\Group($response))->getData();
            if( !empty($groupData) ){
                $groupData['id'] = $groupId;
            }
        }

        return $groupData;
    }

    /**
     * @param int $structureId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getUniverseStructureData(int $structureId, string $accessToken, array $additionalOptions = []): array {
        $url = $this->getEndpointURL(['universe', 'structures', 'GET'], [$structureId]);
        $structureData = [];

        $response = $this->request($url, 'GET', $accessToken, $additionalOptions);

        if( !empty($response) ){
            $structureData = (new namespace\Mapper\Universe\Structure($response))->getData();
            if( !empty($structureData) ){
                $structureData['id'] = $structureId;
            }
        }

        return $structureData;
    }

    /**
     * @param int $typeId
     * @param array $additionalOptions
     * @return array
     */
    public function getUniverseTypesData(int $typeId, array $additionalOptions = []): array {
        $url = $this->getEndpointURL(['universe', 'types', 'GET'], [$typeId]);
        $typesData = [];
        $response = $this->request($url, 'GET', '', $additionalOptions);

        if( !empty($response) ){
            $typesData = (new namespace\Mapper\Universe\Type($response))->getData();
        }

        return $typesData;
    }

    /**
     * @param int $targetId
     * @param string $accessToken
     * @return array
     */
    public function openWindow(int $targetId, string $accessToken): array{
        $urlParams = [
            'target_id' => $targetId
        ];

        $url = $this->getEndpointURL(['ui', 'openwindow', 'information', 'POST'], [], $urlParams);
        $return = [];

        // need to be send in "content" vars as well! Otherwise "Content-Length" header is not send
        $additionalOptions = [
            'content' => $urlParams
        ];

        $response = $this->request($url, 'POST', $accessToken, $additionalOptions);

        // "null" === success => There is no response body send...
        if( !is_null($response) ){
            $return['error'] = self::ERROR_ESI_WINDOW;
        }

        return $return;
    }

    /**
     * @param int $sourceId
     * @param int $targetId
     * @param array $options
     * @return array
     */
    public function getRouteData(int $sourceId, int $targetId, array $options = []): array {
        $urlParams = [];
        if( !empty($options['avoid']) ){
            $urlParams['avoid'] = $options['avoid'];
        }
        if( !empty($options['connections']) ){
            $urlParams['connections'] = $options['connections'];
        }
        if( !empty($options['flag']) ){
            $urlParams['flag'] = $options['flag'];
        }

        $urlParams = $this->formatUrlParams($urlParams, [
            'connections' => [',', '|'],
            'avoid' => [',']
        ]);

        $url = $this->getEndpointURL(['routes', 'GET'], [$sourceId, $targetId], $urlParams);
        $routeData = [];
        $response = $this->request($url, 'GET');

        if($response->error){
            $routeData['error'] = $response->error;
        }else{
            $routeData['route'] = array_unique( array_map('intval', $response) );
        }

        return $routeData;
    }

    /**
     * @param int $corporationId
     * @return bool
     */
    public function isNpcCorporation(int $corporationId): bool{
        $npcCorporations = $this->getNpcCorporations();
        return in_array($corporationId, $npcCorporations);
    }

    /**
     * @return array
     */
    protected function getNpcCorporations(): array{
        $url = $this->getEndpointURL(['corporations', 'npccorps', 'GET']);
        $npcCorporations = [];
        $response = $this->request($url, 'GET');

        if( !empty($response) ){
            $npcCorporations = (array)$response;
        }

        return $npcCorporations;
    }

    protected function formatUrlParams(array $urlParams = [], array $format = []) : array {

        $formatter = function(&$item, $key, $params) use (&$formatter) {
            $params['depth'] = isset($params['depth']) ? ++$params['depth'] : 0;
            $params['firstKey'] = isset($params['firstKey']) ? $params['firstKey'] : $key;

            if(is_array($item)){
                if($delimiter = $params[$params['firstKey']][$params['depth']]){
                    array_walk($item, $formatter, $params);
                    $item = implode($delimiter, $item);
                }
            }
        };

        array_walk($urlParams, $formatter, $format);

        return $urlParams;
    }

    /**
     * get/build endpoint URL
     * @param array $path
     * @param array $placeholders
     * @param array $params
     * @return string
     */
    protected function getEndpointURL(array $path = [], array $placeholders = [], array $params = []): string{
        $url = $this->getUrl() . Config\ESIConf::getEndpoint($path, $placeholders);

        // add "datasource" parameter (SISI, TQ) (optional)
        if( !empty($datasource = $this->getDatasource()) ){
            $params['datasource'] = $datasource;
        }
        // overwrite endpoint version (debug)
        if( !empty($endpointVersion = $this->getVersion()) ){
            $url = preg_replace('/(v[\d]+|latest|dev|legacy)/',$endpointVersion, $url, 1);
        }

        if( !empty($params) ){
            // add URL params
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
    protected function request(string $url, string $method = 'GET', string $accessToken = '', array $additionalOptions = []){
        $responseBody = null;
        $method = strtoupper($method);

        $webClient = namespace\Lib\WebClient::instance($this->getDebugLevel());

        if( \Audit::instance()->url($url) ){
            // check if url is blocked (error limit exceeded)
            if(!$webClient->isBlockedUrl($url)){
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
                        // "Content-Type" Header is required for POST requests
                        $requestOptions['header'][] = 'Content-Type: application/json';

                        $requestOptions['content'] =  json_encode($additionalOptions['content'], JSON_UNESCAPED_SLASHES);
                        unset($additionalOptions['content']);
                    }

                    $responseBody = $webClient->request($url, $requestOptions, $additionalOptions);
                }else{
                    $webClient->getLogger('err_server')->write(sprintf(self::ERROR_ESI_METHOD, $method, $url));
                }
            }
        }else{
            $webClient->getLogger('err_server')->write(sprintf(self::ERROR_ESI_URL, $url));
        }

        return $responseBody;
    }
}