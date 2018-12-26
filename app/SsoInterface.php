<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 16:23
 */

namespace Exodus4D\ESI;


interface SsoInterface {

    public function getAccessData(string $authHeader, array $urlParams = []) : array;

}