<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Publicly;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

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

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Token'),
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