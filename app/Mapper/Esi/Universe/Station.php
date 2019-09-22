<?php
/**
 * Created by PhpStorm.
 * User: Exodus
 * Date: 09.04.2017
 * Time: 11:05
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use data\mapper;

class Station extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'station_id'        => 'id',
        'name'              => 'name',
        'system_id'         => 'systemId',
        'type_id'           => 'typeId',
        'race_id'           => 'raceId',
        'owner'             => 'ownerId',
        'services'          => 'services',
        'position'          => 'position',
        'x'                 => 'x',
        'y'                 => 'y',
        'z'                 => 'z'
    ];
}