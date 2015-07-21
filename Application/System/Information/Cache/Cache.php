<?php
namespace SPHERE\Application\System\Information\Cache;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Information\Cache\Frontend\Status;
use SPHERE\Common\Frontend\Icon\Repository\Flash;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Cache as CacheType;
use SPHERE\System\Cache\Type\Apcu;
use SPHERE\System\Cache\Type\ApcUser;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\OpCache;
use SPHERE\System\Cache\Type\TwigCache;

/**
 * Class Cache
 *
 * @package SPHERE\Application\System\Information\Cache
 */
class Cache implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Cache' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
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
     * @param bool $Clear
     *
     * @return Stage
     */
    public function frontendCache( $Clear = false )
    {

        $Stage = new Stage( 'Cache', 'Status' );

        if ($Clear) {
            ( new CacheType( new ApcUser() ) )->getCache()->clearCache();
            ( new CacheType( new Apcu() ) )->getCache()->clearCache();
            ( new CacheType( new Memcached() ) )->getCache()->clearCache();
            ( new CacheType( new OpCache() ) )->getCache()->clearCache();
            ( new CacheType( new TwigCache() ) )->getCache()->clearCache();
        }
        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( new LayoutRow(
                    new LayoutColumn( new Status(
                        ( new CacheType( new Memcached() ) )->getCache()
                    ) )
                ), new Title( 'Memcached' ) ),
                new LayoutGroup( new LayoutRow(
                    new LayoutColumn( new Status(
                        ( new CacheType( new Apcu() ) )->getCache()
                    ) )
                ), new Title( 'APCu' ) ),
                new LayoutGroup( new LayoutRow(
                    new LayoutColumn( new Status(
                        ( new CacheType( new OpCache() ) )->getCache()
                    ) )
                ), new Title( 'Zend OpCache' ) ),
                new LayoutGroup( new LayoutRow(
                    new LayoutColumn( new Status(
                        ( new CacheType( new TwigCache() ) )->getCache()
                    ) )
                ), new Title( 'Twig' ) ),
                new LayoutGroup( new LayoutRow(
                    new LayoutColumn(
                        new Primary( 'Clear', '/System/Information/Cache', new Flash(),
                            array( 'Clear' => true ), 'Cache leeren' )
                    )
                ) )
            ) )
        );
        return $Stage;
    }
}
