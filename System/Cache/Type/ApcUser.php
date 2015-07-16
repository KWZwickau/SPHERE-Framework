<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\ITypeInterface;

/**
 * Class ApcUser
 *
 * @package SPHERE\System\Cache\Type
 */
class ApcUser implements ITypeInterface
{

    /** @var array $Status */
    private $Status = array();

    /**
     * @return void
     */
    public static function clearCache()
    {

        if (function_exists( 'apc_clear_cache' )) {
            apc_clear_cache();
        }
    }

    /**
     * @return integer
     */
    public function getHitCount()
    {

        if (isset( $this->Status['nhits'] )) {
            return $this->Status['nhits'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getMissCount()
    {

        if (isset( $this->Status['nmisses'] )) {
            return $this->Status['nmisses'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getUsedSize()
    {

        return $this->getSizeAvailable() - $this->getSizeFree();
    }

    /**
     * @return integer
     */
    public function getAvailableSize()
    {

        if (isset( $this->Status['mem_size'] )) {
            return $this->Status['mem_size'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getFreeSize()
    {

        return -1;
    }

    /**
     * @return integer
     */
    public function getWastedSize()
    {

        return -1;
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
    public function setConfiguration( $Configuration )
    {

        if (function_exists( 'apc_cache_info' )) {
            $this->Status = apc_cache_info( 'user', true );
        }
    }
}
