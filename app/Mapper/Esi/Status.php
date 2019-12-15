<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 03.02.2019
 * Time: 00:22
 */

namespace Exodus4D\ESI\Mapper\Esi;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Status extends AbstractIterator {

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

    /**
     * map iterator
     * @return array
     */
    public function getData(){

        $normalize = function(\Iterator $iterator){
            return preg_replace('/\/{(\w+)}/', '/{x}', $iterator->current());
        };

        self::$map['route'] = $normalize;

        return parent::getData();
    }

}