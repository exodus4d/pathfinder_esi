<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 31.03.2017
 * Time: 19:16
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Character extends mapper\AbstractIterator {

    protected static $map = [
        'name' => 'name',

        'corporation_id' => ['corp' => 'id'],
    ];
}