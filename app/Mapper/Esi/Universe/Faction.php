<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 30.03.2019
 * Time: 10:57
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Faction extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'faction_id'            => 'id',
        'name'                  => 'name',
        'description'           => 'description',
        'size_factor'           => 'sizeFactor',
        'station_count'         => 'stationCount',
        'station_system_count'  => 'stationSystemCount'
    ];
}