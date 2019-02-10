<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 11.01.2019
 * Time: 22:53
 */

namespace Exodus4D\ESI\Lib\Middleware\Cache\Strategy;


use Exodus4D\ESI\Lib\Middleware\Cache\CacheEntry;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface CacheStrategyInterface {

    /**
     * Return a CacheEntry or null if no cache
     * @param RequestInterface $request
     * @return CacheEntry|null
     */
    public function fetch(RequestInterface $request) : ?CacheEntry;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool true if success
     */
    public function cache(RequestInterface $request, ResponseInterface $response) : bool;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool true if success
     */
    public function update(RequestInterface $request, ResponseInterface $response) : bool;

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function delete(RequestInterface $request) : bool;
}