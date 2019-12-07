<?php


namespace Exodus4D\ESI\Config;

class AbstractConfig implements ConfigInterface {

    /**
     * @var array
     */
    protected static $spec = [];

    /**
     * removes version from $endpoint
     * -> return found version
     * @param string $endpoint
     * @return string|null
     */
    protected function stripVersion(string &$endpoint) : ?string {
        $version = null;
        $endpoint = preg_replace_callback(
            '/^\/(v\d{1})\//',
            function($matches) use (&$version){
                // set found version and strip it from $endpoint
                $version = $matches[1];
                return '/';
            },
            $endpoint,
            1
        );

        return $version;
    }

    /**
     * get endpoint data for all configured ESI endpoints
     * @return array
     */
    public function getEndpointsData() : array {
        $endpointsData = [];
        $conf = static::$spec;

        array_walk_recursive($conf, function($value, $key) use (&$endpointsData){
            if(is_string($value) && !empty($value)){
                // get version from route and remove it
                $version = $this->stripVersion($value);
                $endpointsData[] = [
                    'method'    => strtolower($key),
                    'route'     => $value,
                    'version'   => $version,
                    'status'    => null
                ];
            }
        });

        return $endpointsData;
    }

    /**
     * @param array $path
     * @param array $placeholders
     * @return string
     */
    public function getEndpoint(array $path, array $placeholders = []) : string {
        $endpoint = '';
        $spec = static::$spec;

        foreach($path as $key){
            if(is_array($spec) && array_key_exists($key, $spec)){
                $spec = $spec[$key];
            }else{
                throw new \InvalidArgumentException(sprintf(self::ERROR_UNKNOWN_ENDPOINT, implode('→', $path)));
            }
        }

        if(is_string($spec)){
            // replace vars
            $pattern = '/\{x\}/';
            foreach($placeholders as $placeholder){
                $spec = preg_replace($pattern, $placeholder, $spec, 1);
            }
            $endpoint =  trim($spec);
        }

        return $endpoint;
    }
}