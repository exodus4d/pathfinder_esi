<?php

/**
 * Created by PhpStorm.
 * User: Exodus4D
 * Date: 27.03.2017
 * Time: 16:06
 */

namespace Exodus4D\ESI\Lib;

class WebClient extends \Web {

    const ERROR_STATUS_LOG                  = 'HTTP %s: \'%s\' | url: %s \'%s\'%s';

    /**
     * end of line
     * @var string
     */
    private $eol = "\r\n";

    /**
     * @param array $headers
     * @return int
     */
    protected function getStatusCodeFromHeaders($headers = []){
        $statusCode = 0;

        if(
        preg_match(
            '/HTTP\/1\.\d (\d{3}?)/',
            implode($this->eol, (array)$headers),
            $matches
        )
        ){
            $statusCode = (int)$matches[1];
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

    protected function getErrorMessageFromJsonResponse(int $code, string $method, string $url, $responseBody = null):string {
        $message = empty($responseBody->message) ?  @constant('Base::HTTP_' . $code) : $responseBody->message;
        $body = !is_null($responseBody) ? ' | body: ' . print_r($responseBody, true) : '';

        return sprintf(self::ERROR_STATUS_LOG, $code, $message, $method, $url, $body);
    }

    public function request($url,array $options = null){

        $response = parent::request($url, $options);

        $statusCode = $this->getStatusCodeFromHeaders( $response['headers'] );
        $statusType = $this->getStatusType($statusCode);

        switch($statusType){
            case 'err_client':
                $errorMsg = $this->getErrorMessageFromJsonResponse(
                    $statusCode,
                    $options['method'],
                    $url,
                    json_decode($response['body'])
                );

                var_dump(' ---- ');
                var_dump(@constant('Base::HTTP_' . $statusCode));
                var_dump($errorMsg);
                break;
            default:
        }

        return $response;
    }
}