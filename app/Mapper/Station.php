<?php
/**
 * Created by PhpStorm.
 * User: Exodus
 * Date: 09.04.2017
 * Time: 11:05
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Station extends mapper\AbstractIterator {

    protected static $map = [
        'id'                => ['station' => 'id'],
        'name'              => ['station' => 'name']
    ];
}