<?php


namespace Exodus4D\ESI\Client\EveScout;

use Exodus4D\ESI\Client;

class EveScout extends Client\AbstractApi implements EveScoutInterface {

    public function getTheraConnections() {
        $uri = 'https://www.eve-scout.com/api/wormholes';
        $requestOptions = [];

        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        return $response;
    }
}