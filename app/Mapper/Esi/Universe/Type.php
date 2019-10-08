<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 14.10.2017
 * Time: 15:40
 */

namespace Exodus4D\ESI\Mapper\Esi\Universe;

use data\mapper;

class Type extends mapper\AbstractIterator {

    /**
     * @var array
     */
    protected static $map = [
        'type_id'           => 'id',
        'name'              => 'name',
        'description'       => 'description',
        'published'         => 'published',
        'group_id'          => 'groupId',
        'market_group_id'   => 'marketGroupId',
        'radius'            => 'radius',
        'volume'            => 'volume',
        'packaged_volume'   => 'packagedVolume',
        'capacity'          => 'capacity',
        'portion_size'      => 'portionSize',
        'mass'              => 'mass',
        'graphic_id'        => 'graphicId',
        'dogma_attributes'  => 'dogma_attributes'
    ];

    /**
     * map iterator
     * @return array
     */
    public function getData(){

        $normalizeDogmaAttributes = function(\Iterator $iterator){
            $dogmaAttributes = [];
            foreach((array)$iterator->current() as $data){
                $dogmaAttributes[] = [
                    'attributeId'   => (int)$data->attribute_id,
                    'value'         => (float)$data->value
                ];
            }
            return $dogmaAttributes;
        };

        self::$map['dogma_attributes'] = $normalizeDogmaAttributes;

        return parent::getData();
    }
}