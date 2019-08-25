<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 01.04.2017
 * Time: 00:09
 */

namespace Exodus4D\ESI\Mapper\Esi\Alliance;

use data\mapper;

class Alliance extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'name'              => 'name',
        'ticker'            => 'ticker',
        'date_founded'      => 'dateFounded',
        'faction_id'        => 'factionId'
    ];
}