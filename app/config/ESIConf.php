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
        'alliances' => [
            'GET' => '/v2/alliances/{x}/'
        ],
        'corporations' => [
            'GET' => '/v3/corporations/{x}/'
        ],
        'characters' => [
            'GET' => ' /v4/characters/{x}/',
            'affiliation' => [
                'POST' => '/v1/characters/affiliation/'
            ],
            'location' => [
                'GET' => '/v1/characters/{x}/location/'
            ],
            'ship' => [
                'GET' => '/v1/characters/{x}/ship/'
            ]
        ]
    ];

    /**
     * get an ESI endpoint path
     * @param array $path
     * @param array $params
     * @return string
     */
    static function getEndpoint($path = [], $params = []): string{
        $endpoint = '';

        $tmp = self::SWAGGER_SPEC;
        foreach($path as $key){
            if(array_key_exists($key, $tmp)){
                $tmp = $tmp[$key];
            }
        }

        if(is_string($tmp)){
            // replace vars
            $placeholder = '/\{x\}/';
            foreach($params as $param){
                $tmp = preg_replace($placeholder, $param, $tmp, 1);
            }

            $endpoint =  $tmp;
        }

        return $endpoint;
    }
}