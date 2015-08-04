<?php
namespace SPHERE\Application\Company\Group;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Group
 *
 * @package SPHERE\Application\Company\Group
 */
class Group implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Firmengruppen' ),
                new Link\Icon( new GroupIcon() )
            )
        );
    }
}
