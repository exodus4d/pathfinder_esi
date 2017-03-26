<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 16:37
 */

namespace Exodus4D\ESI;


class ESI {

    protected $test = '';

    /**
     * ESI constructor.
     */
    public function __construct(){
      //  $this->test = $param;
    }

    public function getCharacterLocationData($first, $second){
        return $first . ' - ' . $second;
    }
}