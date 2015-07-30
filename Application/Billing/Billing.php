<?php
namespace SPHERE\Application\Billing;

use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Billing implements IClusterInterface
{

    public static function registerCluster()
    {
        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Billing' ), new Link\Name( 'Fakturierung' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Billing', __CLASS__.'::frontendWelcome'
        ) );
    }

}
