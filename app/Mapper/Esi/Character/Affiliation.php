<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 31.03.2017
 * Time: 22:00
 */

namespace Exodus4D\ESI\Mapper\Esi\Character;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Affiliation extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'character_id' => ['character' => 'id'],

        'corporation_id' => ['corporation' => 'id'],

        'alliance_id' => ['alliance' => 'id']
    ];
}