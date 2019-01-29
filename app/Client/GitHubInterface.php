<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 28.01.2019
 * Time: 22:38
 */

namespace Exodus4D\ESI\Client;


interface GitHubInterface {

    /**
     * @param string $projectName
     * @return array
     */
    public function getProjectReleases(string $projectName) : array;

    /**
     * @param string $context
     * @param string $markdown
     * @return string
     */
    public function markdownToHtml(string $context, string $markdown) : string;

}