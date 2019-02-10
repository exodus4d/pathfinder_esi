<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 19.05.2018
 * Time: 00:21
 */

namespace Exodus4D\ESI\Mapper\Universe;

use data\mapper;

class Star extends mapper\AbstractIterator {

    /**
     * @var array
     */
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