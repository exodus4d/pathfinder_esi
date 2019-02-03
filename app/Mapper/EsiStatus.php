<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 03.02.2019
 * Time: 00:22
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class EsiStatus extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'endpoint'      => 'endpoint',
        'method'        => 'method',
        'route'         => 'route',
        'status'        => 'status',
        'tags'          => 'tags'
    ];
}