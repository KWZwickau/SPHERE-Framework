<?php
namespace SPHERE\System\Debugger\Logger;

/**
 * Class BenchmarkLogger
 * @package SPHERE\System\Debugger\Logger
 */
class BenchmarkLogger extends AbstractLogger implements LoggerInterface
{
    /** @var float $TimerStart */
    private $TimerStart = 0.0;
    /** @var float $TimerStop */
    private $TimerStop = 0.0;
    /** @var float $TimerGap */
    private $TimerGap = 0.0;
    /** @var bool $TimerRunning */
    private $TimerRunning = false;

    /**
     * BenchmarkLogger constructor.
     */
    public function __construct()
    {
        $this->startTimer();
    }

    /**
     * @return BenchmarkLogger
     */
    public function startTimer()
    {
        $this->addLog('- Start Timer -');
        if (!$this->TimerRunning) {
            $this->TimerStart = microtime(true);
        }
        $this->TimerRunning = true;
        return $this;
    }

    /**
     * @param string $Content
     * @return BenchmarkLogger
     */
    public function addLog($Content)
    {
        return parent::addLog($this->getTimer() . ' (' . $this->getGap() . ') ' . $Content);
    }

    /**
     * @return string
     */
    private function getTimer()
    {
        $this->TimerGap = microtime(true);
        if ($this->TimerRunning) {
            return number_format(microtime(true) - $this->TimerStart, 8);
        } else {
            return number_format($this->TimerStop - $this->TimerStart, 8);
        }
    }

    /**
     * @return string
     */
    private function getGap()
    {
        return number_format(microtime(true) - $this->TimerGap, 8);
    }

    /**
     * @return BenchmarkLogger
     */
    public function stopTimer()
    {
        $this->addLog('- Stop Timer -');
        if ($this->TimerRunning) {
            $this->TimerStop = microtime(true);
        }
        $this->TimerRunning = false;
        return $this;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        $this->addLog('- Get Log -');
        $Log = parent::getLog();
//        krsort($Log);
        return $Log;
    }
}
