<?php


namespace Exodus4D\ESI\Mapper\Esi\Universe;

use data\mapper;

class Race extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'race_id'           => 'id',
        'name'              => 'name',
        'description'       => 'description',
        'alliance_id'       => 'allianceId'
    ];
}