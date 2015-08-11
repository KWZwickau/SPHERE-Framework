<?php
namespace SPHERE\Application\Transfer;

use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\Transfer
 */
class Transfer implements IClusterInterface
{

    public static function registerCluster()
    {

        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/FuxMedia' ), new Link\Name( 'Datentransfer' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/FuxMedia', __CLASS__.'::frontendWelcome'
        ) );
    }

}
