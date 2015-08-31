<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\ITypeInterface;

/**
 * Class Apcu
 *
 * @package SPHERE\System\Cache\Type
 */
class Apcu implements ITypeInterface
{

    /** @var array $Status */
    private $Status = array();

    /**
     * @return void
     */
    public function clearCache()
    {

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
    }

    /**
     * @return bool
     */
    public function needConfiguration()
    {

        return true;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {

        if (function_exists('apc_clear_cache')) {
            return true;
        }
        return false;
    }

    /**
     * @return integer
     */
    public function getHitCount()
    {

        if (isset( $this->Status['nhits'] )) {
            return $this->Status['nhits'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getMissCount()
    {

        if (isset( $this->Status['nmisses'] )) {
            return $this->Status['nmisses'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getUsedSize()
    {

        return $this->getAvailableSize() - $this->getFreeSize();
    }

    /**
     * @return integer
     */
    public function getAvailableSize()
    {

        if (isset( $this->Status['seg_size'] )) {
            return $this->Status['seg_size'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getFreeSize()
    {

        if (isset( $this->Status['avail_mem'] )) {
            return $this->Status['avail_mem'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getWastedSize()
    {

        return -1;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return '';
    }

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        if (function_exists('apc_sma_info')) {
            $this->Status += apc_sma_info(true);
        }
        if (function_exists('apc_cache_info')) {
            $this->Status += apc_cache_info('user', true);
        }
    }
}
