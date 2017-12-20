<?php
/**
 * Created by PhpStorm.
 * User: exodu
 * Date: 29.07.2017
 * Time: 14:49
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Region extends mapper\AbstractIterator {

    protected static $map = [
        'region_id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'constellations' => 'constellations'
    ];
}