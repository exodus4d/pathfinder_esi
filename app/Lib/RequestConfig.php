<?php


namespace Exodus4D\ESI\Lib;


use GuzzleHttp\Psr7\Request;

class RequestConfig {

    /**
     * default for: request options
     */
    const DEFAULT_OPTIONS = [];

    /**
     * @var Request
     */
    private $request;

    /**
     * array of options send along with the request
     * @var array
     */
    private $options = self::DEFAULT_OPTIONS;

    /**
     * optional callback for response body formatting
     * @var callable|null
     */
    private $formatter;

    /**
     * RequestConfig constructor.
     * @param Request       $request
     * @param array         $options
     * @param callable|null $formatter
     */
    public function __construct(Request $request, array $options = self::DEFAULT_OPTIONS, ?callable $formatter = null){
        $this->setRequest($request);
        $this->setOptions($options);
        $this->setFormatter($formatter);
    }

    /**
     * @return Request
     */
    public function getRequest() : Request{
        return $this->request;
    }

    /**
     * @param Request $request
     * @return RequestConfig
     */
    public function setRequest(Request $request) : RequestConfig{
        $this->request = $request;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions() : array{
        return $this->options;
    }

    /**
     * @param array $options
     * @return RequestConfig
     */
    public function setOptions(array $options) : RequestConfig{
        $this->options = $options;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getFormatter() : ?callable{
        return $this->formatter;
    }

    /**
     * @param callable|null $formatter
     * @return RequestConfig
     */
    public function setFormatter(?callable $formatter) : RequestConfig{
        $this->formatter = $formatter;
        return $this;
    }

}