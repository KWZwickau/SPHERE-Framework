<?php
namespace SPHERE\System\Debugger\Logger;

/**
 * Class AbstractLogger
 * @package SPHERE\System\Debugger\Logger
 */
abstract class AbstractLogger implements LoggerInterface
{
    /** @var array $LogCache */
    private $LogCache = array();
    /** @var bool $LogEnabled */
    private $LogEnabled = true;

    /**
     * @param string $Content
     * @return LoggerInterface
     */
    public function addLog($Content)
    {
        if ($this->LogEnabled) {
            array_push($this->LogCache, $Content);
        }
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function clearLog()
    {
        $this->LogCache = array();
        return $this;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->LogCache;
    }

    /**
     * @return LoggerInterface
     */
    public function enableLog()
    {
        $this->LogEnabled = true;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function disableLog()
    {
        $this->LogEnabled = false;
        return $this;
    }
}
