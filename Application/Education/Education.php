<?php
namespace SPHERE\Application\Education;

use SPHERE\Application\Education\Graduation\Graduation;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Education implements IClusterInterface
{

    public static function registerCluster()
    {

        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Bildung' ) )
        );

        Graduation::registerApplication();
    }

}
