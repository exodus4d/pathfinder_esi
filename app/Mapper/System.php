<?php
/**
 * Created by PhpStorm.
 * User: Exodus
 * Date: 09.04.2017
 * Time: 11:10
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class System extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'system_id'         => 'id',
        'name'              => 'name',
        'constellation_id'  => 'constellationId',
        'security_class'    => 'securityClass',
        'security_status'   => 'securityStatus',
        'star_id'           => 'starId',
        'planets'           => 'planets',
        'planet_id'         => 'planetId',
        'moons'             => 'moons',
        'asteroid_belts'    => 'asteroidBelts',
        'stargates'         => 'stargates',
        'position'          => 'position',
        'x'                 => 'x',
        'y'                 => 'y',
        'z'                 => 'z'
    ];
}