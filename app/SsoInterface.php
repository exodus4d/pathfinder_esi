<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 16:23
 */

namespace Exodus4D\ESI;


interface SsoInterface {

    /**
     * @param string $accessToken
     * @return array
     */
    public function getVerifyCharacterData(string $accessToken) : array;

    /**
     * @param string $authHeader
     * @param array $urlParams
     * @return array
     */
    public function getAccessData(string $authHeader, array $urlParams = []) : array;

}