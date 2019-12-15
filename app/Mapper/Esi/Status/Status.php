<?php
/**
 * Created by PhpStorm.
 * User: Exodus
 * Date: 11.04.2017
 * Time: 16:50
 */

namespace Exodus4D\ESI\Mapper\Esi\Status;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Status extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'start_time'        => 'startTime',
        'players'           => 'playerCount',
        'server_version'    => 'serverVersion',
        'vip '              => 'isVip'
    ];
}