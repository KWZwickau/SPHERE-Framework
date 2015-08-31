<?php
namespace SPHERE\System\Cache;

use SPHERE\System\Cache\Type\Apcu;
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
     *
     * @throws \Exception
     */
    function __construct(ITypeInterface $Type = null)
    {

        if ($Type === null) {
            $Type = new Memcached();
        }
        if (!$Type->isAvailable()) {
            $Type = new Apcu();
        }
        if (!$Type->isAvailable()) {
            $Type = new Memory();
        }

        $this->Type = $Type;
        if ($this->Type->needConfiguration() && $this->Type->getConfiguration() !== null) {
            $Configuration = parse_ini_file(__DIR__.'/Configuration.ini', true);
            if (isset( $Configuration[$this->Type->getConfiguration()] )) {
                $this->Type->setConfiguration($Configuration[$this->Type->getConfiguration()]);
            } else {
                $this->Type->setConfiguration(null);
            }
        }
    }

    /**
     * @return ITypeInterface
     */
    public function getCache()
    {

        return $this->Type;
    }
}
