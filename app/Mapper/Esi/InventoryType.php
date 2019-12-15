<?php
/**
 * Created by PhpStorm.
 * User: Exodus
 * Date: 09.04.2017
 * Time: 11:10
 */

namespace Exodus4D\ESI\Mapper\Esi;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class InventoryType extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'name'              => 'name'
    ];
}