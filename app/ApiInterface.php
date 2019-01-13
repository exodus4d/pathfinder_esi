<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 20:45
 */

namespace Exodus4D\ESI;


interface ApiInterface {

    /**
     * @param string $url
     * @return mixed
     */
    public function setUrl(string $url);

    /**
     * @param string $acceptType
     * @return mixed
     */
    public function setAcceptType(string $acceptType);

    /**
     * @param float $timeout
     * @return mixed
     */
    public function setTimeout(float $timeout);

    /**
     * @param float $connectTimeout
     * @return mixed
     */
    public function setConnectTimeout(float $connectTimeout);

    /**
     * @param float $readTimeout
     * @return mixed
     */
    public function setReadTimeout(float $readTimeout);

    /**
     * @param $proxy
     * @return mixed
     */
    public function setProxy($proxy);

    /**
     * @param bool $verify
     * @return mixed
     */
    public function setVerify(bool $verify);

    /**
     * @param bool $debugRequests
     * @return mixed
     */
    public function setDebugRequests(bool $debugRequests);

    /**
     * @param int $debugLevel
     * @return mixed
     */
    public function setDebugLevel(int $debugLevel);

    /**
     * @param string $userAgent
     * @return mixed
     */
    public function setUserAgent(string $userAgent);

    /**
     * @param callable $newLog
     * @return mixed
     */
    public function setNewLog(callable $newLog);

    /**
     * @return string
     */
    public function getUrl() : string;

    /**
     * @return string
     */
    public function getAcceptType() : string;

    /**
     * @return float
     */
    public function getTimeout() : float;

    /**
     * @return float
     */
    public function getConnectTimeout() : float;

    /**
     * @return float
     */
    public function getReadTimeout() : float;

    /**
     * @return mixed
     */
    public function getProxy();

    /**
     * @return bool
     */
    public function getVerify() : bool;

    /**
     * @return bool
     */
    public function getDebugRequests() : bool;

    /**
     * @return int
     */
    public function getDebugLevel() : int;

    /**
     * @return string
     */
    public function getUserAgent() : string;

    /**
     * @return callable|null
     */
    public function getNewLog() : ?callable;

    /**
     * @return callable|null
     */
    public function getCachePool() : ?callable;
}