<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 30.12.2018
 * Time: 01:09
 */

namespace Exodus4D\ESI\Lib\Stream;


use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

class JsonStream implements StreamInterface {

    // we need to "overwrite" the default Trait getContents() method
    // -> therefore we make it usable as traitGetContents() and call it from
    // the new getContents() method
    use StreamDecoratorTrait {
        StreamDecoratorTrait::getContents as traitGetContents;
    }

    /**
     * @return mixed|string|null
     */
    public function getContents(){
        $contents = (string) $this->traitGetContents();

        if($contents === ''){
            return null;
        }
        $decodedContents = \GuzzleHttp\json_decode($contents);

        if(json_last_error() !== JSON_ERROR_NONE){
            throw new \RuntimeException('Error trying to decode response: ' . json_last_error_msg());
        }

        return $decodedContents;
    }
}