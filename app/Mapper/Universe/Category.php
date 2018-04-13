<?php
/**
 * Created by PhpStorm.
 * User: exodu
 * Date: 14.04.2018
 * Time: 00:26
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Category extends mapper\AbstractIterator {

    protected static $map = [
        'name'              => 'name',
        'published'         => 'published',
        'groupso'            => 'groups'
    ];
}