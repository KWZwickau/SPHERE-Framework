<?php
namespace SPHERE\Application\Company;

use SPHERE\Application\Company\Group\Group;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Company
 *
 * @package SPHERE\Application\Company
 */
class Company implements IClusterInterface
{

    public static function registerCluster()
    {

        Group::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Company' ), new Link\Name( 'Firmen' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company', __CLASS__.'::frontendDashboard'
        ) );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage( 'Dashboard', 'Firmen' );

        return $Stage;
    }
}
