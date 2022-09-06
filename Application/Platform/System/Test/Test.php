<?php
namespace SPHERE\Application\Platform\System\Test;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Test
 *
 * @package SPHERE\Application\System\Platform\Test
 */
class Test implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Frontend'), new Link\Name('Frontend-Test'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/TestSite'), new Link\Name('Test Seite'))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Frontend',
                __NAMESPACE__.'\Frontend::frontendPlatform'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/TestSite',
                __NAMESPACE__.'\Frontend::frontendTestSite'
            )
        );
    }

    public static function useService()
    {
//         TODO: Implement useService() method.
        return new Service(new Identifier('Platform', 'System', 'BasicData'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
