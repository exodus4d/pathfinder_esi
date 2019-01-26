<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 27.03.2017
 * Time: 16:06
 */

namespace Exodus4D\ESI\Lib;


use Exodus4D\ESI\Lib\Stream\JsonStream;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Class WebClient
 * @package Exodus4D\ESI\Lib
 * @method Client send(RequestInterface $request, array $options = [])
 */
class WebClient {

    /**
     * @var Client|null
     */
    private $client                             = null;

    /**
     * WebClient constructor.
     * @param string $baseUri
     * @param array $config
     * @param \Closure|null $initStack modify handler Stack by ref
     */
    public function __construct(string $baseUri, array $config = [], ?\Closure $initStack = null){
        // use cURLHandler for all requests
        $handler = new CurlHandler();
        // new Stack for the Handler, manages Middleware for requests
        $stack = HandlerStack::create($handler);

        // init stack by reference
        if(is_callable($initStack)){
            $initStack($stack);
        }

        // Client default configuration
        $config['handler'] = $stack;
        $config['base_uri'] = $baseUri;

        // init client
        $this->client = new Client($config);
    }

    /**
     * @param string $method
     * @param string $uri
     * @return Request
     */
    public function newRequest(string $method, string $uri) : Request {
        return new Request($method, $uri);
    }

    /**
     * get new Response object
     * @param int $status
     * @param array $headers
     * @param null $body
     * @param string $version
     * @param string|null $reason
     * @return Response
     */
    public function newResponse(int $status = 200, array $headers = [], $body = null, string $version = '1.1', ?string $reason = null) : Response {
        return new Response($status, $headers, $body, $version, $reason);
    }

    /**
     * get error response with error message in body
     * -> wraps a GuzzleException (or any other Exception) into an error response
     * -> this class should handle any Exception thrown within Guzzle Context
     * @see http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions
     * @param \Exception $e
     * @param bool $json
     * @return Response
     */
    public function newErrorResponse(\Exception $e, bool $json = true) : Response {
        $message = [get_class($e)];

        if($e instanceof ConnectException){
            // hard fail! e.g. cURL connect error
            $message[] = $e->getMessage();
        }elseif($e instanceof ClientException){
            // 4xx response (e.g. 404 URL not found)
            $message[] = 'HTTP ' . $e->getCode();
            $message[] = $e->getMessage();
        }elseif($e instanceof ServerException){
            // 5xx response
            $message[] = 'HTTP ' . $e->getCode();
            $message[] = $e->getMessage();
        }elseif($e instanceof RequestException){
            // hard fail! e.g. cURL errors (connection timeout, DNS errors, etc.)
            $message[] = $e->getMessage();
        }elseif($e instanceof \Exception){
            // any other Exception type
            $message[] = $e->getMessage();
        }

        $body = (object)[];
        $body->error = implode(', ', $message);

        $bodyStream = \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\json_encode($body));

        if($json){
            // use JsonStream for as body
            $bodyStream = new JsonStream($bodyStream);
        }

        $response = $this->newResponse();
        $response = $response->withStatus(200, 'Error Response');
        $response = $response->withBody($bodyStream);

        return $response;
    }

    /**
     * pipe all functions right into the Client
     * @param string $name
     * @param array $arguments
     * @return array|mixed
     */
    public function __call(string $name, array $arguments = []){
        $return = [];

        if(is_object($this->client)){
            if( method_exists($this->client, $name) ){
                $return  = call_user_func_array([$this->client, $name], $arguments);
            }
        }

        return $return;
    }
}