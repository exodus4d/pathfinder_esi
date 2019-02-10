<?php
/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 26.03.2017
 * Time: 20:45
 */

namespace Exodus4D\ESI\Client;


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
     * @param $decodeContent
     * @return mixed
     */
    public function setDecodeContent($decodeContent);

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
     * @param \Closure $cachePool
     * @return mixed
     */
    public function setCachePool(\Closure $cachePool);

    /**
     * @param \Closure $newLog
     * @return mixed
     */
    public function setNewLog(\Closure $newLog);

    /**
     * @param \Closure $isLoggable
     * @return mixed
     */
    public function setIsLoggable(\Closure $isLoggable);

    /**
     * @param bool $logEnabled
     * @return mixed
     */
    public function setLogEnabled(bool $logEnabled);

    /**
     * @param bool $logStats
     * @return mixed
     */
    public function setLogStats(bool $logStats);

    /**
     * @param bool $logCache
     * @return mixed
     */
    public function setLogCache(bool $logCache);

    /**
     * @param string $logCacheHeader
     * @return mixed
     */
    public function setLogCacheHeader(string $logCacheHeader);

    /**
     * @param bool $logAllStatus
     * @return mixed
     */
    public function setLogAllStatus(bool $logAllStatus);

    /**
     * @param string $logFile
     * @return mixed
     */
    public function setLogFile(string $logFile);

    /**
     * @param bool $cacheEnabled
     * @return mixed
     */
    public function setCacheEnabled(bool $cacheEnabled);

    /**
     * @param bool $cacheDebug
     * @return mixed
     */
    public function setCacheDebug(bool $cacheDebug);

    /**
     * @param string $cacheDebugHeader
     * @return mixed
     */
    public function setCacheDebugHeader(string $cacheDebugHeader);

    /**
     * @param bool $retryEnabled
     * @return mixed
     */
    public function setRetryEnabled(bool $retryEnabled);

    /**
     * @param string $logFile
     * @return mixed
     */
    public function setRetryLogFile(string $logFile);

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
    public function getDecodeContent();

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
     * @return \Closure|null
     */
    public function getCachePool() : ?\Closure;

    /**
     * @return \Closure|null
     */
    public function getNewLog() : ?\Closure;

}