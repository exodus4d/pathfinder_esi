<?php
/**
 * Created by PhpStorm.
 * User: Exodus
 * Date: 09.04.2017
 * Time: 11:05
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Station extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'station_id'        => 'id',
        'name'              => 'name',
        'system_id'         => 'systemId',
        'type_id'           => 'typeId',
        'owner'             => 'corporationId',
        'race_id'           => 'raceId',
        'services'          => 'services',
        'position'          => 'position',
        'x'                 => 'x',
        'y'                 => 'y',
        'z'                 => 'z'
    ];
}