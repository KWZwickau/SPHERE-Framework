<?php
namespace SPHERE\Application\Transfer\Davinci;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Davinci\Import\Import;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Davinci
 * @package SPHERE\Application\Transfer\Davinci
 */
class Davinci implements IApplicationInterface
{
    public static function registerApplication()
    {

        Import::registerModule();
//        ExportLectureship::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Davinci'))
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

        $Stage = new Stage('Davinci', 'Datentransfer');

        return $Stage;
    }
}