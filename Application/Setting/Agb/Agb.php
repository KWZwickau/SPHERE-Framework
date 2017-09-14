<?php

namespace SPHERE\Application\Setting\Agb;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class MyAccount
 *
 * @package SPHERE\Application\Setting\Agb
 */
class Agb implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        if (($tblAccount = Account::useService()->getAccountBySession())) {
            $tblSetting = Account::useService()->getSettingByAccount($tblAccount, 'AGB');
            if( $tblSetting && $tblSetting->getValue() == TblSetting::VAR_ACCEPT_AGB ) {
                Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__),
                    new Link\Name('AGB'), new Link\Icon(new MoreItems())
                ));
            }
        }

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendAgbView'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Accept', __NAMESPACE__.'\Frontend::frontendAcceptAgb'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Decline', __NAMESPACE__.'\Frontend::frontendDeclineAgb'
        ));

    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Account'),
            __DIR__.'/../../Platform/Gatekeeper/Authorization/Account/Service/Entity',
            '\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity'
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