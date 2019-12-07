<?php


namespace Exodus4D\ESI\Config\EveScout;

use Exodus4D\ESI\Config\AbstractConfig;

class Config extends AbstractConfig {

    /**
     * @var array
     */
    protected static $spec = [
        'wormholes' => [
            'GET' => '/api/wormholes'
        ]
    ];
}