<?php


namespace Exodus4D\ESI\Mapper\Esi\Sovereignty;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Map extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'system_id'         => 'systemId',
        'faction_id'        => 'factionId',
        'alliance_id'       => 'allianceId',
        'corporation_id'    => 'corporationId'
    ];
}