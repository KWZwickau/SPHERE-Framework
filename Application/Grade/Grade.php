<?php

namespace SPHERE\Application\Grade;


use SPHERE\Application\Grade\Administration\Administration;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Grade
 * @package SPHERE\Application\Grade
 */
class Grade implements IClusterInterface
{

    public static function registerCluster()
    {
        Administration::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Noten'))
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

        $Stage = new Stage('Dashboard', 'Noten');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Noten'));

        return $Stage;
    }
}