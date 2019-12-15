<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 29.07.2017
 * Time: 14:49
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Region extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'region_id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'constellations' => 'constellations'
    ];
}