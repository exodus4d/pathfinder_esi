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

    public function setAcceptType(string $acceptType);

    public function setTimeout(float $timeout);

    public function setConnectTimeout(float $connectTimeout);

    public function setReadTimeout(float $readTimeout);

    public function setProxy($proxy);

    public function setVerify(bool $verify);

    public function setDebugRequests(bool $debugRequests);

    public function setDebugLevel(int $debugLevel);

    public function setUserAgent(string $userAgent);

    public function setNewLog(callable $newLog);

    public function getUrl() : string;

    public function getAcceptType() : string;

    public function getTimeout() : float;

    public function getConnectTimeout() : float;

    public function getReadTimeout() : float;

    public function getProxy();

    public function getVerify() : bool;

    public function getDebugRequests() : bool;

    public function getDebugLevel() : int;

    public function getUserAgent() : string;

    public function getNewLog() : ?callable;

    public function getCachePool() : ?callable;
}