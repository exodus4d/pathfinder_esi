<?php
/**
 * Created by PhpStorm.
 * User: Exodus 4D
 * Date: 26.12.2018
 * Time: 20:27
 */

namespace Exodus4D\ESI\Client\Ccp\Esi;

interface EsiInterface {

    /**
     * @param int $typeId
     * @return array
     */
    public function getUniverseTypesData(int $typeId) : array;

    /**
     * @param int $attributeId
     * @return array
     */
    public function getDogmaAttributeData(int $attributeId) : array;

    /**
     * @return array
     */
    public function getFactionWarSystems() : array;

    /**
     * @param int $destinationId
     * @param string $accessToken
     * @param array $options
     * @return array
     */
    public function setWaypoint(int $destinationId, string $accessToken, array $options = []) : array;

    /**
     * @param int $targetId
     * @param string $accessToken
     * @return array
     */
    public function openWindow(int $targetId, string $accessToken) : array;

    /**
     * @return array
     */
    public function getSovereigntyMap() : array;

    /**
     * @param array $categories
     * @param string $search
     * @param bool $strict
     * @return array
     */
    public function search(array $categories, string $search, bool $strict = false) : array;

    /**
     * @param string $version
     * @return array
     */
    public function getStatus(string $version) : array;

    /**
     * @param string $version
     * @return array
     */
    public function getStatusForRoutes(string $version) : array;

    /**
     * @param int $corporationId
     * @return bool
     */
    public function isNpcCorporation(int $corporationId) : bool;
}