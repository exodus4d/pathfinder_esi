<?php
/**
 * Created by PhpStorm.
 * User: Exodus
 * Date: 09.04.2017
 * Time: 11:10
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class InventoryType extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'name'              => 'name'
    ];
}