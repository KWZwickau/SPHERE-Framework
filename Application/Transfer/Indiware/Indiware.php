<?php

namespace SPHERE\Application\Transfer\Indiware;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Indiware\Export\Export;
use SPHERE\Application\Transfer\Indiware\Import\Import;
use SPHERE\Application\Transfer\Indiware\Import\Lectureship as ImportLectureship;
use SPHERE\Application\Transfer\Indiware\Import\StudentCourse as ImportStudentCourse;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Indiware
 * @package SPHERE\Application\Transfer\Indiware
 */
class Indiware implements IApplicationInterface
{
    public static function registerApplication()
    {

        Import::registerModule();
        ImportLectureship::registerModule();
        ImportStudentCourse::registerModule();
        Export::registerModule();
//        ExportLectureship::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Indiware'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Indiware', 'Datentransfer');

        return $Stage;
    }
}