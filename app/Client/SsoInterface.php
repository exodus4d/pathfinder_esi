<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 16:23
 */

namespace Exodus4D\ESI\Client;


interface SsoInterface {

    /**
     * @param string $accessToken
     * @return array
     */
    public function getVerifyCharacterData(string $accessToken) : array;

    /**
     * @param array $credentials
     * @param array $requestParams
     * @return array
     */
    public function getAccessData(array $credentials, array $requestParams = []) : array;

    /**
     * @return string
     */
    public function getAuthorizationEndpointURI() : string;

    /**
     * @return string
     */
    public function getVerifyUserEndpointURI() : string;

    /**
     * @return string
     */
    public function getVerifyAuthorizationCodeEndpointURI() : string;

}