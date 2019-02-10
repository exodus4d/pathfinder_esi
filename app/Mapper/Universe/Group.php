<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 14.04.2018
 * Time: 02:03
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Group extends mapper\AbstractIterator {

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