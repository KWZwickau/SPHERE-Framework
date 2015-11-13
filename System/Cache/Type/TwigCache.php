<?php
namespace SPHERE\System\Cache\Type;

use MOC\V\Component\Template\Component\Bridge\Repository\TwigTemplate;
use SPHERE\System\Cache\ITypeInterface;

/**
 * Class TwigCache
 *
 * @package SPHERE\System\Cache\Type
 */
class TwigCache implements ITypeInterface
{

    private static $Cache = '/../../../Library/MOC-V/Component/Template/Component/Bridge/Repository/TwigTemplate';

    /**
     * @param bool $doPrune
     */
    public function clearCache($doPrune = false)
    {

        (new TwigTemplate())->createInstance()->clearCacheFiles();
        (new TwigTemplate())->createInstance()->clearTemplateCache();
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
    public function getWastedSize()
    {

        return $this->getAvailableSize() - $this->getFreeSize() - $this->getUsedSize();
    }

    /**
     * @return integer
     */
    public function getAvailableSize()
    {

        return ( disk_total_space(__DIR__) );
    }

    /**
     * @return integer
     */
    public function getFreeSize()
    {

        return ( disk_free_space(__DIR__) );
    }

    /**
     * @return integer
     */
    public function getUsedSize()
    {

        $Total = 0;
        $Path = realpath(__DIR__.self::$Cache);
        if ($Path !== false) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Path,
                \FilesystemIterator::SKIP_DOTS)) as $Object) {
                $Total += $Object->getSize() * 1024;
            }
        }
        return $Total;
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
