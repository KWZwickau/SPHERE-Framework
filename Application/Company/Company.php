<?php
namespace SPHERE\Application\Company;

use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Company implements IClusterInterface
{

    public static function registerCluster()
    {

        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Company' ), new Link\Name( 'Firmen' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company', __CLASS__.'::frontendWelcome'
        ) );
    }

}
