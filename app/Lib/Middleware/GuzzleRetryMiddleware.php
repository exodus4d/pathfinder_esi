<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 19.01.2019
 * Time: 11:34
 */

namespace Exodus4D\ESI\Lib\Middleware;


class GuzzleRetryMiddleware extends \GuzzleRetry\GuzzleRetryMiddleware {

    public function __construct(callable $nextHandler, array $defaultOptions = []){
        var_dump('YESS!');
        parent::__construct($nextHandler, $defaultOptions);
    }
}