<?php


namespace Exodus4D\ESI\Client\EveScout;

use Exodus4D\ESI\Client;
use Exodus4D\ESI\Mapper\EveScout as Mapper;

class EveScout extends Client\AbstractApi implements EveScoutInterface {

    public function getTheraConnections() {
        $uri = 'https://www.eve-scout.com/api/wormholes';
        $connectionsData = [];
        $requestOptions = [];

        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if($response->error){
            $connectionsData['error'] = $response->error;
        }else{
            foreach((array)$response as $data){
                $connectionsData['connections'][(int)$data->id] = (new Mapper\Connection($data))->getData();
            }
        }

        return $connectionsData;
    }
}