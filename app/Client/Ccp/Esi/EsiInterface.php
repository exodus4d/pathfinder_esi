<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:27
 */

namespace Exodus4D\ESI\Client\Ccp\Esi;

interface EsiInterface {

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterClonesData(int $characterId, string $accessToken) : array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterLocationData(int $characterId, string $accessToken) : array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterShipData(int $characterId, string $accessToken) : array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterOnlineData(int $characterId, string $accessToken) : array;

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
    public function getUniverseFactions() : array;

    /**
     * @param int $factionId
     * @return array
     */
    public function getUniverseFactionData(int $factionId) : array;

    /**
     * @return array
     */
    public function getUniverseRaces() : array;

    /**
     * @param int $raceId
     * @return array
     */
    public function getUniverseRaceData(int $raceId) : array;

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
     * @param int $starId
     * @return array
     */
    public function getUniverseStarData(int $starId) : array;

    /**
     * @param int $planetId
     * @return array
     */
    public function getUniversePlanetData(int $planetId) : array;

    /**
     * @param int $stargateId
     * @return array
     */
    public function getUniverseStargateData(int $stargateId) : array;

    /**
     * @param array $universeIds
     * @return array
     */
    public function getUniverseNamesData(array $universeIds) : array;

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
     * @return array
     */
    public function getUniverseStructureData(int $structureId, string $accessToken) : array;

    /**
     * @param int $stationId
     * @return array
     */
    public function getUniverseStationData(int $stationId) : array;

    /**
     * @param int $typeId
     * @return array
     */
    public function getUniverseTypesData(int $typeId) : array;

    /**
     * @param int $attributeId
     * @return array
     */
    public function getDogmaAttributeData(int $attributeId) : array;

    /**
     * @return array
     */
    public function getFactionWarSystems() : array;

    /**
     * @param int $destinationId
     * @param string $accessToken
     * @param array $options
     * @return array
     */
    public function setWaypoint(int $destinationId, string $accessToken, array $options = []) : array;

    /**
     * @param int $targetId
     * @param string $accessToken
     * @return array
     */
    public function openWindow(int $targetId, string $accessToken) : array;

    /**
     * @return array
     */
    public function getSovereigntyMap() : array;

    /**
     * @param array $categories
     * @param string $search
     * @param bool $strict
     * @return array
     */
    public function search(array $categories, string $search, bool $strict = false) : array;

    /**
     * @param string $version
     * @return array
     */
    public function getStatus(string $version) : array;

    /**
     * @param string $version
     * @return array
     */
    public function getStatusForRoutes(string $version) : array;

    /**
     * @param int $corporationId
     * @return bool
     */
    public function isNpcCorporation(int $corporationId) : bool;
}