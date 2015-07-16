<?php
namespace SPHERE\System\Cache\Type;

use SPHERE\System\Cache\ITypeInterface;

/**
 * Class ApcSma
 *
 * @package SPHERE\System\Cache\Type
 */
class ApcSma implements ITypeInterface
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

        return -1;
    }

    /**
     * @return integer
     */
    public function getMissCount()
    {

        return -1;
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

        if (isset( $this->Status['seg_size'] )) {
            return $this->Status['seg_size'];
        } else {
            return -1;
        }
    }

    /**
     * @return integer
     */
    public function getFreeSize()
    {

        if (isset( $this->Status['avail_mem'] )) {
            return $this->Status['avail_mem'];
        } else {
            return -1;
        }
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

        if (function_exists( 'apc_sma_info' )) {
            $this->Status = apc_sma_info( true );
        }
    }
}
