<?php


namespace Exodus4D\ESI\Config\GitHub;

use Exodus4D\ESI\Config\AbstractConfig;

class Config extends AbstractConfig {

    /**
     * @var array
     */
    protected static $spec = [
        'releases' => [
            'GET' =>  '/repos/{x}/releases'
        ],
        'markdown' => [
            'POST' =>  '/markdown'
        ]
    ];
}