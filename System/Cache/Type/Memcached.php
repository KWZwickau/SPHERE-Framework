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
    private $Server = null;
    /** @var string $Host */
    private $Host = '';
    /** @var string $Port */
    private $Port = '11211';
    /** @var array $Status */
    private $Status = null;

    /**
     * @return void
     */
    public function clearCache()
    {

        if (null !== $this->Server) {
            $this->Server->flush();
        }
    }

    /**
     * @return \Memcached
     */
    public function getServer()
    {

        return $this->Server;
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

        if (null !== $this->Server && empty( $this->Status )) {
            $this->Status = $this->Server->getStats();
        }
    }

    /**
     * @return string
     */
    public function getConnection()
    {

        return $this->Host.':'.$this->Port;
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

        $this->Host = (string)$Configuration['Host'];
        $this->Port = (integer)$Configuration['Port'];

        if ($this->Host && $this->Port) {
            if (class_exists( '\Memcached', false ) && null === $this->Server) {
                $this->Server = new \Memcached();
                $this->Server->addServer( $this->Host, $this->Port );
                $this->Server->setOption( \Memcached::OPT_TCP_NODELAY, true );
            }
        }
    }
}
