<?php
namespace SPHERE\Application\Api\Reporting\Custom\Schneeberg;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Reporting\Custom\Schneeberg\Person\Person as SchneebergPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Schneeberg
 */
class Person
{

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadClassList($DivisionCourseId = null)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && !empty($TableContent = SchneebergPerson::useService()->createClassList($tblDivisionCourse))) {
            $fileLocation = SchneebergPerson::useService()->createClassListExcel($TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Klassenliste ".$tblDivisionCourse->getDisplayName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}