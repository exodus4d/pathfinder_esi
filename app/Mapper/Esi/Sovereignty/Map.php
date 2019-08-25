<?php


namespace Exodus4D\ESI\Mapper\Esi\Sovereignty;

use data\mapper;

class Map extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'system_id'         => 'systemId',
        'faction_id'        => 'factionId',
        'corporation_id'    => 'corporationId',
        'alliance_id'       => 'allianceId'
    ];
}