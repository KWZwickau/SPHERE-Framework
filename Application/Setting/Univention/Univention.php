<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Publicly;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Univention
 * @package SPHERE\Application\Setting\Univention
 */
class Univention implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        if (Account::useService()->getAccountBySession()) {
            Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__),
                new Link\Name('UCS school'), new Link\Icon(new Publicly())
            ));
        }

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendUnivention'
        ));

    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }


    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}