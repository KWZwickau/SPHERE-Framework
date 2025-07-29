<?php
namespace SPHERE\Application\Transfer;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Export\Export;
use SPHERE\Application\Transfer\Import\Import;
use SPHERE\Application\Transfer\Indiware\Import\Replacement\Replacement;
use SPHERE\Application\Transfer\Indiware\Indiware;
use SPHERE\Application\Transfer\Untis\Untis;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\Transfer
 */
class Transfer implements IClusterInterface
{

    public static function registerCluster()
    {

        Import::registerApplication();
        Export::registerApplication();
        Untis::registerApplication();
        Indiware::registerApplication();
        Education::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Datentransfer'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Datentransfer');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Transfer'));

        return $Stage;
    }
}
