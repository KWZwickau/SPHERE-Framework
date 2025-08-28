<?php
namespace SPHERE\Application\Api\Reporting\Custom\Kreuzgymnasium;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Reporting\Custom\Kreuzgymnasium\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Kreuzgymnasium
 */
class Common
{

    /**
     * @param string $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadSignList(string $DivisionCourseId, bool $isLandscape = false)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createSignList($tblDivisionCourse))
        ) {
            $fileLocation = Person::useService()->createSignListExcel($TableContent, $tblPersonList, $isLandscape);

            return FileSystem::getDownload($fileLocation->getRealPath(), "Kreuzgymnasium Unterschriftenliste ".$tblDivisionCourse->getDisplayName()." "
                .date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadStudentCount()
    {
        $tblYearList = Term::useService()->getYearByNow();
        list($DivisionCourseSek1List, $DivisionCourseSek2List, $DivisionCourseDAZList, $DivisionCourseSiAList) =
            Person::useService()->getStudentCountDivisionList($tblYearList);
        $tblYear = current($tblYearList);
        if(true){
            $fileLocation = Person::useService()->createStudentCountExcel($tblYear->getYear(), $DivisionCourseSek1List, $DivisionCourseSek2List, $DivisionCourseDAZList, $DivisionCourseSiAList);

            return FileSystem::getDownload($fileLocation->getRealPath(), "Kreuzgymnasium Schülerzählung ".$tblYear->getYear()." "
                .date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
