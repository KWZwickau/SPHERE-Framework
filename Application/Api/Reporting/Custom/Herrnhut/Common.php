<?php
namespace SPHERE\Application\Api\Reporting\Custom\Herrnhut;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Reporting\Custom\Herrnhut\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Herrnhut
 */
class Common
{

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadProfileList($DivisionCourseId = null)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createProfileList($tblDivisionCourse))) {
            $fileLocation = Person::useService()->createProfileListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Herrnhut Klassenliste Profile ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadSignList($DivisionCourseId = null)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createSignList($tblDivisionCourse))) {
            $fileLocation = Person::useService()->createSignListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Herrnhut Unterschriftenliste ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadLanguageList($DivisionCourseId = null)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblPersonList = $tblDivisionCourse->getStudents())
            && !empty($TableContent = Person::useService()->createLanguageList($tblDivisionCourse))
        ) {
            $fileLocation = Person::useService()->createLanguageListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Herrnhut Klassenliste Fremdsprachen ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadClassList($DivisionCourseId = null)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblPersonList = $tblDivisionCourse->getStudents())
            && !empty($TableContent = Person::useService()->createClassList($tblDivisionCourse))
        ) {
            $fileLocation = Person::useService()->createClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Herrnhut Klassenliste ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadExtendedClassList($DivisionCourseId = null)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblPersonList = $tblDivisionCourse->getStudents())
            && !empty($TableContent = Person::useService()->createExtendedClassList($tblDivisionCourse))
        ) {
            $fileLocation = Person::useService()->createExtendedClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Herrnhut Erweiterte Klassenliste ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }
}
