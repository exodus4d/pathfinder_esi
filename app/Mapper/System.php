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

    protected static $map = [
        'id'                => 'id',
        'system_id'         => 'id',
        'name'              => 'name',
        'constellation_id'  => 'constellationId',
        'security_class'    => 'securityClass',
        'security_status'   => 'securityStatus',
        'position'          => 'position',
        'x'                 => 'x',
        'y'                 => 'y',
        'z'                 => 'z'
    ];
}