<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 14.04.2018
 * Time: 00:26
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Category extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'name'              => 'name',
        'published'         => 'published',
        'groups'            => 'groups'
    ];
}