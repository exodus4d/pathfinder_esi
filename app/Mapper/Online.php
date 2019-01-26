<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 10.06.2017
 * Time: 02:18
 */

namespace Exodus4D\ESI\Mapper;

use data\mapper;

class Online extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'online' => 'online',
        'logins' => 'logins'
    ];

    /**
     * map iterator
     * @return array
     */
    public function getData(){

        $convertTime = function(\Iterator $iterator){
            return (new \DateTime($iterator->current()))->format('Y-m-d H:i:s');
        };

        self::$map['last_login'] = $convertTime;
        self::$map['last_logout'] = $convertTime;

        $data = parent::getData();
        $data = $this->camelCaseKeys($data);
        return $data;
    }
}