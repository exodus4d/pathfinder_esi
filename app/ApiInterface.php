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

    public function setTimeout(float $timeout);

    public function setConnectTimeout(float $connectTimeout);

    public function setReadTimeout(float $readTimeout);

    public function setDebugLevel(int $debugLevel);

    public function setDebugRequests(bool $debugRequests);

    public function setUserAgent(string $userAgent);

    public function getUrl() : string;

    public function getTimeout() : float;

    public function getConnectTimeout() : float;

    public function getReadTimeout() : float;

    public function getDebugLevel() : int;

    public function getDebugRequests() : bool;

    public function getUserAgent() : string;
}