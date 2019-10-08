<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 31.03.2017
 * Time: 23:00
 */

namespace Exodus4D\ESI\Mapper\Esi\Corporation;

use data\mapper;

class Corporation extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'name'              => 'name',
        'ticker'            => 'ticker',
        'date_founded'      => 'dateFounded',
        'member_count'      => 'memberCount',
        'faction_id'        => 'factionId',
        'alliance_id'       => 'allianceId'
    ];
}