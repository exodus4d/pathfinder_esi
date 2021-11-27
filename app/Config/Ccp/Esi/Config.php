<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 02.04.2017
 * Time: 20:32
 */

namespace Exodus4D\ESI\Config\Ccp\Esi;

use Exodus4D\ESI\Config\AbstractConfig;

class Config extends AbstractConfig {

    /**
     * Swagger endpoint configuration
     */
    protected static $spec = [
        'meta' => [
            'status' => [
                'GET' => '/status.json'
            ]
        ],
        'status' => [
            'GET' => '/v1/status/'
        ],
        'alliances' => [
            'GET' => '/v3/alliances/{x}/'
        ],
        'corporations' => [
            'GET' => '/v4/corporations/{x}/',
            'npccorps' => [
                'GET' => '/v1/corporations/npccorps/'
            ],
            'roles' => [
                'GET' => '/v1/corporations/{x}/roles/'
            ]
        ],
        'characters' => [
            'GET' => '/v5/characters/{x}/',
            'affiliation' => [
                'POST' => '/v1/characters/affiliation/'
            ],
            'clones' => [
                'GET' => '/v3/characters/{x}/clones/'
            ],
            'location' => [
                'GET' => '/v1/characters/{x}/location/'
            ],
            'ship' => [
                'GET' => '/v1/characters/{x}/ship/'
            ],
            'online' => [
                'GET' => '/v2/characters/{x}/online/'
            ],
            'roles' => [
                'GET' => '/v2/characters/{x}/roles/'
            ]
        ],
        'dogma' => [
            'attributes' => [
                'GET' => '/v1/dogma/attributes/{x}/'
            ]
        ],
        'fw' => [
            'systems' => [
                'GET' => '/v2/fw/systems/'
            ]
        ],
        'universe' => [
            'names' => [
                'POST' => '/v3/universe/names/'
            ],
            'factions' => [
                'list' => [
                    'GET' => '/v2/universe/factions/'
                ]
            ],
            'system_jumps' => [
                'GET' => '/v1/universe/system_jumps/'
            ],
            'system_kills' => [
                'GET' => '/v2/universe/system_kills/'
            ],
            'races' => [
                'list' => [
                    'GET' => '/v1/universe/races/'
                ]
            ],
            'regions' => [
                'GET' => '/v1/universe/regions/{x}/',
                'list' => [
                    'GET' => '/v1/universe/regions/'
                ]
            ],
            'constellations' => [
                'GET' => '/v1/universe/constellations/{x}/',
                'list' => [
                    'GET' => '/v1/universe/constellations/'
                ]
            ],
            'systems' => [
                'GET' => '/v4/universe/systems/{x}/',
                'list' => [
                    'GET' => '/v1/universe/systems/'
                ]
            ],
            'stars' => [
                'GET' => '/v1/universe/stars/{x}/'
            ],
            'planets' => [
                'GET' => '/v1/universe/planets/{x}/'
            ],
            'stargates' => [
                'GET' => '/v1/universe/stargates/{x}/'
            ],
            'stations' => [
                'GET' => '/v2/universe/stations/{x}/'
            ],
            'structures' => [
                'GET' => '/v2/universe/structures/{x}/'
            ],
            'categories' => [
                'GET' => '/v1/universe/categories/{x}/',
                'list' => [
                    'GET' => '/v1/universe/categories/'
                ]
            ],
            'groups' => [
                'GET' => '/v1/universe/groups/{x}/',
                'list' => [
                    'GET' => '/v1/universe/groups/'
                ]
            ],
            'types' => [
                'GET' => '/v3/universe/types/{x}/'
            ]
        ],
        'routes' => [
            'GET' => '/v1/route/{x}/{x}/'
        ],
        'ui' => [
            'autopilot' => [
                'waypoint' => [
                    'POST' => '/v2/ui/autopilot/waypoint/'
                ]
            ],
            'openwindow' => [
                'information' => [
                    'POST' => '/v1/ui/openwindow/information/'
                ]
            ]
        ],
        'sovereignty' => [
            'map' => [
                'GET' => '/v1/sovereignty/map/'
            ]
        ],
        'search' => [
            'GET' => '/v2/search/'
        ]
    ];
}
