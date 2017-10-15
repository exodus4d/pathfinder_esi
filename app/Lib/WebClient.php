<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 27.03.2017
 * Time: 16:06
 */

namespace Exodus4D\ESI\Lib;

class WebClient extends \Web {

    const ERROR_STATUS_LOG                      = 'HTTP %s: \'%s\' | url: %s \'%s\'%s';
    const ERROR_RESOURCE_LEGACY                 = 'Resource: %s has been marked as legacy.';
    const ERROR_RESOURCE_DEPRECATED             = 'Resource: %s has been marked as deprecated.';

    const REQUEST_METHODS                       = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * max number of CREST curls for a single endpoint until giving up...
     * this is because CREST is not very stable
     */
    const RETRY_COUNT_MAX                       = 3;

    /**
     * end of line
     * @var string
     */
    private $eol                                = "\r\n";

    /**
     * parse array with HTTP header data
     * @param array $headers
     * @return array
     */
    protected function parseHeaders(array $headers = []): array {
        $parsedHeaders = [];
        foreach($headers as $header){
            $parts = explode(':', $header, 2);
            $parsedHeaders[strtolower(trim($parts[0]))] = isset($parts[1]) ? trim($parts[1]) :  '';
        }
        return $parsedHeaders;
    }

    /**
     * @param array $headers
     * @return int
     */
    protected function getStatusCodeFromHeaders(array $headers = []): int {
        $statusCode = 0;
        foreach($headers as $key => $value){
            if(preg_match('/http\/1\.\d (\d{3}?)/i', $key, $matches)){
                $statusCode = (int)$matches[1];
                break;
            }
        }
        return $statusCode;
    }

    /**
     * get HTTP status type from HTTP status code (e.g. 404 )> 'err_client')
     * @param int $statusCode
     * @return string
     */
    protected function getStatusType(int $statusCode): string{
        $typeLevel = (int)substr($statusCode, 0, 1);
        switch($typeLevel){
            case 1:
                $statusType = 'info';
                break;
            case 2:
                $statusType = 'ok';
                break;
            case 3:
                $statusType = 'redirect';
                break;
            case 4:
                $statusType = 'err_client';
                break;
            case 5:
                $statusType = 'err_server';
                break;
            default:
                $statusType = 'unknown';
        }

        return $statusType;
    }

    /**
     * @param int $code
     * @param string $method
     * @param string $url
     * @param null $responseBody
     * @return string
     */
    protected function getErrorMessageFromJsonResponse(int $code, string $method, string $url, $responseBody = null):string {
        $message = empty($responseBody->message) ?  @constant('Base::HTTP_' . $code) : $responseBody->message;
        $body = !is_null($responseBody) ? ' | body: ' . print_r($responseBody, true) : '';

        return sprintf(self::ERROR_STATUS_LOG, $code, $message, $method, $url, $body);
    }

    /**
     * get Logger obj for given status type
     * @param string $statusType
     * @return \Log
     */
    public function getLogger(string $statusType): \Log{
        switch($statusType){
            case 'err_server':
                $logfile = 'esi_error_server';
                break;
            case 'err_client':
                $logfile = 'esi_error_client';
                break;
            case 'resource_legacy':
                $logfile = 'esi_resource_legacy';
                break;
            case 'resource_deprecated':
                $logfile = 'esi_resource_deprecated';
                break;
            default:
                $logfile = 'esi_error_unknown';
        }
        return new \Log($logfile . '.log');
    }

