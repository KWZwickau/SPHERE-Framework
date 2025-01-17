<?php
namespace SPHERE\Application\Api\Reporting\Custom\Gersdorf;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Reporting\Custom\Gersdorf\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Gersdorf
 */
class Common
{

    /**
     * @param string $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadClassList(string $DivisionCourseId)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createClassList($tblDivisionCourse))
        ){
            $fileLocation = Person::useService()->createClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Gersdorf Klassenliste ".$tblDivisionCourse->getDisplayName()." "
                .date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param string $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadSignList(string $DivisionCourseId)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createSignList($tblDivisionCourse))
        ) {
            $fileLocation = Person::useService()->createSignListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Gersdorf Unterschriftenliste ".$tblDivisionCourse->getDisplayName()." "
                .date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadElectiveClassList($DivisionCourseId)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createElectiveClassList($tblDivisionCourse))) {
            $fileLocation = Person::useService()->createElectiveClassListExcel($TableContent, $tblPersonList, $tblDivisionCourse);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Gersdorf Klassenliste Fremdsprachen ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadClassPhoneList($DivisionCourseId)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createClassPhoneList($tblDivisionCourse))) {
            $fileLocation = Person::useService()->createClassPhoneListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Gersdorf Erweiterte Klassenliste ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param $isTeacher
     *
     * @return false|string
     */
    public function downloadTeacherList($isTeacher = false)
    {

        if(!empty($PersonList = Person::useService()->createTeacherList($isTeacher))
        && !empty($tblPersonList = Person::useService()->getPersonStaffList($isTeacher))
        ) {
            $fileLocation = Person::useService()->createTeacherListExcel($PersonList, $tblPersonList, $isTeacher);
            if($isTeacher){
                return FileSystem::getDownload($fileLocation->getRealPath(), "Lehrerliste ".date("Y-m-d").".xlsx")->__toString();
            }
            return FileSystem::getDownload($fileLocation->getRealPath(), "Mitarbeiter u Lehrer ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
