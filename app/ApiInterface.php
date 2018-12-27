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

    public function setTimeout(int $timeout);

    public function setDebugLevel(int $debugLevel);

    public function setDebugLogRequests(bool $logRequests);

    public function setUserAgent(string $userAgent);

    public function getUrl() : string;

    public function getTimeout() : int;

    public function getDebugLevel() : int;

    public function getDebugLogRequests() : bool;

    public function getUserAgent() : string;
}