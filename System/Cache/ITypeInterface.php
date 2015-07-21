<?php
namespace SPHERE\System\Cache;

/**
 * Interface ITypeInterface
 *
 * @package SPHERE\System\Cache
 */
interface ITypeInterface
{

    /**
     * @return void
     */
    public function clearCache();

    /**
     * @return integer
     */
    public function getHitCount();

    /**
     * @return integer
     */
    public function getMissCount();

    /**
     * @return float
     */
    public function getAvailableSize();

    /**
     * @return float
     */
    public function getUsedSize();

    /**
     * @return float
     */
    public function getFreeSize();

    /**
     * @return float
     */
    public function getWastedSize();

    /**
     * @return string
     */
    public function getConfiguration();

    /**
     * @param array $Configuration
     */
    public function setConfiguration( $Configuration );
}
