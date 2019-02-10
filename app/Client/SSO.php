<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 16:21
 */

namespace Exodus4D\ESI\Client;


use Exodus4D\ESI\Mapper;

class SSO extends AbstractCcp implements SsoInterface {

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
            $characterData = (new Mapper\Sso\Character($response))->getData();
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
     * @return array
     */
    public function getAccessData(array $credentials, array $requestParams = []) : array {
        $uri = $this->getVerifyAuthorizationCodeEndpointURI();
        $accessData = [];

        $requestOptions = [
            'json' => $requestParams,
            'auth' => $credentials
        ];

        $response = $this->request('POST', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $accessData = (new Mapper\Sso\Access($response))->getData();
        }

        return $accessData;
    }

    /**
     * @return string
     */
    public function getAuthorizationEndpointURI() : string {
        return '/oauth/authorize';
    }

    /**
     * @return string
     */
    public function getVerifyUserEndpointURI() : string {
        return '/oauth/verify';
    }

    /**
     * @return string
     */
    public function getVerifyAuthorizationCodeEndpointURI() : string {
        return '/oauth/token';
    }
}