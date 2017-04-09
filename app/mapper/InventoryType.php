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

    protected static $map = [
        'id' => ['ship' => 'typeId'],

        'name' => ['ship' => 'typeName']
    ];
}