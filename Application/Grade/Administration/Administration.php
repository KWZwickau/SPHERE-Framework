<?php

namespace SPHERE\Application\Grade\Administration;


use SPHERE\Application\Grade\Administration\GradeType\GradeType;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Administration
 * @package SPHERE\Application\Grade\Administration
 */
class Administration implements IApplicationInterface
{

    public static function registerApplication()
    {

        GradeType::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Administration'),
                new Link\Icon(new \SPHERE\Common\Frontend\Icon\Repository\Person())
            )
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Administration');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Administration'));

        return $Stage;
    }

}