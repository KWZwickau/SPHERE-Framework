<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineGradebook;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

class OnlineGradebook extends Extension implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {
        self::registerModule();
    }

    public static function registerModule()
    {
        // nur registrieren, wenn über die Mandanteneinstellung freigeschaltet ist und Personen angezeigt würden
        // oder wenn System-Account fürs Sperren der Routen

        $isRegistered = false;
        if (($tblAccount = Account::useService()->getAccountBySession())) {
            if (($tblIdentification = $tblAccount->getServiceTblIdentification()) && $tblIdentification->getName() == 'System') {
                // System-Account
                $isRegistered = true;
            } elseif (($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
                && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
            ) {
                // Schüler-Zugang
                $isRegistered = OnlineGradebook::useService()->getPersonListFromStudentLogin();
            } else {
                // Mitarbeiter oder Eltern-Zugang
                $isRegistered = OnlineGradebook::useService()->getPersonListFromCustodyLogin();
            }
        }

        if ($isRegistered) {
            Main::getDisplay()->addApplicationNavigation(
                new Link(new Link\Route(__NAMESPACE__), new Link\Name('Notenübersicht'), new Link\Icon(new Education()))
            );

            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendOnlineGradebook')
            );
        }
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}