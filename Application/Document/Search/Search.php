<?php
namespace SPHERE\Application\Document\Search;

use SPHERE\Application\Document\Search\FileSystem\FileSystem;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Search
 *
 * @package SPHERE\Application\Document\Search
 */
class Search implements IApplicationInterface
{

    public static function registerApplication()
    {
        FileSystem::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Suche'))
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

        $Stage = new Stage('Dashboard', 'Suche');

        return $Stage;
    }
}
