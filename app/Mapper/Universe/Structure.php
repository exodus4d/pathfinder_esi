<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 14.10.2017
 * Time: 15:40
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Structure extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'name'              => 'name',
        'solar_system_id'   => 'systemId',
        'type_id'           => 'typeId',
        'owner_id'          => 'ownerId',
        'position'          => 'position',
        'x'                 => 'x',
        'y'                 => 'y',
        'z'                 => 'z',
    ];
}