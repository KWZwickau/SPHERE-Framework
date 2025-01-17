<?php
namespace SPHERE\Application\Billing\Bookkeeping\Export;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Export
 * @package SPHERE\Application\Billing\Bookkeeping\Export
 */
class Export implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation Application
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route('/Billing/Export'), new Link\Name('Export'),
                new Link\Icon(new Download()))
        );
        /**
         * Register Navigation Module
         */
//        Main::getDisplay()->addModuleNavigation(
//            new Link(new Link\Route('/Billing/Balance/Excel'), new Link\Name('Bescheinigung Serienbrief'),
//                new Link\Icon(new Listing()))
//        );

        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Export',
                __NAMESPACE__.'\Export::frontendExport'
            )
        );

    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Billing', 'Invoice', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    public function frontendExport()
    {

        $Stage = new Stage('Download', 'aller Zahlungsinformationen');
//        $Stage->addButton(new External('Zahlungsinformationen Beitragszahler', '/Api/Billing/Accounting/AccountingDownload', new Download(), array(), '', External::STYLE_BUTTON_PRIMARY));
        $now = new \DateTime();
        $nowString = $now->format('d.m.Y');
        if(!isset($_POST['Date'])){
            $_POST['Date'] = $nowString;
        }
        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(new LayoutColumn( new Well(
            new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new DatePicker('Date', $nowString, "Fälligkeitsdatum für aktive Abrechnungen", new Clock())
                , 6),
                new FormColumn(
                    new Info('Abrechnungen gelten als aktiv, wenn:'
                        . new Container('- das Fälligkeitsdatum im aktiven Zeitraum der Abrechnung ist')
                        . new Container('- der Beitragsverursacher in der zur Beitragsart korrekten Personengruppe ist')
                    , null, false, '15', '0')
                , 6)
            ))), new Primary('Download', new Download(), true), '/Api/Billing/Accounting/AccountingDownload')
        ), 8),
        )))));
        return $Stage;
    }
}
