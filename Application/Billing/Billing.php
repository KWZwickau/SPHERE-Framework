<?php
namespace SPHERE\Application\Billing;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Billing\Accounting\Accounting;
use SPHERE\Application\Billing\Bookkeeping\Bookkeeping;
use SPHERE\Application\Billing\Inventory\Inventory;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Billing
 *
 * @package SPHERE\Application\Billing
 */
class Billing implements IClusterInterface
{

    public static function registerCluster()
    {

        /**
         * Register Application
         */
        Inventory::registerApplication();
        Accounting::registerApplication();
        Bookkeeping::registerApplication();

        /**
         * Register Navigation
         */
        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Fakturierung'))
        );

        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendBilling'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Bookkeeping', __CLASS__.'::frontendBookkeeping'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendBilling()
    {

        $Stage = new Stage('Dashboard', 'Fakturierung');

        $Stage->setContent(new Layout(new LayoutGroup(
            new LayoutRow(array(
                new LayoutColumn(
                    new \SPHERE\Common\Frontend\Link\Repository\Link(
                        new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png'), 'Anleitung Fakturierung', 'Stand: 21.02.2022')
                        , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Billing'))
                , 2),
            )),
        )));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendBookkeeping()
    {

        $Stage = new Stage('Dashboard', ' Buchungen');
        return $Stage;
    }

}
