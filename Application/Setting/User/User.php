<?php
namespace SPHERE\Application\Setting\User;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Family;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class User
 * @package SPHERE\Application\Setting\User
 */
class User implements IApplicationInterface
{
    public static function registerApplication()
    {
        Account::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route('/People/User'), new Link\Name('Eltern und Schülerzugänge'),
                new Link\Icon(new Family()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/People/User', __NAMESPACE__.'\Account\Frontend::frontendDashboard')
        );
    }
}
