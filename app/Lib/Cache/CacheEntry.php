<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 10.01.2019
 * Time: 20:23
 */

namespace Exodus4D\ESI\Lib\Cache;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CacheEntry {

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var \DateTime
     */
    protected $staleAt;

    /**
     * CacheEntry constructor.
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param \DateTime $staleAt
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        \DateTime $staleAt
    ){
        $this->request = $request;
        $this->response = $response;
        $this->staleAt = $staleAt;
    }
}