<?php

namespace SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\AppointmentGrade as AppointmentGradeTask;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

class AppointmentGrade implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadAppointmentGrade'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param int $Period
     * @param int $TaskId
     *
     * @return string
     */
    public function downloadAppointmentGrade(int $Period, int $TaskId)
    {

        $fileLocation = AppointmentGradeTask::useService()->createGradeListCsv($Period, $TaskId);
        $tblTask = Grade::useService()->getTaskById($TaskId);
        if ($fileLocation && $tblTask) {
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Stichtagsnoten ".$tblTask->getDate()->format('d.m.Y')." ".$tblTask->getName().".csv")->__toString();
        }
        return 'Die zu erzeugende CSV ist leer, eine Datei konnte nicht erstellt werden.';
    }
}