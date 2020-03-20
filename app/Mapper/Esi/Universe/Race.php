<?php


namespace Exodus4D\ESI\Mapper\Esi\Universe;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Race extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'race_id'           => 'id',
        'name'              => 'name',
        'description'       => 'description',
        'alliance_id'       => 'factionId'      // CCP failed here...
    ];
}