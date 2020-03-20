<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 27.12.2018
 * Time: 22:56
 */

namespace Exodus4D\ESI\Mapper\Sso;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Character extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'CharacterID'           => 'characterId',
        'CharacterName'         => 'characterName',
        'CharacterOwnerHash'    => 'characterOwnerHash',
        'ExpiresOn'             => 'expiresOn',
        'Scopes'                => 'scopes',
        'TokenType'             => 'tokenType',
        'IntellectualProperty'  => 'intellectualProperty'
    ];
}