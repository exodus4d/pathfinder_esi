<?php
/**
 * Created by PhpStorm.
 * User: exodu
 * Date: 19.05.2018
 * Time: 00:21
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Star extends mapper\AbstractIterator {

    protected static $map = [
        'name'              => 'name',
        'type_id'           => 'typeId',
        'age'               => 'age',
        'luminosity'        => 'luminosity',
        'radius'            => 'radius',
        'spectral_class'    => 'spectralClass',
        'temperature'       => 'temperature'
    ];
}