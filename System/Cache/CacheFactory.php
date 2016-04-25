<?php
namespace SPHERE\System\Cache;

use SPHERE\System\Cache\Handler\DefaultHandler;
use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\ConfigInterface;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class CacheFactory
 *
 * @package SPHERE\System\Cache
 */
class CacheFactory extends Extension
{

    /** @var HandlerInterface $InstanceCache */
    private static $InstanceCache = array();
    /** @var null|\SPHERE\System\Config\Reader\ReaderInterface $Configuration */
    private static $Configuration = null;

    /**
     * CacheFactory constructor.
     */
    public function __construct()
    {
        if (null === self::$Configuration) {
            self::$Configuration = (new ConfigFactory())->createReader(__DIR__ . '/Configuration.ini', new IniReader());
        }
    }

    /**
     * @param HandlerInterface $Handler
     * @return HandlerInterface
     */
    public function createHandler(HandlerInterface $Handler = null)
    {

        if (!$this->isAvailable($Handler)) {
            if (null === $Handler) {
                $Handler = new DefaultHandler();
            }
            $Setting = (new \ReflectionClass($Handler))->getShortName();
            $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__ . ': ' . get_class($Handler));
            $this->setHandler($Handler, $Setting, self::$Configuration);
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

        return isset(self::$InstanceCache[$this->getHash($Handler)]);
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
     * @param string $Name
     * @param ConfigInterface $Config
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
