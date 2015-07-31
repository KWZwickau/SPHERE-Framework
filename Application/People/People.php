<?php
namespace SPHERE\Application\People;

use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class People implements IClusterInterface
{

    public static function registerCluster()
    {

        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Person' ), new Link\Name( 'Personen' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person', __CLASS__.'::frontendWelcome'
        ) );
    }

}
