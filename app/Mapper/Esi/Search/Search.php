<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 21.04.2018
 * Time: 15:20
 */

namespace Exodus4D\ESI\Mapper\Esi\Search;

use data\mapper;

class Search extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'agent'             => 'agent',
        'alliance'          => 'alliance',
        'character'         => 'character',
        'constellation'     => 'constellation',
        'corporation'       => 'corporation',
        'faction'           => 'faction',
        'inventory_type'    => 'inventoryType',
        'region'            => 'region',
        'solar_system'      => 'solarSystem',
        'station'           => 'station'
    ];
}