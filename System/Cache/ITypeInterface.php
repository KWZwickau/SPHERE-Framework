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
     * @param bool $doPrune
     *
     * @return
     */
    public function clearCache($doPrune = false);

    /**
     * @return bool
     */
    public function needConfiguration();

    /**
     * @return bool
     */
    public function isAvailable();

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
    public function setConfiguration($Configuration);
}
