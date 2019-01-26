<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 19.05.2018
 * Time: 03:47
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Stargate extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'stargate_id'       => 'id',
        'name'              => 'name',
        'system_id'         => 'systemId',
        'type_id'           => 'typeId',
        'destination'       => 'destination',
        'position'          => 'position',
        'x'                 => 'x',
        'y'                 => 'y',
        'z'                 => 'z'
    ];
}