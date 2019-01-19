<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 30.12.2018
 * Time: 01:09
 */

namespace Exodus4D\ESI\Lib\Stream;


use GuzzleHttp\Psr7\StreamDecoratorTrait;

class JsonStream implements JsonStreamInterface {

    // we need to "overwrite" the default Trait getContents() method
    // -> therefore we make it accessible as traitGetContents() and call it from
    // the new getContents() method
    use StreamDecoratorTrait {
        StreamDecoratorTrait::getContents as traitGetContents;
    }

    /**
     * @return mixed|string|null
     */
    public function getContents(){
        $contents = $this->traitGetContents();

        if($contents === ''){
            return null;
        }
        $decodedContents = \GuzzleHttp\json_decode($contents);

        if(json_last_error() !== JSON_ERROR_NONE){
            throw new \RuntimeException('Error trying to decode response: ' . json_last_error_msg());
        }

        return $decodedContents;
    }

    /**
     * we need to overwrite this because of Trait __toString() calls $this->Contents() which no longer returns a string
     * @return string
     */
    public function __toString(){
        try {
            if($this->isSeekable()){
                $this->seek(0);
            }
            return $this->traitGetContents();
        }catch (\Exception $e){
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            trigger_error('StreamDecorator::__toString exception: '
                . (string) $e, E_USER_ERROR);
            return '';
        }
    }
}