<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 31.03.2017
 * Time: 19:16
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Character extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'id'                => 'id',
        'name'              => 'name',
        'birthday'          => 'birthday',
        'gender'            => 'gender',
        'security_status'   => 'securityStatus',

        'race_id'           => ['race' => 'id'],

        'bloodline_id'      => ['bloodline' => 'id'],

        'ancestry_id'       => ['ancestry' => 'id'],

        'corporation_id'    => ['corporation' => 'id'],

        'alliance_id'       => ['alliance' => 'id']
    ];
}