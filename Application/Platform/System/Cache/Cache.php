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
use SPHERE\System\Cache\Cache as CacheType;
use SPHERE\System\Cache\Type\Apcu;
use SPHERE\System\Cache\Type\ApcUser;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Cache\Type\OpCache;
use SPHERE\System\Cache\Type\SmartyCache;
use SPHERE\System\Cache\Type\TwigCache;
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
            (new CacheType(new ApcUser(), true))->getCache()->clearCache();
            (new CacheType(new Apcu(), true))->getCache()->clearCache();
            (new CacheType(new Memcached(), true))->getCache()->clearCache(true);
            (new CacheType(new OpCache(), true))->getCache()->clearCache();
            (new CacheType(new TwigCache(), true))->getCache()->clearCache();
            (new CacheType(new SmartyCache(), true))->getCache()->clearCache();
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        (new CacheType(new Memcached(), true))->getCache()
                    ))
                ), new Title('Memcached')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        (new CacheType(new Apcu(), true))->getCache()
                    ))
                ), new Title('APCu')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        (new CacheType(new Memory(), true))->getCache()
                    ))
                ), new Title('Memory')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        (new CacheType(new OpCache(), true))->getCache()
                    ))
                ), new Title('Zend OpCache')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        (new CacheType(new TwigCache(), true))->getCache()
                    ))
                ), new Title('Template: Twig')),
                new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Status(
                        (new CacheType(new SmartyCache(), true))->getCache()
                    ))
                ), new Title('Template: Smarty')),
            ))
        );
        return $Stage;
    }


}
