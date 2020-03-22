<?php


namespace Exodus4D\ESI\Mapper\Esi\Character;

use Exodus4D\Pathfinder\Data\Mapper\AbstractIterator;

class Roles extends AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'roles'             => 'roles',
        'roles_at_base'     => 'rolesAtBase',
        'roles_at_hq'       => 'rolesAtHq',
        'roles_at_other'    => 'rolesAtOther'
    ];
}