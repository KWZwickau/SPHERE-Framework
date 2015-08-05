<?php
namespace SPHERE\Application\People\Meta;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Meta implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Meta' ) )
        );
    }

}
