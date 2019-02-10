<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 28.01.2019
 * Time: 22:38
 */

namespace Exodus4D\ESI\Client;


use Exodus4D\ESI\Mapper;

class Github extends AbstractApi implements GitHubInterface {

    /**
     * @param string $projectName
     * @param int $count
     * @return array
     */
    public function getProjectReleases(string $projectName, int $count = 1) : array {
        $uri = $this->getReleasesEndpointURI($projectName);
        $releasesData = [];

        $requestOptions = [
            'query' => [
                'page' => 1,
                'per_page' => $count
            ]
        ];

        $response = $this->request('GET', $uri, $requestOptions)->getContents();

        if(!$response->error){
            foreach((array)$response as $data){
                $releasesData[] = (new Mapper\GitHub\Release($data))->getData();
            }
        }

        return $releasesData;
    }

    /**
     * @param string $context
     * @param string $markdown
     * @return string
     */
    public function markdownToHtml(string $context, string $markdown) : string {
        $uri = $this->getMarkdownToHtmlEndpointURI();
        $html = '';

        $requestOptions = [
            'json_enabled' => false, // disable JSON Middleware
            'json' => [
                'text' => $markdown,
                'mode' => 'gfm',
                'context' => $context
            ]
        ];

        $response = $this->request('POST', $uri, $requestOptions)->getContents();

        if(!$response->error){
            $html = (string)$response;
        }

        return $html;
    }

    /**
     * @param string $projectName e.g. "exodus4d/pathfinder"
     * @return string
     */
    protected function getReleasesEndpointURI(string $projectName) : string {
        return '/repos/' . $projectName . '/releases';
    }

    /**
     * @return string
     */
    protected function getMarkdownToHtmlEndpointURI() : string {
        return '/markdown';
    }
}