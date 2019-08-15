<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 14.04.2018
 * Time: 00:26
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use data\mapper;

class Category extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'name'              => 'name',
        'published'         => 'published',
        'groups'            => 'groups'
    ];
}