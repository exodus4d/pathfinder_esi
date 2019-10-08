<?php


namespace Exodus4D\ESI\Mapper\Esi\Character;

use data\mapper;

class CharacterClone extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'location_id'   => ['location' => 'id'],
        'location_type' => ['location' => 'type']
    ];
}