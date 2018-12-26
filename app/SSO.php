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
        $accessData = [
            'accessToken' => 'testToken123'
        ];

        return $accessData;
    }
}