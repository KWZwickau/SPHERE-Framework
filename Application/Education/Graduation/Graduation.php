<?php
namespace SPHERE\Application\Education\Graduation;

use SPHERE\Application\Education\Graduation\Certificate\Certificate;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class Graduation implements IApplicationInterface
{

    public static function registerApplication()
    {
        Certificate::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Zensuren' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ) );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage( 'Dashboard' );

        return $Stage;
    }
}
