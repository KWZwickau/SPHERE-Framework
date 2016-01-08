<?php
namespace SPHERE\System\Debugger;

use SPHERE\System\Config\ConfigInterface;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\LoggerInterface;

/**
 * Class DebuggerFactory
 *
 * @package SPHERE\System\Debugger
 */
class DebuggerFactory
{

    /**
     * @var LoggerInterface
     */
    private static $InstanceCache = array();

    /**
     * @param LoggerInterface $Logger
     * @param ConfigInterface $Config
     * @param string $Name
     *
     * @return LoggerInterface
     */
    public function createLogger(LoggerInterface $Logger = null, ConfigInterface $Config = null, $Name = 'Debugger')
    {

        if (null === $Logger) {
            $Logger = new BenchmarkLogger();
        }
        if (!$this->isAvailable($Logger)) {
            $this->setLogger($Logger, $Name, $Config);
        }
        return $this->getLogger($Logger);
    }

    /**
     * @param LoggerInterface $Logger
     *
     * @return bool
     */
    private function isAvailable($Logger)
    {

        return isset( self::$InstanceCache[$this->getHash($Logger)] );
    }

    /**
     * @param string $Logger
     *
     * @return string
     */
    private function getHash($Logger)
    {

        return get_class($Logger);
    }

    /**
     * @param LoggerInterface $Logger
     * @param $Name
     * @param ConfigInterface $Config
     */
    private function setLogger(LoggerInterface $Logger, $Name, ConfigInterface $Config = null)
    {

        self::$InstanceCache[$this->getHash($Logger)] = $Logger->setConfig($Name, $Config);
    }

    /**
     * @param LoggerInterface $Logger
     *
     * @return LoggerInterface
     */
    private function getLogger(LoggerInterface $Logger)
    {

        return self::$InstanceCache[$this->getHash($Logger)];
    }
}
