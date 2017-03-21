<?php
namespace SPHERE\Application\Setting\User;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Family;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

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
            Main::getDispatcher()->createRoute('/People/User', __CLASS__.'::frontendDashboard')
        );
    }

    /**
     * @return Service
     */
    public function useService()
    {
        return new Service();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Eltern und Schülerzugänge', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/People', new ChevronLeft()));
        $IsSend = $IsExport = true;
        $tblUserAccountList = Account::useService()->getUserAccountByIsSendAndIsExport($IsSend, $IsExport);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( $tblUserAccountList
                                ? new TableData($tblUserAccountList, new Title('Hier könnten die Accounts stehen. (mit Zusatzinfos und der möglichkeit diese zu löschen)'))
                                : new Warning('Keine Benutzer vorhanden')
                            )
                        )
                    )
                )
            )
        );
        return $Stage;
    }
}
