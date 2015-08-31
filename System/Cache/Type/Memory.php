<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\IApiInterface;

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

    /**
     * @param string   $Key
     * @param mixed    $Value
     * @param null|int $Timeout
     *
     * @return bool
     */
    public function setValue($Key, $Value, $Timeout = null)
    {

        self::$Memory[$Key] = $Value;
        return true;
    }

    /**
     * @param string $Key
     *
     * @return mixed|false
     */
    public function getValue($Key)
    {

        if (array_key_exists($Key, self::$Memory)) {
            self::$HitCount++;
            return self::$Memory[$Key];
        }
        self::$MissCount++;
        return false;
    }

    /**
     * @return void
     */
    public function clearCache()
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
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'E':
                $Value = $Value * 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'P':
                $Value = $Value * 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'T':
                $Value = $Value * 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'G':
                $Value = $Value * 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'M':
                $Value = $Value * 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'K':
                $Value = $Value * 1024;
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
}
