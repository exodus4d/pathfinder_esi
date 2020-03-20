<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 28.01.2019
 * Time: 22:38
 */

namespace Exodus4D\ESI\Client\GitHub;

use Exodus4D\ESI\Client;
use Exodus4D\ESI\Config\ConfigInterface;
use Exodus4D\ESI\Config\GitHub\Config;
use Exodus4D\ESI\Lib\RequestConfig;
use Exodus4D\ESI\Lib\WebClient;
use Exodus4D\ESI\Mapper;

class GitHub extends Client\AbstractApi implements GitHubInterface {

    /**
     * @param string $projectName e.g. "exodus4d/pathfinder"
     * @param int $count
     * @return RequestConfig
     */
    protected function getProjectReleasesRequest(string $projectName, int $count = 1) : RequestConfig {
        $requestOptions = [
            'query' => [
                'page' => 1,
                'per_page' => $count
            ]
        ];

        return new RequestConfig(
            WebClient::newRequest('GET', $this->getConfig()->getEndpoint(['releases', 'GET'], [$projectName])),
            $requestOptions,
            function($body) : array {
                $releasesData = [];
                if(!$body->error){
                    foreach((array)$body as $data){
                        $releasesData[] = (new Mapper\GitHub\Release($data))->getData();
                    }
                }

                return $releasesData;
            }
        );
    }

    /**
     * @param string $context
     * @param string $markdown
     * @return RequestConfig
     */
    protected function markdownToHtmlRequest(string $context, string $markdown) : RequestConfig {
        $requestOptions = [
            'json_enabled' => false, // disable JSON Middleware
            'json' => [
                'text' => $markdown,
                'mode' => 'gfm',
                'context' => $context
            ]
        ];

        return new RequestConfig(
            WebClient::newRequest('POST', $this->getConfig()->getEndpoint(['markdown', 'POST'])),
            $requestOptions,
            function($body) : string {
                $html = '';
                if(!$body->error){
                    $html = (string)$body;
                }

                return $html;
            }
        );
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig() : ConfigInterface {
        return ($this->config instanceof ConfigInterface) ? $this->config : $this->config = new Config();
    }
}