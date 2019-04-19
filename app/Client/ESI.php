<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 16:37
 */

namespace Exodus4D\ESI\Client;

use Exodus4D\ESI\Config;
use Exodus4D\ESI\Mapper;

class ESI extends AbstractCcp implements EsiInterface {

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
     * @return array
     */
    public function getServerStatus() : array {
        $uri = $this->getEndpointURI(['status', 'GET']);
        $serverStatus = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $serverStatus['status'] = (new Mapper\ServerStatus($response))->getData();
        }else{
            $serverStatus['error'] = $response->error;
        }

        return $serverStatus;
    }

    /**
     * @param array $characterIds
     * @return array
     */
    public function getCharacterAffiliationData(array $characterIds) : array {
        $uri = $this->getEndpointURI(['characters', 'affiliation', 'POST']);
        $characterAffiliationData = [];

        $requestOptions = $this->getRequestOptions('', $characterIds);
        $response = $this->request('POST', $uri, $requestOptions)->getContents();

        if(!$response->error){
            foreach((array)$response as $affiliationData){
                $characterAffiliationData[] = (new Mapper\CharacterAffiliation($affiliationData))->getData();
            }
        }

        return $characterAffiliationData;
    }

    /**
     * @param int $characterId
     * @return array
     */
    public function getCharacterData(int $characterId) : array {
        $uri = $this->getEndpointURI(['characters', 'GET'], [$characterId]);
        $characterData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $characterData = (new Mapper\Character($response))->getData();
            if( !empty($characterData) ){
                $characterData['id'] = $characterId;
            }
        }

        return $characterData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterClonesData(int $characterId, string $accessToken) : array {
        $uri = $this->getEndpointURI(['characters', 'clones', 'GET'], [$characterId]);
        $clonesData = [];

        $requestOptions = $this->getRequestOptions($accessToken);
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $clonesData['home'] = (new Mapper\CharacterClone($response->home_location))->getData();
        }else{
            $clonesData['error'] = $response->error;
        }

        return $clonesData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterLocationData(int $characterId, string $accessToken) : array {
        $uri = $this->getEndpointURI(['characters', 'location', 'GET'], [$characterId]);
        $locationData = [];

        $requestOptions = $this->getRequestOptions($accessToken);
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $locationData = (new Mapper\Location($response))->getData();
        }

        return $locationData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterShipData(int $characterId, string $accessToken) : array {
        $uri = $this->getEndpointURI(['characters', 'ship', 'GET'], [$characterId]);
        $shipData = [];

        $requestOptions = $this->getRequestOptions($accessToken);
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $shipData = (new Mapper\Ship($response))->getData();
        }

        return $shipData;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterOnlineData(int $characterId, string $accessToken) : array {
        $uri = $this->getEndpointURI(['characters', 'online', 'GET'], [$characterId]);
        $onlineData = [];

        $requestOptions = $this->getRequestOptions($accessToken);
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $onlineData = (new Mapper\Online($response))->getData();
        }

        return $onlineData;
    }

    /**
     * @param int $corporationId
     * @return array
     */
    public function getCorporationData(int $corporationId) : array {
        $uri = $this->getEndpointURI(['corporations', 'GET'], [$corporationId]);
        $corporationData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $corporationData = (new Mapper\Corporation($response))->getData();
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
    public function getAllianceData(int $allianceId) : array {
        $uri = $this->getEndpointURI(['alliances', 'GET'], [$allianceId]);
        $allianceData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $allianceData = (new Mapper\Alliance($response))->getData();
            if( !empty($allianceData) ){
                $allianceData['id'] = $allianceId;
            }
        }

        return $allianceData;
    }

    /**
     * @param int $corporationId
     * @param string $accessToken
     * @return array
     */
    public function getCorporationRoles(int $corporationId, string $accessToken) : array {
        $uri = $this->getEndpointURI(['corporations', 'roles', 'GET'], [$corporationId]);
        $rolesData = [];

        $requestOptions = $this->getRequestOptions($accessToken);

        // 403 'Character cannot grant roles' error
        $requestOptions['log_off_status'] = [403];

        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            foreach((array)$response as $characterRoleData){
                $rolesData['roles'][(int)$characterRoleData->character_id] = array_map('strtolower', (array)$characterRoleData->roles);
            }
        }else{
            $rolesData['error'] = $response->error;
        }

        return $rolesData;
    }

    /**
     * @return array
     */
    public function getUniverseFactions() : array {
        $uri = $this->getEndpointURI(['universe', 'factions', 'list', 'GET']);
        $factionData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            foreach((array)$response as $data){
                $factionData['factions'][(int)$data->faction_id] = (new Mapper\Universe\Faction($data))->getData();
            }
        }else{
            $factionData['error'] = $response->error;
        }

        return $factionData;
    }

    /**
     * @param int $factionId
     * @return array
     */
    public function getUniverseFactionData(int $factionId) : array {
        $factionDataAll = $this->getUniverseFactions();
        $factionData = [];

        if(isset($factionDataAll['error'])){
            $factionData = $factionDataAll;
        }elseif(is_array($factionDataAll['factions']) && array_key_exists($factionId, $factionDataAll['factions'])){
            $factionData = $factionDataAll['factions'][$factionId];
        }

        return $factionData;
    }

    /**
     * @return array
     */
    public function getUniverseRegions() : array {
        $uri = $this->getEndpointURI(['universe', 'regions', 'list', 'GET']);
        $regionData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $regionData = array_unique( array_map('intval', (array)$response) );
        }

        return $regionData;
    }

    /**
     * @param int $regionId
     * @return array
     */
    public function getUniverseRegionData(int $regionId) : array {
        $uri = $this->getEndpointURI(['universe', 'regions', 'GET'], [$regionId]);
        $regionData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $regionData = (new Mapper\Region($response))->getData();
        }

        return $regionData;
    }

    /**
     * @return array
     */
    public function getUniverseConstellations() : array {
        $uri = $this->getEndpointURI(['universe', 'constellations', 'list', 'GET']);
        $constellationData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $constellationData = array_unique( array_map('intval', (array)$response) );
        }

        return $constellationData;
    }

    /**
     * @param int $constellationId
     * @return array
     */
    public function getUniverseConstellationData(int $constellationId) : array {
        $uri = $this->getEndpointURI(['universe', 'constellations', 'GET'], [$constellationId]);
        $constellationData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $constellationData = (new Mapper\Constellation($response))->getData();
        }

        return $constellationData;
    }

    /**
     * @return array
     */
    public function getUniverseSystems() : array {
        $uri = $this->getEndpointURI(['universe', 'systems', 'list', 'GET']);
        $systemData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $systemData = array_unique( array_map('intval', (array)$response) );
        }

        return $systemData;
    }

    /**
     * @param int $systemId
     * @return array
     */
    public function getUniverseSystemData(int $systemId) : array {
        $uri = $this->getEndpointURI(['universe', 'systems', 'GET'], [$systemId]);
        $systemData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $systemData = (new Mapper\System($response))->getData();
        }

        return $systemData;
    }

    /**
     * @param int $starId
     * @return array
     */
    public function getUniverseStarData(int $starId) : array {
        $uri = $this->getEndpointURI(['universe', 'stars', 'GET'], [$starId]);
        $starData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $starData = (new Mapper\Universe\Star($response))->getData();
            if( !empty($starData) ){
                $starData['id'] = $starId;
            }
        }

        return $starData;
    }

    /**
     * @param int $planetId
     * @return array
     */
    public function getUniversePlanetData(int $planetId) : array {
        $uri = $this->getEndpointURI(['universe', 'planets', 'GET'], [$planetId]);
        $planetData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $planetData = (new Mapper\Universe\Planet($response))->getData();
        }

        return $planetData;
    }

    /**
     * @param int $stargateId
     * @return array
     */
    public function getUniverseStargateData(int $stargateId) : array {
        $uri = $this->getEndpointURI(['universe', 'stargates', 'GET'], [$stargateId]);
        $stargateData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $stargateData = (new Mapper\Universe\Stargate($response))->getData();
        }

        return $stargateData;
    }

    /**
     * @param array $universeIds
     * @return array
     */
    public function getUniverseNamesData(array $universeIds) : array {
        $uri = $this->getEndpointURI(['universe', 'names', 'POST']);
        $universeData = [];

        $requestOptions = $this->getRequestOptions('', $universeIds);
        $response = $this->request('POST', $uri, $requestOptions)->getContents();

        if(!$response->error){
            foreach((array)$response as $data){
                // store category because $data get changed in Mappers
                $category = $data->category;
                switch($category){
                    case 'character':
                        $categoryData = (new Mapper\Character($data))->getData();
                        break;
                    case 'alliance':
                        $categoryData = (new Mapper\Alliance($data))->getData();
                        break;
                    case 'corporation':
                        $categoryData = (new Mapper\Corporation($data))->getData();
                        break;
                    case 'station':
                        $categoryData = (new Mapper\Station($data))->getData();
                        break;
                    case 'solar_system':
                        $category = 'system';
                        $categoryData = (new Mapper\System($data))->getData();
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
            $universeData['error'] = $response->error;
        }

        return $universeData;
    }

    /**
     * @return array
     */
    public function getUniverseJumps() : array {
        $uri = $this->getEndpointURI(['universe', 'system_jumps', 'GET']);
        $systemJumps = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            foreach((array)$response as $jumpData){
                $systemJumps[$jumpData->system_id]['jumps'] = (int)$jumpData->ship_jumps;
            }
        }

        return $systemJumps;
    }

    /**
     * @return array
     */
    public function getUniverseKills() : array {
        $uri = $this->getEndpointURI(['universe', 'system_kills', 'GET']);
        $systemKills = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
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
    public function getUniverseCategories() : array {
        $uri = $this->getEndpointURI(['universe', 'categories', 'list', 'GET']);
        $categoryData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $categoryData = array_unique( array_map('intval', (array)$response) );
        }

        return $categoryData;
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getUniverseCategoryData(int $categoryId) : array {
        $uri = $this->getEndpointURI(['universe', 'categories', 'GET'], [$categoryId]);
        $categoryData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $categoryData = (new Mapper\Universe\Category($response))->getData();
            if( !empty($categoryData) ){
                $categoryData['id'] = $categoryId;
            }
        }

        return $categoryData;
    }

    /**
     * @return array
     */
    public function getUniverseGroups() : array {
        $uri = $this->getEndpointURI(['universe', 'groups', 'list', 'GET']);
        $groupData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $groupData = array_unique( array_map('intval', (array)$response) );
        }

        return $groupData;
    }

    /**
     * @param int $groupId
     * @return array
     */
    public function getUniverseGroupData(int $groupId) : array {
        $uri = $this->getEndpointURI(['universe', 'groups', 'GET'], [$groupId]);
        $groupData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $groupData = (new Mapper\Universe\Group($response))->getData();
            if( !empty($groupData) ){
                $groupData['id'] = $groupId;
            }
        }

        return $groupData;
    }

    /**
     * @param int $structureId
     * @param string $accessToken
     * @return array
     */
    public function getUniverseStructureData(int $structureId, string $accessToken) : array {
        $uri = $this->getEndpointURI(['universe', 'structures', 'GET'], [$structureId]);
        $structureData = [];

        $requestOptions = $this->getRequestOptions($accessToken);
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $structureData = (new Mapper\Universe\Structure($response))->getData();
            if( !empty($structureData) ){
                $structureData['id'] = $structureId;
            }
        }

        return $structureData;
    }

    /**
     * @param int $typeId
     * @return array
     */
    public function getUniverseTypesData(int $typeId) : array {
        $uri = $this->getEndpointURI(['universe', 'types', 'GET'], [$typeId]);
        $typesData = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $typesData = (new Mapper\Universe\Type($response))->getData();
        }

        return $typesData;
    }

    /**
     * @param int $sourceId
     * @param int $targetId
     * @param array $options
     * @return array
     */
    public function getRouteData(int $sourceId, int $targetId, array $options = []) : array {
        $uri = $this->getEndpointURI(['routes', 'GET'], [$sourceId, $targetId]);
        $routeData = [];

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

        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $routeData['route'] = array_unique( array_map('intval', (array)$response) );
        }else{
            $routeData['error'] = $response->error;
        }

        return $routeData;
    }

    /**
     * @param int $systemId
     * @param string $accessToken
     * @param array $options
     * @return array
     */
    public function setWaypoint(int $systemId, string $accessToken, array $options = []) : array {
        $uri = $this->getEndpointURI(['ui', 'autopilot', 'waypoint', 'POST']);
        $waypointData = [];

        $query = [
            'add_to_beginning'      => var_export( (bool)$options['addToBeginning'], true),
            'clear_other_waypoints' => var_export( (bool)$options['clearOtherWaypoints'], true),
            'destination_id'        => $systemId
        ];

        $requestOptions = $this->getRequestOptions($accessToken, null, $query);
        $response = $this->request('POST', $uri, $requestOptions)->getContents();

        // "null" === success => There is no response body send...
        if( $response->error ){
            $waypointData['error'] = self::ERROR_ESI_WAYPOINT;
        }

        return $waypointData;
    }

    /**
     * @param int $targetId
     * @param string $accessToken
     * @return array
     */
    public function openWindow(int $targetId, string $accessToken) : array {
        $uri = $this->getEndpointURI(['ui', 'openwindow', 'information', 'POST']);
        $return = [];

        $query = [
            'target_id' => $targetId
        ];

        $requestOptions = $this->getRequestOptions($accessToken, null, $query);
        $response = $this->request('POST', $uri, $requestOptions)->getContents();

        // "null" === success => There is no response body send...
        if( $response->error ){
            $return['error'] = self::ERROR_ESI_WINDOW;
        }

        return $return;
    }

    /**
     * @param array $categories
     * @param string $search
     * @param bool $strict
     * @return array
     */
    public function search(array $categories, string $search, bool $strict = false) : array {
        $uri = $this->getEndpointURI(['search', 'GET']);
        $searchData = [];

        $query = [
            'categories'            => $categories,
            'search'                => $search,
            'strict'                => var_export( (bool)$strict, true),
        ];

        $query = $this->formatUrlParams($query, [
            'categories' => [',']
        ]);

        $requestOptions = $this->getRequestOptions('', null, $query);
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if($response->error){
            $searchData['error'] = $response->error;
        }else{
            $searchData = (new Mapper\Search($response))->getData();
        }

        return $searchData;
    }

    /**
     * @param string $version
     * @return array
     */
    public function getStatus(string $version = 'last') : array {
        $uri = $this->getEndpointURI(['meta', 'status', 'GET']);
        $statusData = [];

        $requestOptions = [
            'query' => [
                'version' => $version
            ]
        ];

        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            foreach((array)$response as $status){
                $statusData['status'][] = (new Mapper\EsiStatus($status))->getData();
            }
        }else{
            $statusData['error'] = $response->error;
        }

        return $statusData;
    }

    /**
     * @param string $version
     * @return array
     */
    public function getStatusForRoutes(string $version = 'last') : array {
        // data for all configured ESI endpoints
        $statusData = [
            'status' => Config\ESIConf::getEndpointsData()
        ];

        $statusDataAll = $this->getStatus($version);
        if(!isset($statusDataAll['error'])){
            foreach((array)$statusData['status'] as $key => $data){
                foreach((array)$statusDataAll['status'] as $status){
                    if(
                        $status['route'] == $data['route'] &&
                        $status['method'] == $data['method']
                    ){
                        $statusData['status'][$key]['status'] = $status['status'];
                        $statusData['status'][$key]['tags']   = $status['tags'];
                        break;
                    }
                }
            }
        }else{
            $statusData['error'] = $statusDataAll['error'];
        }

        return $statusData;
    }

    /**
     * @param int $corporationId
     * @return bool
     */
    public function isNpcCorporation(int $corporationId) : bool {
        $npcCorporations = $this->getNpcCorporations();
        return in_array($corporationId, $npcCorporations);
    }

    /**
     * @return array
     */
    protected function getNpcCorporations() : array {
        $uri = $this->getEndpointURI(['corporations', 'npccorps', 'GET']);
        $npcCorporations = [];

        $requestOptions = $this->getRequestOptions();
        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $npcCorporations = $response;
        }

        return $npcCorporations;
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
        $uri = Config\ESIConf::getEndpoint($path, $placeholders);

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
}