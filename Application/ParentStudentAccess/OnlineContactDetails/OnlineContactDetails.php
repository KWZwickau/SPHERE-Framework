<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

class OnlineContactDetails extends Extension implements IApplicationInterface, IModuleInterface
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
                $isRegistered = OnlineContactDetails::useService()->getPersonListFromStudentLogin();
            } else {
                // Mitarbeiter oder Eltern-Zugang
                $isRegistered = OnlineContactDetails::useService()->getPersonListFromCustodyLogin();
            }
        }

        if ($isRegistered) {
            Main::getDisplay()->addApplicationNavigation(
                new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kontakt-Daten'), new Link\Icon(new Conversation()))
            );

            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendOnlineContactDetails')
            );
        }
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service(
            new Identifier('Contact', 'Address', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}