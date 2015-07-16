<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\ITypeInterface;

/**
 * Class Memcached
 *
 * @package SPHERE\System\Cache\Type
 */
class Memcached implements ITypeInterface
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
    public static function clearCache()
    {

        if (null !== self::$Server) {
            self::$Server->flush();
        }
    }

    /**
     * @return \Memcached
     */
    public static function getServer()
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
            return $this->Status[self::getConnection()]['get_hits'];
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
    public static function getConnection()
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
            return $this->Status[self::getConnection()]['get_misses'];
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
            return $this->Status[self::getConnection()]['limit_maxbytes'];
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
            return $this->Status[self::getConnection()]['bytes'];
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

        self::$Host = $Configuration['Host'];
        self::$Port = $Configuration['Port'];

        if (self::$Host && self::$Port) {
            if (class_exists( '\Memcached', false ) && null === self::$Server) {
                self::$Server = new \Memcached();
                self::$Server->addServer( self::$Host, self::$Port );
                self::$Server->setOption( \Memcached::OPT_TCP_NODELAY, true );
            }
        }
    }
}
