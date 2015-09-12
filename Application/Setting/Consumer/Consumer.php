<?php
namespace SPHERE\Application\Setting\Consumer;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\SchoolBoard\SchoolBoard;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Consumer
 *
 * @package SPHERE\Application\Setting\Consumer
 */
class Consumer implements IApplicationInterface
{

    public static function registerApplication()
    {

        School::registerModule();
        SchoolBoard::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Mandant'), new Link\Icon(new Building()))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __CLASS__.'::frontendDashboard')
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Mandant');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Consumer'));

        return $Stage;
    }
}
