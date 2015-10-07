<?php
namespace SPHERE\Application\Manual\Kreda;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Kreda
 *
 * @package SPHERE\Application\Manual\Kreda
 */
class Kreda implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('KREDA Handbuch'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendKreda'
        ));
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

}
