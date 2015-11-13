<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\ITypeInterface;

/**
 * Class OpCache
 *
 * @package SPHERE\System\Cache\Type
 */
class OpCache implements ITypeInterface
{

    /** @var array $Status */
    private $Status = array();
    /** @var array $Config */
    private $Config = array();

    /**
     * @param bool $doPrune
     */
    public function clearCache($doPrune = false)
    {

        if (function_exists('opcache_reset')) {
            opcache_reset();
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

        if (function_exists('opcache_reset')) {
            return true;
        }
        return false;
    }

    /**
     * @return integer
     */
    public function getHitCount()
    {

        if (!empty( $this->Status )) {
            return $this->Status['opcache_statistics']['hits'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getMissCount()
    {

        if (!empty( $this->Status )) {
            return $this->Status['opcache_statistics']['misses'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getUsedSize()
    {

        if (!empty( $this->Status )) {
            return $this->Status['memory_usage']['used_memory'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getAvailableSize()
    {

        if (!empty( $this->Status )) {
            return $this->Config['directives']['opcache.memory_consumption'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getFreeSize()
    {

        if (!empty( $this->Status )) {
            return $this->Status['memory_usage']['free_memory'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getWastedSize()
    {

        if (!empty( $this->Status )) {
            return $this->Status['memory_usage']['wasted_memory'];
        }
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

        if (function_exists('opcache_get_status')) {
            $this->Status = opcache_get_status();
        }
        if (function_exists('opcache_get_configuration')) {
            $this->Config = opcache_get_configuration();
        }
    }
}
