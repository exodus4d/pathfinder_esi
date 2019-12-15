<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 14.04.2018
 * Time: 02:03
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Group extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'name'              => 'name',
        'published'         => 'published',
        'category_id'       => 'categoryId',
        'types'             => 'types'
    ];
}