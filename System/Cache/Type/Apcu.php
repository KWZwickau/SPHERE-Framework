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

    /** @var bool $Available */
    private static $Available = false;
    /** @var array $Status */
    private $Status = array();

    /**
     * @param bool $doPrune
     */
    public function clearCache($doPrune = false)
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

        if ($this->isAvailable() && empty( $this->Status )) {
            if (function_exists('apc_sma_info')) {
                $this->Status += apc_sma_info(true);
            }
            if (function_exists('apc_cache_info')) {
                $this->Status += apc_cache_info('user', true);
            }
        }
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {

        if (self::$Available || function_exists('apc_clear_cache')) {
            self::$Available = true;
            return true;
        }
        return false;
    }
}
