<?php
namespace SPHERE\System\Debugger;

use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\ConfigInterface;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\LoggerInterface;

/**
 * Class DebuggerFactory
 *
 * @package SPHERE\System\Debugger
 */
class DebuggerFactory
{

    /** @var LoggerInterface[] $Instance */
    private static $Instance = array();
    /** @var null|\SPHERE\System\Config\Reader\ReaderInterface $Configuration */
    private static $Configuration = null;

    /**
     * DebuggerFactory constructor.
     */
    public function __construct()
    {
        if (null === self::$Configuration) {
            self::$Configuration = (new ConfigFactory())->createReader(__DIR__ . '/Configuration.ini', new IniReader());
        }
    }

    /**
     * @param LoggerInterface $Logger
     * @return LoggerInterface
     */
    public function createLogger(LoggerInterface $Logger = null)
    {

        if (!$this->isAvailable($Logger)) {
            if (null === $Logger) {
                $Logger = new BenchmarkLogger();
            }
            $Setting = (new \ReflectionClass($Logger))->getShortName();
            $this->setLogger($Logger, $Setting, self::$Configuration);
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

        return isset(self::$Instance[$this->getHash($Logger)]);
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
     * @param string $Name
     * @param ConfigInterface $Config
     */
    private function setLogger(LoggerInterface $Logger, $Name, ConfigInterface $Config = null)
    {

        self::$Instance[$this->getHash($Logger)] = $Logger->setConfig($Name, $Config);
    }

    /**
     * @param LoggerInterface $Logger
     *
     * @return LoggerInterface
     */
    private function getLogger(LoggerInterface $Logger)
    {

        return self::$Instance[$this->getHash($Logger)];
    }
}
