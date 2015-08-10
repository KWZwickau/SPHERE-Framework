<?php
namespace SPHERE\Application\People;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Meta;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Search\Search;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class People implements IClusterInterface
{

    public static function registerCluster()
    {

        Person::registerApplication();
        Group::registerApplication();
        Search::registerApplication();
        Meta::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Personen' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ) );
    }

    public function frontendDashboard()
    {

        $Stage = new Stage( 'Dashboard', 'Personen' );

        return $Stage;
    }
}
