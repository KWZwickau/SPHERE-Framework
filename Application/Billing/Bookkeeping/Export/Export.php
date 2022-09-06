<?php
namespace SPHERE\Application\Billing\Bookkeeping\Export;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\External;
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
        $Stage->addButton(new External('Zahlungsinformationen Beitragszahler', '/Api/Billing/Accounting/AccountingDownload', new Download(), array(), '', External::STYLE_BUTTON_PRIMARY));
        return $Stage;
    }
}
