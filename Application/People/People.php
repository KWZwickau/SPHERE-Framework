<?php

namespace SPHERE\Application\People;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\People\ContactDetails\ContactDetails;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Meta;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Search;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class People
 *
 * @package SPHERE\Application\People
 */
class People implements IClusterInterface
{

    public static function registerCluster()
    {
        Search::registerApplication();
        Person::registerApplication();
        Group::registerApplication();
        Meta::registerApplication();
        Relationship::registerApplication();
        ContactDetails::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Personen'), new Link\Icon(new PersonIcon()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Search\Frontend::frontendSearch'
        ));
    }
}
