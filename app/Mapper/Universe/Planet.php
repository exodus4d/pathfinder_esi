<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 19.05.2018
 * Time: 01:43
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Planet extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'planet_id'         => 'id',
        'name'              => 'name',
        'system_id'         => 'systemId',
        'type_id'           => 'typeId',
        'position'          => 'position',
        'x'                 => 'x',
        'y'                 => 'y',
        'z'                 => 'z'
    ];
}