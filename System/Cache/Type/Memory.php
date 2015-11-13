<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Memory
 *
 * @package SPHERE\System\Cache\Type
 */
class Memory implements IApiInterface
{

    private static $Memory = array();

    private static $HitCount = 1;
    private static $MissCount = 0;

    private $Partition = 'Default:';
    /** @var string|float $Timing */
    private $Timing = '';

    /**
     * @param string $Partition
     */
    public function __construct($Partition = 'Default')
    {

        $this->Partition = $Partition.':';
    }

    /**
     * @param string   $Key
     * @param mixed    $Value
     * @param null|int $Timeout
     *
     * @return bool
     */
    public function setValue($Key, $Value, $Timeout = null)
    {

        self::$Memory[$this->Partition.$Key] = $Value;
        return true;
    }

    /**
     * @param string $Key
     *
     * @return mixed|false
     */
    public function getValue($Key)
    {

        $this->Timing = Debugger::getTimeGap();

        if (array_key_exists($this->Partition.$Key, self::$Memory)) {
            $Value = self::$Memory[$this->Partition.$Key];
            self::$HitCount++;
            $this->Timing = number_format(( Debugger::getTimeGap() - $this->Timing ) * 1000, 3, ',', '');
            return $Value;
        }
        self::$MissCount++;
        $this->Timing = number_format(( Debugger::getTimeGap() - $this->Timing ) * 1000, 3, ',', '');
        return false;
    }

    /**
     * @param bool $doPrune
     */
    public function clearCache($doPrune = false)
    {

        self::$Memory = array();
    }

    /**
     * @return bool
     */
    public function needConfiguration()
    {

        return false;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {

        return true;
    }

    /**
     * @return integer
     */
    public function getHitCount()
    {

        return self::$HitCount;
    }

    /**
     * @return integer
     */
    public function getMissCount()
    {

        return self::$MissCount;
    }

    /**
     * @return float
     */
    public function getFreeSize()
    {

        return $this->getAvailableSize() - $this->getUsedSize();
    }

    /**
     * @return float
     */
    public function getAvailableSize()
    {

        return $this->convertByte2Integer(ini_get('memory_limit'));
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

    /**
     * @return float
     */
    public function getUsedSize()
    {

        return memory_get_peak_usage(true);
    }

    /**
     * @return float
     */
    public function getWastedSize()
    {

        return 0;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return '';
    }

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

    }

    /**
     * @return string
     */
    public function getLastTiming()
    {

        return '-NA-';
    }


}
