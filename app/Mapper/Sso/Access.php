<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 27.12.2018
 * Time: 13:58
 */

namespace Exodus4D\ESI\Mapper\Sso;

use data\mapper;

class Access extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'access_token'      => 'accessToken',
        'token_type'        => 'tokenType',
        'expires_in'        => 'expiresIn',
        'refresh_token'     => 'refreshToken'
    ];
}