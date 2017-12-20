<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 02.04.2017
 * Time: 20:32
 */

namespace Exodus4D\ESI\Config;

class ESIConf extends \Prefab {

    const SWAGGER_SPEC  = [
        'status' => [
            'GET' => '/v1/status/'
        ],
        'alliances' => [
            'GET' => '/v2/alliances/{x}/'
        ],
        'corporations' => [
            'GET' => '/v3/corporations/{x}/',
            'npccorps' => [
                'GET' => '/v1/corporations/npccorps/'
            ],
            'roles' => [
                'GET' => '/v1/corporations/{x}/roles/'
            ]
        ],
        'characters' => [
            'GET' => '/v4/characters/{x}/',
            'affiliation' => [
                'POST' => '/v1/characters/affiliation/'
            ],
            'location' => [
                'GET' => '/v1/characters/{x}/location/'
            ],
            'ship' => [
                'GET' => '/v1/characters/{x}/ship/'
            ],
            'online' => [
                'GET' => '/v2/characters/{x}/online/'
            ]
        ],
        'universe' => [
            'names' => [
                'POST' => '/v2/universe/names/'
            ],
            'system_jumps' => [
                'GET' => ' /v1/universe/system_jumps/'
            ],
            'system_kills' => [
                'GET' => ' /v1/universe/system_kills/'
            ],
            'regions' => [
                'GET' => '/v1/universe/regions/{x}/',
                'list' => [
                    'GET' => '/v1/universe/regions/'
                ]
            ],
            'constellations' => [
                'GET' => '/v1/universe/constellations/{x}/',
                'list' => [
                    'GET' => '/v1/universe/constellations/'
                ]
            ],
            'structures' => [
                'GET' => '/v1/universe/structures/{x}/',
            ],
            'types' => [
                'GET' => '/v3/universe/types/{x}/'
            ]
        ],
        'ui' => [
            'autopilot' => [
                'waypoint' => [
                    'POST' => '/v2/ui/autopilot/waypoint/'
                ]
            ],
            'openwindow' => [
                'information' => [
                    'POST' => '/v1/ui/openwindow/information/'
                ]
            ]
        ]
    ];

    /**
     * get an ESI endpoint path
     * @param array $path
     * @param array $placeholders
     * @return string
     */
    static function getEndpoint($path = [], $placeholders = []): string{
        $endpoint = '';

        $tmp = self::SWAGGER_SPEC;
        foreach($path as $key){
            if(array_key_exists($key, $tmp)){
                $tmp = $tmp[$key];
            }
        }

        if(is_string($tmp)){
            // replace vars
            $pattern = '/\{x\}/';
            foreach($placeholders as $placeholder){
                $tmp = preg_replace($pattern, $placeholder, $tmp, 1);
            }

            $endpoint =  trim($tmp);
        }

        return $endpoint;
    }
}