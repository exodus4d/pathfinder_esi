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
    public function getCharacterData(int $characterId);

    /**
     * @param int $characterId
     * @param string $accessToken
     * @return array
     */
    public function getCharacterLocationData($characterId, $accessToken);

    /**
     * @param int $corporationId
     * @return array
     */
    public function getCorporationData(int $corporationId): array;

}