<?php
namespace SPHERE\Application\Api\Reporting\Custom\Hormersdorf;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Custom\Hormersdorf\Person\Person as HormersdorfPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Hormersdorf
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

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = HormersdorfPerson::useService()->createClassList($tblDivisionCourse))) {
            $fileLocation = HormersdorfPerson::useService()->createClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Klassenliste ".$tblDivisionCourse->getDisplayName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public function downloadStaffList()
    {

        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF))
        && ($tblPersonList = $tblGroup->getPersonList())
        && ($TableContent = HormersdorfPerson::useService()->createStaffList($tblPersonList))) {
            $fileLocation = HormersdorfPerson::useService()->createStaffListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Mitarbeiterliste (Geburtstage) ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
