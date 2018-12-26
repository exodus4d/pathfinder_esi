<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 16:21
 */

namespace Exodus4D\ESI;


class SSO extends Api implements SsoInterface {

    public function getAccessData() : array {
        $url = $this->getAuthorizationEndpointURL();


        $accessData = [
            'accessToken' => 'testToken123'
        ];
        $response = $this->request($url, 'POST');

        return $accessData;
    }

    protected function getAuthorizationEndpointURL() : string {
        return $this->getUrl() . '/oauth/authorize';
    }


}