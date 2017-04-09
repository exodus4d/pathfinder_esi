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
     * get corporation/alliance ids by characterIds
     * @param array $characterIds
     * @return array
     */
    public function getCharacterAffiliationData(array $characterIds): array;

    /**
     * @param int $characterId
     * @return array
     */
    public function getCharacterData(int $characterId): array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterLocationData(int $characterId, string $accessToken, array $additionalOptions = []): array;

    /**
     * @param int $characterId
     * @param string $accessToken
     * @param array $additionalOptions
     * @return array
     */
    public function getCharacterShipData(int $characterId, string $accessToken, array $additionalOptions = []): array;

    /**
     * @param int $corporationId
     * @return array
     */
    public function getCorporationData(int $corporationId): array;

    /**
     * @param int $allianceId
     * @return array
     */
    public function getAllianceData(int $allianceId): array;

    /**
     * @param int $systemId
     * @param string $accessToken
     * @param array $options
     * @return array
     */
    public function setWaypoint(int $systemId, string $accessToken, array $options = []): array;

    /**
     * @param array $universeIds
     * @return array
     */
    public function getUniverseNamesData(array $universeIds): array;

    /**
     * @param int $targetId
     * @param string $accessToken
     * @return array
     */
    public function openWindow(int $targetId, string $accessToken): array;

    /**
     * @param int $corporationId
     * @return bool
     */
    public function isNpcCorporation(int $corporationId): bool;
}