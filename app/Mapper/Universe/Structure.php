<?php
/**
 * Created by PhpStorm.
 * User: exodu
 * Date: 14.10.2017
 * Time: 15:40
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Structure extends mapper\AbstractIterator {

    protected static $map = [
        'name'              => 'name',
        'solar_system_id'   => ['system' => 'id'],
        'type_id'           => ['type' => 'id'],
        'position' => 'position',
        'x' => 'x',
        'y' => 'y',
        'z' => 'z',
    ];
}