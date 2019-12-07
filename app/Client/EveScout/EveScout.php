<?php


namespace Exodus4D\ESI\Client\EveScout;

use Exodus4D\ESI\Client;
use Exodus4D\ESI\Config\ConfigInterface;
use Exodus4D\ESI\Config\EveScout\Config;
use Exodus4D\ESI\Mapper\EveScout as Mapper;

class EveScout extends Client\AbstractApi implements EveScoutInterface {

    /**
     * @return array
     */
    public function getTheraConnections() : array {
        $uri = $this->getConfig()->getEndpoint(['wormholes', 'GET']);
        $connectionsData = [];

        $response = $this->request('GET', $uri)->getContents();

        if($response->error){
            $connectionsData['error'] = $response->error;
        }else{
            foreach((array)$response as $data){
                $connectionsData['connections'][(int)$data->id] = (new Mapper\Connection($data))->getData();
            }
        }

        return $connectionsData;
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig() : ConfigInterface {
        return ($this->config instanceof ConfigInterface) ? $this->config : $this->config = new Config();
    }
}