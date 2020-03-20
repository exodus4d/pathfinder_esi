<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 16:37
 */

namespace Exodus4D\ESI\Client\Ccp\Esi;

use Exodus4D\ESI\Client\Ccp;
use Exodus4D\ESI\Config\ConfigInterface;
use Exodus4D\ESI\Config\Ccp\Esi\Config;
use Exodus4D\ESI\Lib\RequestConfig;
use Exodus4D\ESI\Lib\WebClient;
use Exodus4D\ESI\Mapper\Esi as Mapper;

class Esi extends Ccp\AbstractCcp implements EsiInterface {

    /**
     * error message for set waypoint
     */
    const ERROR_ESI_WAYPOINT                        = 'Could not set waypoint.';

    /**
     * error message for open client window
     */
    const ERROR_ESI_WINDOW                          = 'Could not open client window.';

    /**
     * DataSource 'singularity' || 'tranquility'
     * @var string $esiDataSource
     */
    private $esiDataSource                          = '';

    /**
     * Overwrite versioned endpoint URL (for testing)
     * @var string
     */
    private $endpointVersion                        = '';

    /**
     * @param string $dataSource
     */
    public function setDataSource(string $dataSource){
        $this->esiDataSource = $dataSource;
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
    public function getDataSource() : string {
        return $this->esiDataSource;
    }

    /**
     * @return string
     */
    public function getVersion() : string {
        return $this->endpointVersion;
    }

    /**
     * @return RequestConfig
     */
    protected function getServerStatusRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['status', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $serverStatus = [];
                if(!$body->error){
                    $serverStatus['status'] = (new Mapper\Status\Status($body))->getData();
                }else{
                    $serverStatus['error'] = $body->error;
                }

                return $serverStatus;
            }
        );
    }

    /**
     * @param array $characterIds
     * @return RequestConfig
     */
    protected function getCharacterAffiliationRequest(array $characterIds) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('POST', $this->getEndpointURI(['characters', 'affiliation', 'POST'])),
            $this->getRequestOptions('', $characterIds),
            function($body) : array {
                $characterAffiliationData = [];
                if(!$body->error){
                    foreach((array)$body as $affiliationData){
                        $characterAffiliationData[] = (new Mapper\Character\Affiliation($affiliationData))->getData();
                    }
                }

                return $characterAffiliationData;
            }
        );
    }

    /**
     * @param int $characterId
     * @return RequestConfig
     */
    protected function getCharacterRequest(int $characterId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['characters', 'GET'], [$characterId])),
            $this->getRequestOptions(),
            function($body) use ($characterId) : array {
                $characterData = [];
                if(!$body->error){
                    $characterData = (new Mapper\Character\Character($body))->getData();
                    if( !empty($characterData) ){
                        $characterData['id'] = $characterId;
                    }
                }

                return $characterData;
            }
        );
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return RequestConfig
     */
    protected function getCharacterClonesRequest(int $characterId, string $accessToken) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['characters', 'clones', 'GET'], [$characterId])),
            $this->getRequestOptions($accessToken),
            function($body) : array {
                $clonesData = [];
                if(!$body->error){
                    $clonesData['home'] = (new Mapper\Character\CharacterClone($body->home_location))->getData();
                }else{
                    $clonesData['error'] = $body->error;
                }

                return $clonesData;
            }
        );
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return RequestConfig
     */
    protected function getCharacterLocationRequest(int $characterId, string $accessToken) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['characters', 'location', 'GET'], [$characterId])),
            $this->getRequestOptions($accessToken),
            function($body) : array {
                $locationData = [];
                if(!$body->error){
                    $locationData = (new Mapper\Character\Location($body))->getData();
                }

                return $locationData;
            }
        );
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return RequestConfig
     */
    protected function getCharacterShipRequest(int $characterId, string $accessToken) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['characters', 'ship', 'GET'], [$characterId])),
            $this->getRequestOptions($accessToken),
            function($body) : array {
                $shipData = [];
                if(!$body->error){
                    $shipData = (new Mapper\Character\Ship($body))->getData();
                }

                return $shipData;
            }
        );
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return RequestConfig
     */
    protected function getCharacterOnlineRequest(int $characterId, string $accessToken) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['characters', 'online', 'GET'], [$characterId])),
            $this->getRequestOptions($accessToken),
            function($body) : array {
                $onlineData = [];
                if(!$body->error){
                    $onlineData = (new Mapper\Character\Online($body))->getData();
                }

                return $onlineData;
            }
        );
    }

    /**
     * @param int $corporationId
     * @return RequestConfig
     */
    protected function getCorporationRequest(int $corporationId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['corporations', 'GET'], [$corporationId])),
            $this->getRequestOptions(),
            function($body) use ($corporationId) : array {
                $corporationData = [];
                if(!$body->error){
                    $corporationData = (new Mapper\Corporation\Corporation($body))->getData();
                    if( !empty($corporationData) ){
                        $corporationData['id'] = $corporationId;
                    }
                }else{
                    $corporationData['error'] = $body->error;
                }

                return $corporationData;
            }
        );
    }

    /**
     * @param int $allianceId
     * @return RequestConfig
     */
    protected function getAllianceRequest(int $allianceId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['alliances', 'GET'], [$allianceId])),
            $this->getRequestOptions(),
            function($body) use ($allianceId) : array {
                $allianceData = [];
                if(!$body->error){
                    $allianceData = (new Mapper\Alliance\Alliance($body))->getData();
                    if( !empty($allianceData) ){
                        $allianceData['id'] = $allianceId;
                    }
                }else{
                    $allianceData['error'] = $body->error;
                }

                return $allianceData;
            }
        );
    }

    /**
     * @param int $corporationId
     * @param string $accessToken
     * @return RequestConfig
     */
    protected function getCorporationRolesRequest(int $corporationId, string $accessToken) : RequestConfig {
        $requestOptions = $this->getRequestOptions($accessToken);

        // 403 'Character cannot grant roles' error
        $requestOptions['log_off_status'] = [403];

        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['corporations', 'roles', 'GET'], [$corporationId])),
            $requestOptions,
            function($body) : array {
                $rolesData = [];
                if(!$body->error){
                    foreach((array)$body as $characterRoleData){
                        $rolesData['roles'][(int)$characterRoleData->character_id] = array_map('strtolower', (array)$characterRoleData->roles);
                    }
                }else{
                    $rolesData['error'] = $body->error;
                }

                return $rolesData;
            }
        );
    }

    /**
     * @param int $factionId
     * @return RequestConfig
     */
    protected function getUniverseFactionRequest(int $factionId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'factions', 'list', 'GET'])),
            $this->getRequestOptions(),
            function($body) use ($factionId) : array {
                $factionData = [];
                if($body->error){
                    $factionData['error'] = $body->error;
                }else{
                    foreach((array)$body as $data){
                        $factionData['factions'][(int)$data->faction_id] = (new Mapper\Universe\Faction($data))->getData();
                    }

                    if($factionId && array_key_exists($factionId, $factionData['factions'])){
                        $factionData = $factionData['factions'][$factionId];
                    }
                }

                return $factionData;
            }
        );
    }

    /**
     * @param int $raceId
     * @return RequestConfig
     */
    protected function getUniverseRaceRequest(int $raceId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'races', 'list', 'GET'])),
            $this->getRequestOptions(),
            function($body) use ($raceId) : array {
                $raceData = [];
                if($body->error){
                    $raceData['error'] = $body->error;
                }else{
                    foreach((array)$body as $data){
                        $raceData['races'][(int)$data->race_id] = (new Mapper\Universe\Race($data))->getData();
                    }

                    if($raceId && array_key_exists($raceId, $raceData['races'])){
                        $raceData = $raceData['races'][$raceId];
                    }
                }

                return $raceData;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getUniverseRegionsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'regions', 'list', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $regionData = [];
                if(!$body->error){
                    $regionData = array_unique( array_map('intval', (array)$body) );
                }

                return $regionData;
            }
        );
    }

    /**
     * @param int $regionId
     * @return RequestConfig
     */
    protected function getUniverseRegionRequest(int $regionId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'regions', 'GET'], [$regionId])),
            $this->getRequestOptions(),
            function($body) : array {
                $regionData = [];
                if(!$body->error){
                    $regionData = (new Mapper\Universe\Region($body))->getData();
                }

                return $regionData;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getUniverseConstellationsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'constellations', 'list', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $constellationData = [];
                if(!$body->error){
                    $constellationData = array_unique( array_map('intval', (array)$body) );
                }

                return $constellationData;
            }
        );
    }

    /**
     * @param int $constellationId
     * @return RequestConfig
     */
    protected function getUniverseConstellationRequest(int $constellationId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'constellations', 'GET'], [$constellationId])),
            $this->getRequestOptions(),
            function($body) : array {
                $constellationData = [];
                if(!$body->error){
                    $constellationData = (new Mapper\Universe\Constellation($body))->getData();
                }

                return $constellationData;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getUniverseSystemsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'systems', 'list', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $systemData = [];
                if(!$body->error){
                    $systemData = array_unique( array_map('intval', (array)$body) );
                }

                return $systemData;
            }
        );
    }

    /**
     * @param int $systemId
     * @return RequestConfig
     */
    protected function getUniverseSystemRequest(int $systemId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'systems', 'GET'], [$systemId])),
            $this->getRequestOptions(),
            function($body) : array {
                $systemData = [];
                if(!$body->error){
                    $systemData = (new Mapper\Universe\System($body))->getData();
                }

                return $systemData;
            }
        );
    }

    /**
     * @param int $starId
     * @return RequestConfig
     */
    protected function getUniverseStarRequest(int $starId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'stars', 'GET'], [$starId])),
            $this->getRequestOptions(),
            function($body) use ($starId) : array {
                $starData = [];
                if(!$body->error){
                    $starData = (new Mapper\Universe\Star($body))->getData();
                    if( !empty($starData) ){
                        $starData['id'] = $starId;
                    }
                }

                return $starData;
            }
        );
    }

    /**
     * @param int $planetId
     * @return RequestConfig
     */
    protected function getUniversePlanetRequest(int $planetId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'planets', 'GET'], [$planetId])),
            $this->getRequestOptions(),
            function($body) : array {
                $planetData = [];
                if(!$body->error){
                    $planetData = (new Mapper\Universe\Planet($body))->getData();
                }

                return $planetData;
            }
        );
    }

    /**
     * @param int $stargateId
     * @return RequestConfig
     */
    protected function getUniverseStargateRequest(int $stargateId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'stargates', 'GET'], [$stargateId])),
            $this->getRequestOptions(),
            function($body) : array {
                $stargateData = [];
                if(!$body->error){
                    $stargateData = (new Mapper\Universe\Stargate($body))->getData();
                }

                return $stargateData;
            }
        );
    }

    /**
     * @param array $universeIds
     * @return RequestConfig
     */
    protected function getUniverseNamesRequest(array $universeIds) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('POST', $this->getEndpointURI(['universe', 'names', 'POST'])),
            $this->getRequestOptions('', $universeIds),
            function($body) : array {
                $universeData = [];
                if(!$body->error){
                    foreach((array)$body as $data){
                        // store category because $data get changed in Mappers
                        $category = $data->category;
                        switch($category){
                            case 'character':
                                $categoryData = (new Mapper\Character\Character($data))->getData();
                                break;
                            case 'alliance':
                                $categoryData = (new Mapper\Alliance\Alliance($data))->getData();
                                break;
                            case 'corporation':
                                $categoryData = (new Mapper\Corporation\Corporation($data))->getData();
                                break;
                            case 'station':
                                $categoryData = (new Mapper\Universe\Station($data))->getData();
                                break;
                            case 'solar_system':
                                $category = 'system';
                                $categoryData = (new Mapper\Universe\System($data))->getData();
                                break;
                            case 'inventory_type':
                                $category = 'inventoryType';
                                $categoryData = (new Mapper\InventoryType($data))->getData();
                                break;
                            default:
                                $categoryData = [];
                        }
                        if( !empty($categoryData) ){
                            $universeData[$category][] = $categoryData;
                        }
                    }
                }else{
                    $universeData['error'] = $body->error;
                }

                return $universeData;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getUniverseJumpsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'system_jumps', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $systemJumps = [];
                if(!$body->error){
                    foreach((array)$body as $jumpData){
                        $systemJumps[$jumpData->system_id]['jumps'] = (int)$jumpData->ship_jumps;
                    }
                }

                return $systemJumps;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getUniverseKillsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'system_kills', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $systemKills = [];
                if(!$body->error){
                    foreach((array)$body as $killData){
                        $systemKills[$killData->system_id] = [
                            'npc_kills' => (int)$killData->npc_kills,
                            'pod_kills' => (int)$killData->pod_kills,
                            'ship_kills' => (int)$killData->ship_kills
                        ];
                    }
                }

                return $systemKills;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getUniverseCategoriesRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'categories', 'list', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $categoryData = [];
                if(!$body->error){
                    $categoryData = array_unique( array_map('intval', (array)$body) );
                }

                return $categoryData;
            }
        );
    }

    /**
     * @param int $categoryId
     * @return RequestConfig
     */
    protected function getUniverseCategoryRequest(int $categoryId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'categories', 'GET'], [$categoryId])),
            $this->getRequestOptions(),
            function($body) use ($categoryId) : array {
                $categoryData = [];
                if(!$body->error){
                    $categoryData = (new Mapper\Universe\Category($body))->getData();
                    if( !empty($categoryData) ){
                        $categoryData['id'] = $categoryId;
                    }
                }

                return $categoryData;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getUniverseGroupsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'groups', 'list', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $groupData = [];
                if(!$body->error){
                    $groupData = array_unique( array_map('intval', (array)$body) );
                }

                return $groupData;
            }
        );
    }

    /**
     * @param int $groupId
     * @return RequestConfig
     */
    protected function getUniverseGroupRequest(int $groupId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'groups', 'GET'], [$groupId])),
            $this->getRequestOptions(),
            function($body) use ($groupId) : array {
                $groupData = [];
                if(!$body->error){
                    $groupData = (new Mapper\Universe\Group($body))->getData();
                    if( !empty($groupData) ){
                        $groupData['id'] = $groupId;
                    }
                }

                return $groupData;
            }
        );
    }

    /**
     * @param int $structureId
     * @param string $accessToken
     * @return RequestConfig
     */
    protected function getUniverseStructureRequest(int $structureId, string $accessToken) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'structures', 'GET'], [$structureId])),
            $this->getRequestOptions($accessToken),
            function($body) use ($structureId) : array {
                $structureData = [];
                if(!$body->error){
                    $structureData = (new Mapper\Universe\Structure($body))->getData();
                    if( !empty($structureData) ){
                        $structureData['id'] = $structureId;
                    }
                }else{
                    $structureData['error'] = $body->error;
                }

                return $structureData;
            }
        );
    }

    /**
     * @param int $stationId
     * @return RequestConfig
     */
    protected function getUniverseStationRequest(int $stationId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'stations', 'GET'], [$stationId])),
            $this->getRequestOptions(),
            function($body) : array {
                $stationData = [];
                if(!$body->error){
                    $stationData = (new Mapper\Universe\Station($body))->getData();
                }else{
                    $stationData['error'] = $body->error;
                }

                return $stationData;
            }
        );
    }

    /**
     * @param int $typeId
     * @return RequestConfig
     */
    protected function getUniverseTypeRequest(int $typeId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['universe', 'types', 'GET'], [$typeId])),
            $this->getRequestOptions(),
            function($body) : array {
                $typeData = [];
                if(!$body->error){
                    $typeData = (new Mapper\Universe\Type($body))->getData();
                }

                return $typeData;
            }
        );
    }

    /**
     * @param int $attributeId
     * @return RequestConfig
     */
    protected function getDogmaAttributeRequest(int $attributeId) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['dogma', 'attributes', 'GET'], [$attributeId])),
            $this->getRequestOptions(),
            function($body) : array {
                $attributeData = [];
                if(!$body->error){
                    $attributeData = (new Mapper\Dogma\Attribute($body))->getData();
                }else{
                    $attributeData['error'] = $body->error;
                }

                return $attributeData;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getFactionWarSystemsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['fw', 'systems', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $systemsData = [];
                if(!$body->error){
                    foreach((array)$body as $data){
                        $systemsData['systems'][(int)$data->solar_system_id] = (new Mapper\FactionWarfare\System($data))->getData();
                    }
                }else{
                    $systemsData['error'] = $body->error;
                }

                return $systemsData;
            }
        );
    }

    /**
     * @param int   $sourceId
     * @param int   $targetId
     * @param array $options
     * @return RequestConfig
     */
    protected function getRouteRequest(int $sourceId, int $targetId, array $options = []) : RequestConfig {
        $query = [];
        if( !empty($options['avoid']) ){
            $query['avoid'] = $options['avoid'];
        }
        if( !empty($options['connections']) ){
            $query['connections'] = $options['connections'];
        }
        if( !empty($options['flag']) ){
            $query['flag'] = $options['flag'];
        }

        $query = $this->formatUrlParams($query, [
            'connections' => [',', '|'],
            'avoid' => [',']
        ]);

        $requestOptions = $this->getRequestOptions('', null, $query);

        // 404 'No route found' error -> should not be logged
        $requestOptions['log_off_status'] = [404];

        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['routes', 'GET'], [$sourceId, $targetId])),
            $requestOptions,
            function($body) : array {
                $routeData = [];
                if(!$body->error){
                    $routeData['route'] = array_unique(array_map('intval', (array)$body));
                }else{
                    $routeData['error'] = $body->error;
                }
                return $routeData;
            }
        );
    }

    /**
     * @param int $destinationId
     * @param string $accessToken
     * @param array $options
     * @return RequestConfig
     */
    protected function setWaypointRequest(int $destinationId, string $accessToken, array $options = []) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('POST', $this->getEndpointURI(['ui', 'autopilot', 'waypoint', 'POST'])),
            $this->getRequestOptions($accessToken, null, [
                'add_to_beginning'      => var_export( (bool)$options['addToBeginning'], true),
                'clear_other_waypoints' => var_export( (bool)$options['clearOtherWaypoints'], true),
                'destination_id'        => $destinationId
            ]),
            function($body) : array {
                $return = [];
                // "null" === success => There is no response body send...
                if($body->error){
                    $return['error'] = self::ERROR_ESI_WAYPOINT;
                }

                return $return;
            }
        );
    }

    /**
     * @param int $targetId
     * @param string $accessToken
     * @return RequestConfig
     */
    protected function openWindowRequest(int $targetId, string $accessToken) : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('POST', $this->getEndpointURI(['ui', 'openwindow', 'information', 'POST'])),
            $this->getRequestOptions($accessToken, null, [
                'target_id' => $targetId
            ]),
            function($body) : array {
                $return = [];
                // "null" === success => There is no response body send...
                if($body->error){
                    $return['error'] = self::ERROR_ESI_WINDOW;
                }

                return $return;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getSovereigntyMapRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['sovereignty', 'map', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $sovData = [];
                if(!$body->error){
                    foreach((array)$body as $data){
                        $sovData['map'][(int)$data->system_id] = (new Mapper\Sovereignty\Map($data))->getData();
                    }
                }else{
                    $sovData['error'] = $body->error;
                }

                return $sovData;
            }
        );
    }

    /**
     * @param array $categories
     * @param string $search
     * @param bool $strict
     * @return RequestConfig
     */
    protected function searchRequest(array $categories, string $search, bool $strict = false) : RequestConfig {
        $query = [
            'categories'            => $categories,
            'search'                => $search,
            'strict'                => var_export( (bool)$strict, true),
        ];

        $query = $this->formatUrlParams($query, [
            'categories' => [',']
        ]);

        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['search', 'GET'])),
            $this->getRequestOptions('', null, $query),
            function($body) : array {
                $searchData = [];
                if(!$body->error){
                    $searchData = (new Mapper\Search\Search($body))->getData();
                }else{
                    $searchData['error'] = $body->error;
                }

                return $searchData;
            }
        );
    }

    /**
     * @param string $version
     * @param bool $forRoutes
     * @return RequestConfig
     */
    protected function getStatusRequest(string $version = 'last', bool $forRoutes = false) : RequestConfig {
        $requestOptions = [
            'query' => [
                'version' => $version
            ]
        ];

        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['meta', 'status', 'GET'])),
            $requestOptions,
            function($body) use ($forRoutes) : array {
                $statusData = [];
                if(!$body->error){
                    foreach((array)$body as $status){
                        $statusData['status'][] = (new Mapper\Status($status))->getData();
                    }

                    if($forRoutes){
                        // data for all configured ESI endpoints
                        $statusDataRoutes = [
                            'status' => $this->getConfig()->getEndpointsData()
                        ];

                        foreach((array)$statusDataRoutes['status'] as $key => $data){
                            foreach((array)$statusData['status'] as $status){
                                if(
                                    $status['route'] == $data['route'] &&
                                    $status['method'] == $data['method']
                                ){
                                    $statusDataRoutes['status'][$key]['status'] = $status['status'];
                                    $statusDataRoutes['status'][$key]['tags']   = $status['tags'];
                                    break;
                                }
                            }
                        }

                        $statusData = $statusDataRoutes;
                    }
                }else{
                    $statusData['error'] = $body->error;
                }

                return $statusData;
            }
        );
    }

    /**
     * @return RequestConfig
     */
    protected function getNpcCorporationsRequest() : RequestConfig {
        return new RequestConfig(
            WebClient::newRequest('GET', $this->getEndpointURI(['corporations', 'npccorps', 'GET'])),
            $this->getRequestOptions(),
            function($body) : array {
                $npcCorporations = [];
                if(!$body->error){
                    $npcCorporations = array_unique(array_map('intval', (array)$body));
                }

                return $npcCorporations;
            }
        );
    }

    /**
     * @param array $query
     * @param array $format
     * @return array
     */
    protected function formatUrlParams(array $query = [], array $format = []) : array {

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

        array_walk($query, $formatter, $format);

        return $query;
    }

    /**
     * get/build endpoint URI
     * @param array $path
     * @param array $placeholders
     * @return string
     */
    protected function getEndpointURI(array $path = [], array $placeholders = []) : string {
        $uri = $this->getConfig()->getEndpoint($path, $placeholders);

        // overwrite endpoint version (debug)
        if( !empty($endpointVersion = $this->getVersion()) ){
            $uri = preg_replace('/(v[\d]+|latest|dev|legacy)/', $endpointVersion, $uri, 1);
        }

        return $uri;
    }

    /**
     * get "default" request options for ESI endpoints
     * @param string $accessToken
     * @param null $content
     * @param array $query
     * @return array
     */
    protected function getRequestOptions(string $accessToken = '', $content = null, array $query = []) : array {
        $options = [];

        if(!empty($accessToken)){
            // send Authorization HTTP header
            // see: https://guzzle.readthedocs.io/en/latest/request-options.html#headers
            $options['headers'] = $this->getAuthHeader($accessToken, 'Bearer');
        }

        if(!empty($content)){
            // send content (body) is always Json
            // see: https://guzzle.readthedocs.io/en/latest/request-options.html#json
            $options['json'] = $content;
        }

        if(!empty($datasource = $this->getDataSource())){
            $query += ['datasource' => $datasource];
        }

        if(!empty($query)){
            // URL Query options
            // see: https://guzzle.readthedocs.io/en/latest/request-options.html#query
            $options['query'] = $query;
        }

        return $options;
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig() : ConfigInterface {
        return ($this->config instanceof ConfigInterface) ? $this->config : $this->config = new Config();
    }
}