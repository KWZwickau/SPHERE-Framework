<?php
namespace SPHERE\Application\Api\Reporting\Custom\Coswig;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Reporting\Custom\Coswig\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Coswig
 */
class Common
{

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadClassList($DivisionCourseId = null)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($TableContent = Person::useService()->createClassList($tblDivisionCourse))
        && ($tblPersonList = $tblDivisionCourse->getStudents())) {
            $fileLocation = Person::useService()->createClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Coswig Klassenliste ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
