<?php
/**
 * Created by PhpStorm.
 * User: exodu
 * Date: 10.06.2017
 * Time: 02:18
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Online extends mapper\AbstractIterator {

    protected static $map = [
        'online' => 'online',
        'logins' => 'logins'
    ];

    /**
     * map iterator
     * @return array
     */
    public function getData(){

        self::$map['last_login'] = function($iterator){
            $trueSec = $iterator['last_login'] . ' aa';
            return $trueSec;
        };

        self::$map['last_logout'] = function($iterator){
            $trueSec = $iterator['last_logout'] . ' bb';
            return $trueSec;
        };

        $data = parent::getData();
        $data = $this->camelCaseKeys($data);
        return $data;
    }
}