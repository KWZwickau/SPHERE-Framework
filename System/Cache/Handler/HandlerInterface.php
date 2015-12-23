<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheInterface;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;

/**
 * Interface HandlerInterface
 *
 * @package SPHERE\System\Cache\Handler
 */
interface HandlerInterface extends CacheInterface
{

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return HandlerInterface
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default');

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return null|mixed
     */
    public function getValue($Key, $Region = 'Default');

    /**
     * @param string          $Name
     * @param ReaderInterface $Config
     *
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null);

    /**
     * @return HandlerInterface
     */
    public function clearCache();

    /**
     * @return CacheStatus
     */
    public function getStatus();
}
