<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 20:45
 */

namespace Exodus4D\ESI;


interface ApiInterface {

    /**
     * set user agent string.
     * -> send in HEADERS
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent);

    /**
     * @return string
     */
    public function getUserAgent();

    /**
     * @return array
     */
    public function getServerStatus() : array;

    /**
     * get corporation/alliance ids by characterIds
     * @param array $characterIds
     * @return array
     */
    public function getCharacterAffiliationData(array $characterIds) : array;

    /**
     * @param int $characterId
     * @return array
     */
    public function getCharacterData(int $characterId) : array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterLocationData(int $characterId, string $accessToken, array $additionalOptions = []) : array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterShipData(int $characterId, string $accessToken, array $additionalOptions = []) : array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterOnlineData(int $characterId, string $accessToken, array $additionalOptions = []) : array;

    /**
     * @param int $corporationId
     * @return array
     */
    public function getCorporationData(int $corporationId) : array;

    /**
     * @param int $allianceId
     * @return array
     */
    public function getAllianceData(int $allianceId) : array;

    /**
     * @param int $corporationId
     * @param string $accessToken
     * @return array
     */
    public function getCorporationRoles(int $corporationId, string $accessToken) : array;

    /**
     * @return array
     */
    public function getUniverseRegions() : array;

    /**
     * @param int $regionId
     * @return array
     */
    public function getUniverseRegionData(int $regionId) : array;

    /**
     * @return array
     */
    public function getUniverseConstellations() : array;

    /**
     * @param int $constellationId
     * @return array
     */
    public function getUniverseConstellationData(int $constellationId) : array;

    /**
     * @return array
     */
    public function getUniverseSystems() : array;

    /**
     * @param int $systemId
     * @return array
     */
    public function getUniverseSystemData(int $systemId) : array;

    /**
     * @param array $universeIds
     * @param array $additionalOptions
     * @return array
     */
    public function getUniverseNamesData(array $universeIds, array $additionalOptions = []) : array;

    /**
     * @return array
     */
    public function getUniverseJumps() : array;

    /**
     * @return array
     */
    public function getUniverseKills() : array;

    /**
     * @return array
     */
    public function getUniverseCategories() : array;

    /**
     * @param int $categoryId
     * @return array
     */
    public function getUniverseCategoryData(int $categoryId) : array;

    /**
     * @return array
     */
    public function getUniverseGroups() : array;

    /**
     * @param int $groupId
     * @return array
     */
    public function getUniverseGroupData(int $groupId) : array;

    /**
     * @param int $structureId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getUniverseStructureData(int $structureId, string $accessToken, array $additionalOptions = []) : array;

    /**
     * @param int $typeId
     * @param array $additionalOptions
     * @return array
     */
    public function getUniverseTypesData(int $typeId, array $additionalOptions = []) : array;

    /**
     * @param int $sourceId
     * @param int $targetId
     * @param array $options
     * @return array
     */
    public function getRouteData(int $sourceId, int $targetId, array $options = []) : array;
    
    /**
     * @param int $systemId
     * @param string $accessToken
     * @param array $options
     * @return array
     */
    public function setWaypoint(int $systemId, string $accessToken, array $options = []) : array;
    
    /**
     * @param int $targetId
     * @param string $accessToken
     * @return array
     */
    public function openWindow(int $targetId, string $accessToken) : array;

    /**
     * @param array $categories
     * @param string $search
     * @param bool $strict
     * @return array
     */
    public function search(array $categories, string $search, bool $strict = false) : array;

        /**
     * @param int $corporationId
     * @return bool
     */
    public function isNpcCorporation(int $corporationId) : bool;
}