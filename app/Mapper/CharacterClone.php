<?php


namespace Exodus4D\ESI\Mapper;

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