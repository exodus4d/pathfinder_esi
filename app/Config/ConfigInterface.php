<?php


namespace Exodus4D\ESI\Config;

interface ConfigInterface {

    /**
     * error message for invalid/unknown endpoint
     */
    const ERROR_UNKNOWN_ENDPOINT = 'Invalid endpoint path "%s"';

    /**
     * @return array
     */
    public function getEndpointsData() : array;

    /**
     * @param array $path
     * @param array $placeholders
     * @return string
     */
    public function getEndpoint(array $path, array $placeholders = []) : string;
}