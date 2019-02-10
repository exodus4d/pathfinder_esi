<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 10.01.2019
 * Time: 20:23
 */

namespace Exodus4D\ESI\Lib\Middleware\Cache;


use Exodus4D\ESI\Lib\Middleware\GuzzleCacheMiddleware;
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
     * This field is only used for serialize
     * Response::body is a stream and can't be serialized
     * @var string
     */
    protected $responseBody;

    /**
     * @var \DateTime
     */
    protected $staleAt;

    /**
     * @var \DateTime|null
     */
    protected $staleIfErrorTo;

    /**
     * @var \DateTime|null
     */
    protected $staleWhileRevalidateTo;

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Cached timestamp of staleAt variable
     * @var int
     */
    protected $timestampStale;

    /**
     * CacheEntry constructor.
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param \DateTime $staleAt
     * @param \DateTime|null $staleIfErrorTo
     * @param \DateTime|null $staleWhileRevalidateTo
     * @throws \Exception
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        \DateTime $staleAt,
        \DateTime $staleIfErrorTo = null,
        \DateTime $staleWhileRevalidateTo = null
    ){
        $this->dateCreated = new \DateTime();
        $this->request = $request;
        $this->response = $response;
        $this->staleAt = $staleAt;

        if($response->hasHeader('Cache-Control')){
            $cacheControlHeader = \GuzzleHttp\Psr7\parse_header($response->getHeader('Cache-Control'));

            if(is_null($staleIfErrorTo)){
                $staleIfError = (int)GuzzleCacheMiddleware::arrayKeyDeep($cacheControlHeader, 'stale-if-error');
                if($staleIfError){
                    $staleIfErrorTo = (new \DateTime(
                        '@'.($this->staleAt->getTimestamp() + $staleIfError)
                    ));
                }
            }

            if(is_null($staleWhileRevalidateTo)){
                $staleWhileRevalidate = (int)GuzzleCacheMiddleware::arrayKeyDeep($cacheControlHeader, 'stale-while-revalidate');
                if($staleWhileRevalidate){
                    $staleWhileRevalidateTo = (new \DateTime(
                        '@'.($this->staleAt->getTimestamp() + $staleWhileRevalidate)
                    ));
                }

            }
        }

        $this->staleIfErrorTo = $staleIfErrorTo;
        $this->staleWhileRevalidateTo = $staleWhileRevalidateTo;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse() : ResponseInterface {
        return $this->response->withHeader('Age', $this->getAge());
    }

    /**
     * @return ResponseInterface
     */
    public function getOriginalResponse() : ResponseInterface {
        return $this->response;
    }

    /**
     * @return RequestInterface
     */
    public function getOriginalRequest() : RequestInterface {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function isVaryEquals(RequestInterface $request) : bool {
        if($this->response->hasHeader('Vary')){
            if($this->request === null){
                return false;
            }

            foreach($this->getVaryHeaders() as $varyHeader){
                if(!$this->request->hasHeader($varyHeader) && !$request->hasHeader($varyHeader)){
                    // Absent from both
                    continue;
                }elseif($this->request->getHeaderLine($varyHeader) == $request->getHeaderLine($varyHeader)){
                    // Same content
                    continue;
                }
                return false;
            }
        }
        return true;
    }

    /**
     * get Vary HTTP Header values as flat array
     * @return array
     */
    public function getVaryHeaders() : array {
        $headers = [];
        if($this->response->getHeader('Vary')){
            $varyHeader = \GuzzleHttp\Psr7\parse_header($this->response->getHeader('Vary'));
            $headers = GuzzleCacheMiddleware::arrayFlattenByValue($varyHeader);
        }
        return $headers;
    }

    /**
     * @return \DateTime|null
     */
    public function getStaleAt() : ?\DateTime {
        return $this->staleAt;
    }

    /**
     * @return bool
     */
    public function isFresh() : bool {
        return !$this->isStale();
    }

    /**
     * @return bool
     */
    public function isStale() : bool {
        return $this->getStaleAge() > 0;
    }

    /**
     * @return int positive value equal staled
     */
    public function getStaleAge() : int {
        // This object is immutable
        if(is_null($this->timestampStale)){
            $this->timestampStale = $this->staleAt->getTimestamp();
        }
        return time() - $this->timestampStale;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function serveStaleIfError() : bool {
        return !is_null($this->staleIfErrorTo) && $this->staleIfErrorTo->getTimestamp() >= (new \DateTime())->getTimestamp();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function staleWhileValidate() : bool {
        return !is_null($this->staleWhileRevalidateTo) && $this->staleWhileRevalidateTo->getTimestamp() >= (new \DateTime())->getTimestamp();
    }

    /**
     * @return bool
     */
    public function hasValidationInformation() : bool {
        return $this->response->hasHeader('Etag') || $this->response->hasHeader('Last-Modified');
    }

    /**
     * @return int TTL in seconds (0 = infinite)
     */
    public function getTTL() : int {
        if($this->hasValidationInformation()){
            // No TTL if we have a way to re-validate the cache
            return 0;
        }

        if(!is_null($this->staleIfErrorTo)){
            // Keep it when stale if error
            $ttl = $this->staleIfErrorTo->getTimestamp() - time();
        }else{
            // Keep it until it become stale
            $ttl = $this->staleAt->getTimestamp() - time();
        }
        // Don't return 0, it's reserved for infinite TTL
        return $ttl !== 0 ? $ttl : -1;
    }

    /**
     * Age in seconds
     * @return int
     */
    public function getAge() : int {
        return time() - $this->dateCreated->getTimestamp();
    }

    /**
     * magic __sleep()
     * @return array
     */
    public function __sleep() : array {
        if($this->response !== null){
            // Stream/Resource can't be serialized... So we copy the content
            $this->responseBody = (string) $this->response->getBody();
            $this->response->getBody()->rewind();
        }
        return array_keys(get_object_vars($this));
    }

    /**
     * magic __wakeup()
     */
    public function __wakeup() : void {
        if($this->response !== null){
            // We re-create the stream of the response
            $this->response = $this->response->withBody(\GuzzleHttp\Psr7\stream_for($this->responseBody));
        }
    }

}