<?php
/**
 * Created by PhpStorm.
 * User: exodu
 * Date: 10.06.2017
 * Time: 02:18
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Online extends mapper\AbstractIterator {

    protected static $map = [
        'online' => 'online',
        'last_login' => 'lastLogin',
        'last_logout' => 'lastLogout',
        'logins' => 'logins'
    ];
}