<?php
namespace SPHERE\System\Debugger;

use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\LoggerInterface;

/**
 * Class DebuggerFactory
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
     * @return LoggerInterface
     */
    public function createLogger(LoggerInterface $Logger = null)
    {
        if (null === $Logger) {
            $Logger = new BenchmarkLogger();
        }
        if (!$this->isAvailable($Logger)) {
            $this->setLogger($Logger);
        }
        return $this->getLogger($Logger);
    }

    /**
     * @param LoggerInterface $Logger
     * @return bool
     */
    private function isAvailable($Logger)
    {
        return isset(self::$InstanceCache[$this->getHash($Logger)]);
    }

    /**
     * @param string $Logger
     * @return string
     */
    private function getHash($Logger)
    {
        return sha1(get_class($Logger));
    }

    /**
     * @param LoggerInterface $Logger
     */
    private function setLogger(LoggerInterface $Logger)
    {
        self::$InstanceCache[$this->getHash($Logger)] = $Logger;
    }

    /**
     * @param LoggerInterface $Logger
     * @return LoggerInterface
     */
    private function getLogger(LoggerInterface $Logger)
    {
        return self::$InstanceCache[$this->getHash($Logger)];
    }
}
