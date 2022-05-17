<?php

namespace SPHERE\Application\ParentStudentAccess;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Common\Frontend\Icon\Repository\Family;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class ParentStudentAccess implements IClusterInterface
{
    public static function registerCluster()
    {
        OnlineAbsence::registerApplication();
        OnlineContactDetails::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Eltern-Schüler-Zugang'), new Link\Icon(new Family()))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard(): Stage
    {
        return new Stage();
    }
}
