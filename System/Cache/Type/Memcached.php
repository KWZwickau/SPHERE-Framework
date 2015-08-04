<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\IApiInterface;

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
    /** @var array $Status */
    private $Status = null;

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
     * @return bool
     */
    public function isAvailable()
    {

        if (class_exists( '\Memcached', false )) {
            return true;
        }
        return false;
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

        if (null !== self::$Server && empty( $this->Status )) {
            $this->Status = self::$Server->getStats();
        }
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
    public function setConfiguration( $Configuration )
    {

        self::$Host = (string)$Configuration['Host'];
        self::$Port = (integer)$Configuration['Port'];

        if (self::$Host && self::$Port) {
            if (class_exists( '\Memcached', false ) && null === self::$Server) {
                self::$Server = new \Memcached();
                self::$Server->addServer( self::$Host, (integer)self::$Port );
                self::$Server->setOption( \Memcached::OPT_TCP_NODELAY, true );
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
    public function setValue( $Key, $Value, $Timeout = null )
    {

        if (null !== self::$Server) {
            self::$Server->set( $Key, $Value, ( !$Timeout ? null : time() + $Timeout ) );
            return true;
        }
        return false;
    }

    /**
     * @param string $Key
     *
     * @return mixed|false
     */
    public function getValue( $Key )
    {

        if (null !== self::$Server) {
            $Value = self::$Server->get( $Key );
            // 0 = MEMCACHED_SUCCESS
            if (self::$Server->getResultCode() == 0) {
                return $Value;
            }
        }
        return false;
    }
}
