<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 16:21
 */

namespace Exodus4D\ESI;


class SSO extends Api implements SsoInterface {

    public function getAccessData(string $credentials, array $urlParams = []) : array {
        $url = $this->getAuthorizationEndpointURL();


        $accessData = [
            'accessToken' => 'testToken123'
        ];
        $requestOptions = [
            'header' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'content' => $urlParams
        ];

        $requestOptions['header'] += $this->getAuthHeader($credentials);

        $response = $this->request('POST', $url, $requestOptions);

        return $accessData;
    }

    protected function getAuthorizationEndpointURL() : string {
        return $this->getUrl() . '/oauth/authorize';
    }


}