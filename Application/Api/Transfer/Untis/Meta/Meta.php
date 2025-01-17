<?php

namespace SPHERE\Application\Api\Transfer\Untis\Meta;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Transfer\Untis\Export\Meta\Meta as MetaApp;
use SPHERE\Common\Main;

class Meta implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadMeta'
        ));
    }

    /**
     */
    public static function useService()
    {
    }

    /**
     */
    public static function useFrontend()
    {
    }

    /**
     * @param string $DivisionCourseId
     *
     * @return bool|string
     */
    public function downloadMeta(string $DivisionCourseId = '')
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($fileLocation = MetaApp::useService()->createCsv($DivisionCourseId))
        ) {
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "GPU010_" . $tblDivisionCourse->getName() . ".txt")->__toString();
        }

        return false;
    }
}