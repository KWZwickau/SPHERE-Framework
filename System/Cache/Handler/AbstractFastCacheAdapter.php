<?php
namespace SPHERE\System\Cache\Handler;

use phpFastCache\CacheManager;
use phpFastCache\Core\DriverAbstract;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;

/**
 * Class AbstractFastCacheHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
abstract class AbstractFastCacheAdapter extends AbstractHandler
{

    const CACHE_TYPE_COOKIE = 'cookie';

    /** @var null|DriverAbstract $FastCacheInstance */
    private $FastCacheInstance = null;

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return HandlerInterface
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {

        if ($this->FastCacheInstance) {
            $this->FastCacheInstance->set($Key, serialize($Value), $Timeout);
        }
        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return null|mixed
     */
    public function getValue($Key, $Region = 'Default')
    {

        if ($this->FastCacheInstance) {
            return unserialize($this->FastCacheInstance->get($Key));
        }
        return null;
    }

    /**
     * @param string          $Name
     * @param ReaderInterface $Config
     *
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        return $this;
    }

    /**
     * @return HandlerInterface
     */
    public function clearCache()
    {

        if ($this->FastCacheInstance) {
            $this->FastCacheInstance->clean();
        }
        return $this;
    }

    /**
     * @return CacheStatus
     */
    abstract public function getStatus();

    /**
     * @param string $CACHE_TYPE
     */
    protected function setCacheType($CACHE_TYPE)
    {

        $this->FastCacheInstance = CacheManager::getInstance($CACHE_TYPE);
    }

    /**
     * @return mixed|null
     */
    protected function getCacheStatus()
    {

        if ($this->FastCacheInstance) {
            return $this->FastCacheInstance->stats();
        }
        return null;
    }
}
