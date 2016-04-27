<?php
namespace SPHERE\System\Debugger\Logger;

use SPHERE\System\Config\Reader\ReaderInterface;

/**
 * Class AbstractLogger
 *
 * @package SPHERE\System\Debugger\Logger
 */
abstract class AbstractLogger implements LoggerInterface
{

    /** @var array $LogCache */
    private $LogCache = array();
    /** @var bool $LogEnabled */
    private $LogEnabled = false;

    /**
     * @param string $Content
     *
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
     * @param string          $Name
     * @param ReaderInterface $Config
     *
     * @return LoggerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        if ($Config) {
            $Value = $Config->getValue($Name);
            if ($Value && $Value->getContainer('Enabled')->getValue()) {
                $this->enableLog();
            } else {
                $this->disableLog();
            }
        }
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function enableLog()
    {

        ini_set('display_errors', 1);
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

    /**
     * @return bool
     */
    public function isEnabled()
    {

        return $this->LogEnabled;
    }
}
