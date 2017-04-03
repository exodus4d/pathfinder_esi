<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 02.04.2017
 * Time: 20:32
 */

namespace Exodus4D\ESI\Config;

class ESIConf extends \Prefab {

    public static $swaggerSpec = [
        'characters' => [
            'location' => [
                'GET' => '/characters/{x}/location/'
            ],
            'ship' => [
                'GET' => '/characters/{x}/ship/'
            ]
        ]
    ];

    static function getEndpointURL($path = [], $params = []): string{
        $endpoint = '';

        $tmp = self::$swaggerSpec;
        foreach($path as $key){
            if(array_key_exists($key, $tmp)){
                $tmp = $tmp[$key];
            }
        }

        if(is_string($tmp)){
            // replace vars
            var_dump('rep');
            var_dump($tmp);
            $placeholder = '/{x}/';
            foreach($params as $param){
                $tmp = preg_replace($placeholder, $param, $tmp, 1);
            }
            var_dump($tmp);
        }

        return $endpoint;
    }
}