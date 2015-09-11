<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Memcached
 *
 * @package SPHERE\System\Cache\Type
 */
class Memcached implements IApiInterface
{

    /** @var \Memcached $Status */
    private static $Server = null;
    /** @var string $Host */
    private static $Host = '';
    /** @var string $Port */
    private static $Port = '11211';
    /** @var bool $Available */
    private static $Available = false;
    /** @var array $Status */
    private $Status = null;
    /** @var string|float $Timing */
    private $Timing = '';

    /**
     * @return void
     */
    public function clearCache()
    {

        if (null !== self::$Server) {
            self::$Server->flush();
        }
    }

    /**
     * @return bool
     */
    public function needConfiguration()
    {

        if (null !== self::$Server) {
            return false;
        }
        return true;
    }

    /**
     * @return \Memcached
     */
    public function getServer()
    {

        return self::$Server;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return 'Memcached';
    }

    /**
     * @return integer
     */
    public function getHitCount()
    {

        $this->fetchStatus();
        if (!empty( $this->Status )) {
            return $this->Status[$this->getConnection()]['get_hits'];
        }
        return -1;
    }

    private function fetchStatus()
    {

        if ($this->isAvailable() && null !== self::$Server && empty( $this->Status )) {
            $this->Status = self::$Server->getStats();
        }
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {

        if (self::$Available || class_exists('\Memcached', false)) {
            self::$Available = true;
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getConnection()
    {

        return self::$Host.':'.self::$Port;
    }

    /**
     * @return integer
     */
    public function getMissCount()
    {

        $this->fetchStatus();
        if (!empty( $this->Status )) {
            return $this->Status[$this->getConnection()]['get_misses'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getFreeSize()
    {

        return $this->getAvailableSize() - $this->getUsedSize();
    }

    /**
     * @return integer
     */
    public function getAvailableSize()
    {

        $this->fetchStatus();
        if (!empty( $this->Status )) {
            return $this->Status[$this->getConnection()]['limit_maxbytes'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getUsedSize()
    {

        $this->fetchStatus();
        if (!empty( $this->Status )) {
            return $this->Status[$this->getConnection()]['bytes'];
        }
        return -1;
    }

    /**
     * @return integer
     */
    public function getWastedSize()
    {

        return 0;
    }

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        self::$Host = (string)$Configuration['Host'];
        self::$Port = (integer)$Configuration['Port'];

        if (self::$Host && self::$Port) {
            if ($this->isAvailable() && null === self::$Server) {
                self::$Server = new \Memcached();
                self::$Server->addServer(self::$Host, (integer)self::$Port);
                self::$Server->setOption(\Memcached::OPT_TCP_NODELAY, true);
            }
        }
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

        $this->Timing = Debugger::getTimeGap();

        if (null !== self::$Server) {
            self::$Server->set($Key, $Value, ( !$Timeout ? null : time() + $Timeout ));
            $this->Timing = number_format(( Debugger::getTimeGap() - $this->Timing ) * 1000, 3, ',', '');
            return true;
        }
        $this->Timing = number_format(( Debugger::getTimeGap() - $this->Timing ) * 1000, 3, ',', '');
        return false;
    }

    /**
     * @return string
     */
    public function getLastTiming()
    {

        return $this->Timing;
    }

    /**
     * @param string $Key
     *
     * @return mixed|false
     */
    public function getValue($Key)
    {

        $this->Timing = Debugger::getTimeGap();

        if (null !== self::$Server) {
            $Value = self::$Server->get($Key);
            // 0 = MEMCACHED_SUCCESS
            if (self::$Server->getResultCode() == 0) {
                $this->Timing = number_format(( Debugger::getTimeGap() - $this->Timing ) * 1000, 3, ',', '');
                return $Value;
            }
        }
        $this->Timing = number_format(( Debugger::getTimeGap() - $this->Timing ) * 1000, 3, ',', '');
        return false;
    }
}
