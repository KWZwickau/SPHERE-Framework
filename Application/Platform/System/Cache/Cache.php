<?php
namespace SPHERE\Application\Platform\System\Cache;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\System\Cache\Frontend\Status;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\OpCacheHandler;
use SPHERE\System\Cache\Handler\SmartyHandler;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

/**
 * Class Cache
 *
 * @package SPHERE\Application\System\Platform\Cache
 */
class Cache extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Cache'))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                'Cache::frontendCache'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param bool $Clear
     *
     * @return Stage
     */
    public function frontendCache($Clear = false)
    {

        $Stage = new Stage('Cache', 'Status');

        $Stage->addButton(new Standard('Cache löschen', '/Platform/System/Cache', null, array('Clear' => true),
            'Cache leeren'));
        $Stage->addButton(new External('phpMemcachedAdmin',
            $this->getRequest()->getPathBase().'/UnitTest/Console/phpMemcachedAdmin-1.2.2'));

        if ($Clear) {
            $this->getCache(new MemcachedHandler(), 'Memcached')->clearCache();
            $this->getCache(new APCuHandler())->clearCache();
            $this->getCache(new MemoryHandler())->clearCache();
            $this->getCache(new OpCacheHandler())->clearCache();
            $this->getCache(new TwigHandler())->clearCache();
            $this->getCache(new SmartyHandler())->clearCache();
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        $this->getCache(new MemcachedHandler(), 'Memcached')->getStatus()
                    ))
                ), new Title('Memcached')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        $this->getCache(new APCuHandler())->getStatus()
                    ))
                ), new Title('APCu')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        $this->getCache(new MemoryHandler())->getStatus()
                    ))
                ), new Title('Memory')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        $this->getCache(new OpCacheHandler())->getStatus()
                    ))
                ), new Title('Zend OpCache')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        $this->getCache(new TwigHandler())->getStatus()
                    ))
                ), new Title('Template: Twig')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        $this->getCache(new SmartyHandler())->getStatus()
                    ))
                ), new Title('Template: Smarty')),
            ))
        );
        return $Stage;
    }


}
