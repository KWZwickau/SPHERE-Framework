<?php
namespace SPHERE\Application\Setting\Device;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Device
 *
 * @package SPHERE\Application\Setting\Device
 */
class Device implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__),
            new Link\Name('Meine Geräte'), new Link\Icon(new PhoneMobil())
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'/Frontend::frontendDevice'
        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Two', __NAMESPACE__.'/Frontend::frontendDeviceTwo'
//        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__.'/../../App/Authentication/Process/Service/Entity',
            '\SPHERE\Application\App\Authentication\Process\Service\Entity'
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
