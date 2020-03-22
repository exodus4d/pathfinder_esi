<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 01.04.2017
 * Time: 13:36
 */

namespace Exodus4D\ESI\Mapper\Esi\Character;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Ship extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'ship_type_id'      => ['ship' => 'typeId'],

        'ship_item_id'      => ['ship' => 'id'],

        'ship_name'         => ['ship' => 'name']
    ];
}