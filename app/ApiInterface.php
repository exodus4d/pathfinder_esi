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
     * @param int $characterId
     * @param string $accessToken
     * @return mixed
     */
    public function getCharacterLocationData($characterId, $accessToken);
}