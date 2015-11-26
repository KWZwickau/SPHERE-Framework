<?php
namespace SPHERE\System\Debugger\Logger;

use SPHERE\System\Debugger\DebuggerInterface;

/**
 * Interface LoggerInterface
 * @package SPHERE\System\Debugger\Logger
 */
interface LoggerInterface extends DebuggerInterface
{
    /**
     * @param string $Content
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
}
