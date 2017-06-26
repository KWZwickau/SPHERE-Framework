<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;

/**
 * Class MemoryHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class MemoryHandler extends AbstractHandler implements HandlerInterface
{

    private static $HitCount = 0;
    private static $MissCount = 0;
    /** @var array $MemoryRegister */
    private $MemoryRegister = array();

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return MemoryHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {

        $this->MemoryRegister[$Region][$Key] = new MemoryContainer($Value, $Timeout);
        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return null|mixed
     */
    public function getValue($Key, $Region = 'Default')
    {

        $Container = $this->getContainer($Region, $Key);
        if (false !== $Container) {
            self::$HitCount++;
            return $Container->getValue();
//        } else {
//            (new DebuggerFactory())->createLogger(new ErrorLogger())
//                ->addLog(__METHOD__.' Error: '
//                    .$Region.'->'.$Key
//                );
        }
        self::$MissCount++;
        return null;
    }

    /**
     * @param string $Region
     * @param string $Key
     *
     * @return bool|MemoryContainer
     */
    private function getContainer($Region, $Key)
    {

        if (isset( $this->MemoryRegister[$Region] )) {
            if (isset( $this->MemoryRegister[$Region][$Key] )) {
                /** @var MemoryContainer $Container */
                $Container = $this->MemoryRegister[$Region][$Key];
                if ($Container->isValid()) {
                    return $Container;
                } else {
                    unset( $this->MemoryRegister[$Region][$Key] );
                }
            }
        }
        return false;
    }

    /**
     * @param ReaderInterface $Name
     * @param ReaderInterface $Config
     *
     * @return MemoryHandler
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        // Nothing to configure
        return $this;
    }

    /**
     * @param null|string $Region
     *
     * @return MemoryHandler
     */
    public function clearCache($Region = null)
    {

        if (null === $Region) {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())
                ->addLog('Clear Memory (Full-Flush)');
            $this->MemoryRegister = array();
        } else {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())
                ->addLog('Clear Memory (Slot-Flush: '.$Region.')');
            $this->MemoryRegister[$Region] = array();
        }
        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Status Memory');
        return new CacheStatus(
            self::$HitCount, self::$MissCount, $this->convertByte2Integer(ini_get('memory_limit')),
            memory_get_peak_usage(true),
            $this->convertByte2Integer(ini_get('memory_limit')) - memory_get_peak_usage(true), 0
        );
    }

    /**
     * @param $Byte
     *
     * @return int
     */
    private function convertByte2Integer($Byte)
    {

        preg_match('/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $Byte, $Match);
        $Value = (float)$Match[1];
        switch (strtoupper($Match[2])) {
            case 'E':
                $Value = $Value * 1024 * 1024 * 1024 * 1024 * 1024 * 1024;
                break;
            case 'P':
                $Value = $Value * 1024 * 1024 * 1024 * 1024 * 1024;
                break;
            case 'T':
                $Value = $Value * 1024 * 1024 * 1024 * 1024;
                break;
            case 'G':
                $Value = $Value * 1024 * 1024 * 1024;
                break;
            case 'M':
                $Value = $Value * 1024 * 1024;
                break;
            case 'K':
                $Value = $Value * 1024;
                break;
        }
        return (integer)$Value;
    }
}
