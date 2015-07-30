<?php
namespace SPHERE\Application\Education;

use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Education implements IClusterInterface
{

    public static function registerCluster()
    {
        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Education' ), new Link\Name( 'Bildung' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Education', __CLASS__.'::frontendWelcome'
        ) );
    }

}
