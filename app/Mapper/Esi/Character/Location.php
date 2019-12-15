<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 01.04.2017
 * Time: 00:56
 */

namespace Exodus4D\ESI\Mapper\Esi\Character;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Location extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'solar_system_id'   => ['system' => 'id'],

        'station_id'        => ['station' => 'id'],

        'structure_id'      => ['structure' => 'id']
    ];
}