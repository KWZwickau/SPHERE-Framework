<?php
namespace SPHERE\Application\Api\Reporting\Custom\Muldental;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Reporting\Custom\Muldental\Person\Person;
use SPHERE\Common\Frontend\Message\Repository\Warning;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Muldental\Coswig
 */
class Common
{

    /**
     * @param null|string $level
     *
     * @return bool|string
     */
    public function downloadClassList($level = null)
    {

        // Sammeln Personenliste aus level
        $tblPersonList = array();
        if($level){
            if (!empty($DivisionList = Person::useFrontend()->getDivisionListByLevel($level))) {
                if(isset($DivisionList[$level]['Person'])){
                    $tblPersonList = $DivisionList[$level]['Person'];
                }
            }
        }
        if(!empty($tblPersonList)
        && !empty($TableContent = Person::useService()->createClassList($tblPersonList))){
            $fileLocation = Person::useService()->createClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Muldental Stufenliste ".$level." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null|string $DivisionCourseId
     *
     * @return bool|string
     */
    public function downloadCoreList($DivisionCourseId = null)
    {

        $tblPersonList = false;
        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $tblPersonList = $tblDivisionCourse->getStudents();
        }
        if($tblPersonList && !empty($TableContent = Person::useService()->createClassList($tblPersonList))){
            $fileLocation = Person::useService()->createClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Muldental Stammgruppe ".$tblDivisionCourse->getDisplayName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
