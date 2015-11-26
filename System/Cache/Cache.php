<?php
namespace SPHERE\System\Cache;

use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;

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
     * @param bool $ForceType
     */
    public function __construct(ITypeInterface $Type = null, $ForceType = false)
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
                $Configuration = (new ConfigFactory())
                    ->createReader(__DIR__ . '/Configuration.ini', new IniReader())
                    ->getConfig();
                if (null !== $Configuration->getContainer($this->Type->getConfiguration())) {
                    $Configuration = $Configuration->getContainer($this->Type->getConfiguration());
                } else {
                    $Configuration = null;
                }
                $this->Type->setConfiguration($Configuration);
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
