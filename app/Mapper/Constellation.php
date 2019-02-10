<?php
/**
 * Created by PhpStorm.
 * User: exodus
 * Date: 29.07.2017
 * Time: 17:03
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Constellation extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'constellation_id' => 'id',
        'name' => 'name',
        'region_id' => 'regionId',
        'position' => 'position',
        'x' => 'x',
        'y' => 'y',
        'z' => 'z',
        'systems' => 'systems'
    ];
}