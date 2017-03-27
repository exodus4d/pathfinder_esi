<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 27.03.2017
 * Time: 16:06
 */

namespace Exodus4D\ESI\Lib;

class Web extends \Web {


    public function request($url,array $options = null){

        $response = parent::request($url, $options);

        return $response;
    }
}