    /**
     * check response headers for warnings/errors and log them
     * @param array $headers
     * @param string $url
     */
    protected function checkResponseHeaders(array $headers, string $url){

        $statusCode = $this->getStatusCodeFromHeaders($headers);

        if( preg_grep('/^Warning: 199/i', $headers) ){
            $this->getLogger('resource_legacy')->write(sprintf(self::ERROR_RESOURCE_LEGACY, $url));
        }
        if( preg_grep('/^Warning: 299/i', $headers) ){
            $this->getLogger('resource_deprecated')->write(sprintf(self::ERROR_RESOURCE_DEPRECATED, $url));
        }


        if($statusCode >= 200 && $statusCode <= 500){
            $esiHeaders = array_filter($headers, function($key){
                return preg_match('/^x-esi-/i', $key);
            }, ARRAY_FILTER_USE_KEY);


            if(
                array_key_exists('x-esi-error-limit-remain', $esiHeaders) &&
                array_key_exists('x-esi-error-limit-reset', $esiHeaders)
            ){
                $f3 = \Base::instance();
                if(!$f3->exists('test_count', $esiErrorRate)){
                    $esiErrorRate = [];
                }
                $esiErrorRate[$url] = (int)$esiErrorRate[$url] + 1;

                arsort($esiErrorRate, SORT_NUMERIC );
var_dump($esiHeaders);
var_dump($esiErrorRate);
                $f3->set('test_count', $esiErrorRate, (int)$esiHeaders['x-esi-error-limit-reset']);
            }
/*
            if(array_key_exists('x-esi-error-limited', $esiHeaders)){
                // we are blocked until new error limit window opens
            }elseif(
                isset($esiHeaders['x-esi-error-limit-reset']) &&
                isset($esiHeaders['x-esi-error-limit-remain']) &&
                (int)$esiHeaders['x-esi-error-limit-remain'] > 0 &&
                (int)$esiHeaders['x-esi-error-limit-remain'] < 3 &&
            ){

            } */

        }

    }

    /**
     * check whether a HTTP request method is valid/given
     * @param $method
     * @return bool
     */
    public function checkRequestMethod($method): bool {
      $valid = false;
      if( in_array($method, self::REQUEST_METHODS) ){
          $valid = true;
      }
      return $valid;
    }

    /**
     * @param string $url
     * @param array|null $options
     * @param array $additionalOptions
     * @param int $retryCount
     * @return mixed|null
     */
    public function request( $url, array $options = null, $additionalOptions = [], $retryCount = 0){
        // retry same request until request limit is reached
        $retry = false;

        $response = parent::request($url, $options);

        $responseHeaders    = (array)$response['headers'];
        $responseBody       = json_decode($response['body']);

        // make sure return type is correct
        if(
            !is_array($responseBody) &&
            !is_bool($responseBody) &&
            !($responseBody instanceof \stdClass)
        ){
            $responseBody = null;
        }

        if( !empty($responseHeaders)){
            $parsedResponseHeaders = $this->parseHeaders($responseHeaders);
            // check response headers
            $this->checkResponseHeaders($parsedResponseHeaders, $url);
            $statusCode = $this->getStatusCodeFromHeaders($responseHeaders);
            $statusType = $this->getStatusType($statusCode);

            switch($statusType){
                case 'info':                                                // HTTP 1xx
                case 'ok':                                                  // HTTP 2xx
                    break;
                case 'err_client':                                          // HTTP 4xx
                    $errorMsg = $this->getErrorMessageFromJsonResponse(
                        $statusCode,
                        $options['method'],
                        $url,
                        $responseBody
                    );
                    $this->getLogger($statusType)->write($errorMsg);
                    break;
                case 'err_server':                                          // HTTP 5xx
                    $retry = true;

                    if( $retryCount == self::RETRY_COUNT_MAX ){
                        $errorMsg = $this->getErrorMessageFromJsonResponse(
                            $statusCode,
                            $options['method'],
                            $url,
                            $responseBody
                        );
                        $this->getLogger($statusType)->write($errorMsg);

                        // trigger error
                        if($additionalOptions['suppressHTTPErrors'] !== true){
                            $f3 = \Base::instance();
                            $f3->error($statusCode, $errorMsg);
                        }
                    }
                    break;
                default:
            }

            if(
                $retry &&
                $retryCount < self::RETRY_COUNT_MAX
            ){
                $retryCount++;
                $this->request($url, $options, $additionalOptions, $retryCount);
            }
        }

        return $responseBody;
    }
}