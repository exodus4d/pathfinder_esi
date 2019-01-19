<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 16:21
 */

namespace Exodus4D\ESI;


class SSO extends Ccp implements SsoInterface {

    /**
     * verify character data by "access_token"
     * -> get some basic information (like character id)
     * -> if more character information is required, use ESI "characters" endpoints request instead
     * @param string $accessToken
     * @return array
     */
    public function getVerifyCharacterData(string $accessToken) : array {
        $uri = $this->getVerifyUserEndpointURI();
        $characterData = [];

        $requestOptions = [
            'headers' => $this->getAuthHeader($accessToken, 'Bearer')
        ];

        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $characterData = (new namespace\Mapper\Sso\Character($response))->getData();
        }

        return $characterData;
    }

    /**
     * get a valid "access_token" for oAuth 2.0 verification
     * -> verify $authCode and get NEW "access_token"
     *      $requestParams['grant_type]     = 'authorization_code'
     *      $requestParams['code]           = 'XXXX'
     * -> request NEW "access_token" if isset:
     *      $requestParams['grant_type]     = 'refresh_token'
     *      $requestParams['refresh_token]  = 'XXXX'
     * @param array $credentials
     * @param array $requestParams
     * @param array $additionalOptions
     * @return array
     */
    public function getAccessData(array $credentials, array $requestParams = [], array $additionalOptions = []) : array {
        $uri = $this->getVerifyAuthorizationCodeEndpointURI();
        $accessData = [];

        $requestOptions = [
            'json' => $requestParams,
            'auth' => $credentials
        ];

        $response = $this->request('POST', $uri, $requestOptions, $additionalOptions)->getContents();

        if(!$response->error){
            $accessData = (new namespace\Mapper\Sso\Access($response))->getData();
        }

        return $accessData;
    }

    /**
     * @return string
     */
    protected function getVerifyUserEndpointURI() : string {
        return '/oauth/verify';
    }

    /**
     * @return string
     */
    protected function getVerifyAuthorizationCodeEndpointURI() : string {
        return '/oauth/token';
    }
}