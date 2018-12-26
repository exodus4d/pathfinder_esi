<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 20:45
 */

namespace Exodus4D\ESI;


interface ApiInterface {

    public function setUrl(string $url);

    public function getUrl() : string;

    public function setTimeout(int $timeout);

    public function getTimeout() : int;

    public function setUserAgent(string $userAgent);

    public function getUserAgent() : string;
}