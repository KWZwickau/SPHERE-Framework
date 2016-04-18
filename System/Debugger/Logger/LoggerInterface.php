<?php
namespace SPHERE\System\Debugger\Logger;

use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerInterface;

/**
 * Interface LoggerInterface
 *
 * @package SPHERE\System\Debugger\Logger
 */
interface LoggerInterface extends DebuggerInterface
{
    /**
     * @param string $Name
     * @param ReaderInterface $Config
     *
     * @return LoggerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null);

    /**
     * @param string $Content
     *
     * @return LoggerInterface
     */
    public function addLog($Content);

    /**
     * @return LoggerInterface
     */
    public function clearLog();

    /**
     * @return array
     */
    public function getLog();

    /**
     * @return LoggerInterface
     */
    public function enableLog();

    /**
     * @return LoggerInterface
     */
    public function disableLog();

    /**
     * @return bool
     */
    public function isEnabled();
}
