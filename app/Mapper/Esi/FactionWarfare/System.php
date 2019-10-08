<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 02.09.2019
 * Time: 18:00
 */

namespace Exodus4D\ESI\Mapper\Esi\FactionWarfare;

use data\mapper;

class System extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'solar_system_id'           => 'systemId',
        'owner_faction_id'          => 'ownerFactionId',
        'occupier_faction_id'       => 'occupierFactionId',
        'contested'                 => 'contested',
        'victory_points'            => 'victoryPoints',
        'victory_points_threshold'  => 'victoryPointsThreshold'
    ];
}