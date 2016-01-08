<?php
namespace SPHERE\System\Cache;

use SPHERE\System\Cache\Handler\DefaultHandler;
use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Config\ConfigInterface;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class CacheFactory
 *
 * @package SPHERE\System\Cache
 */
class CacheFactory extends Extension
{

    /**
     * @var HandlerInterface
     */
    private static $InstanceCache = array();

    /**
     * @param HandlerInterface $Handler
     * @param ConfigInterface  $Config
     * @param string           $Name
     *
     * @return HandlerInterface
     */
    public function createHandler(HandlerInterface $Handler = null, ConfigInterface $Config = null, $Name = 'Memcached')
    {

        if (!$this->isAvailable($Handler)) {
            if (null === $Handler) {
                $Handler = new DefaultHandler();
            }
            $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__ . ': ' . get_class($Handler));
            $this->setHandler($Handler, $Name, $Config);
        }
        return $this->getHandler($Handler);
    }

    /**
     * @param HandlerInterface $Handler
     *
     * @return bool
     */
    private function isAvailable($Handler)
    {

        return isset( self::$InstanceCache[$this->getHash($Handler)] );
    }

    /**
     * @param string $Handler
     *
     * @return string
     */
    private function getHash($Handler)
    {

        return get_class($Handler);
    }

    /**
     * @param HandlerInterface $Handler
     * @param string           $Name
     * @param ConfigInterface  $Config
     */
    private function setHandler(HandlerInterface $Handler, $Name, ConfigInterface $Config = null)
    {

        self::$InstanceCache[$this->getHash($Handler)] = $Handler->setConfig($Name, $Config);
    }

    /**
     * @param HandlerInterface $Handler
     *
     * @return HandlerInterface
     */
    private function getHandler(HandlerInterface $Handler)
    {

        return self::$InstanceCache[$this->getHash($Handler)];
    }
}
