<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 15.08.2019
 * Time: 22:00
 */

namespace Exodus4D\ESI\Mapper\Esi\Dogma;

use data\mapper;

class Attribute extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'attribute_id'      => 'id',
        'default_value'     => 'defaultValue',
        'description'       => 'description',
        'display_name'      => 'displayName',
        'high_is_good'      => 'highIsGood',
        'icon_id'           => 'iconId',
        'name'              => 'name',
        'published'         => 'published',
        'stackable'         => 'stackable',
        'unit_id'           => 'unitId'
    ];
}