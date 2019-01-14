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
        $url = $this->getVerifyUserEndpointURL();
        $urlParts = parse_url($url);

        $characterData = [];

        $requestOptions = [
            'header' => [
                'Host' => $urlParts['host']
            ]
        ];

        $requestOptions['header'] += $this->getAuthHeader($accessToken, 'Bearer');

        $response = $this->request('GET', $url, $requestOptions);

        if( !empty($response) ){
            $characterData = (new namespace\Mapper\Sso\Character($response))->getData();
        }

        return $characterData;
    }

    /**
     * get a valid "access_token" for oAuth 2.0 verification
     * -> verify $authCode and get NEW "access_token"
     *      $urlParams['grant_type]     = 'authorization_code'
     *      $urlParams['code]           = 'XXXX'
     * -> request NEW "access_token" if isset:
     *      $urlParams['grant_type]     = 'refresh_token'
     *      $urlParams['refresh_token]  = 'XXXX'
     * @param array $credentials
     * @param array $urlParams
     * @param array $additionalOptions
     * @return array
     */
    public function getAccessData(array $credentials, array $urlParams = [], array $additionalOptions = []) : array {
        $uri = $this->getVerifyAuthorizationCodeEndpointURI();
        //$urlParts = parse_url($url);

        $accessData = [];

        $requestOptions = [
            'auth' => $credentials
        ];

        $response = $this->request('POST', $uri, $requestOptions, $additionalOptions)->getContents();
        var_dump('resp:');
        var_dump($response);
        die();

        /*
        $requestOptions = [
            'header' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Host' => $urlParts['host']
            ],
            'content' => $urlParams
        ];

        $requestOptions['header'] += $this->getAuthHeader($credentials);

        $response = $this->request('POST', $uri, $requestOptions, $additionalOptions);
*/
        if( !empty($response) ){
            $accessData = (new namespace\Mapper\Sso\Access($response))->getData();
        }

        return $accessData;
    }

    protected function getVerifyUserEndpointURL() : string {
        return '/oauth/verify';
    }

    protected function getVerifyAuthorizationCodeEndpointURI() : string {
        return '/oauth/token';
    }
}