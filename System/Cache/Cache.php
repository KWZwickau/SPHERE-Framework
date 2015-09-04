<?php
namespace SPHERE\System\Cache;

use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;

/**
 * Class Cache
 *
 * @package SPHERE\System\Cache
 */
class Cache
{

    /** @var ITypeInterface $Type */
    private $Type = null;

    /**
     * @param ITypeInterface $Type
     * @param bool           $ForceType
     */
    function __construct(ITypeInterface $Type = null, $ForceType = false)
    {

        if ($Type === null) {
            $Type = new Memcached();
        }
        if (!$ForceType) {
            if (!$Type->isAvailable()) {
                $Type = new Memory();
            }
        }

        $this->Type = $Type;
        if ($this->Type->needConfiguration()) {
            if ($this->Type->getConfiguration() !== null) {
                $ConfigCache = new Memory(__METHOD__);
                $Configuration = $ConfigCache->getValue($this->Type->getConfiguration());
                if (false !== $Configuration) {
                    $this->Type->setConfiguration($Configuration);
                } else {
                    $Configuration = parse_ini_file(__DIR__.'/Configuration.ini', true);
                    if (isset( $Configuration[$this->Type->getConfiguration()] )) {
                        $Configuration = $Configuration[$this->Type->getConfiguration()];
                    } else {
                        $Configuration = null;
                    }
                    $ConfigCache->setValue($this->Type->getConfiguration(), $Configuration);
                    $this->Type->setConfiguration($Configuration);
                }
            } else {
                $this->Type->setConfiguration(null);
            }
        }
    }

    /**
     * @return IApiInterface
     */
    public function getCache()
    {

        return $this->Type;
    }
}
