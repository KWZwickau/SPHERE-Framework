<?php
namespace SPHERE\Application\Api\Reporting\Custom\Chemnitz;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Chemnitz
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

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))){
            if(($tblPersonList = $tblDivisionCourse->getStudents())
            && ($TableContent = Person::useService()->createClassList($tblPersonList))){
                $fileLocation = Person::useService()->createClassListExcel($TableContent, $tblPersonList);
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Klassenliste ".$tblDivisionCourse->getDisplayName()
                    ." ".date("Y-m-d").".xlsx")->__toString();
            }
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public function downloadStaffList()
    {

        if (!empty(($TableContent = Person::useService()->createStaffList()))) {
            foreach ($TableContent as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $TableContent);
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
            if (!empty($tblPersonList = $tblGroup->getPersonList())) {
                $fileLocation = Person::useService()->createStaffListExcel($TableContent, $tblPersonList);
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Mitarbeiterliste ".date("Y-m-d").".xlsx")->__toString();
            }
        }
        return false;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return bool|string
     */
    public function downloadMedicList($DivisionCourseId = null)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($TableContent = Person::useService()->createMedicList($tblDivisionCourse))
        && $tblPersonList = $tblDivisionCourse->getStudents()) {
            $fileLocation = Person::useService()->createMedicListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Arztliste ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return bool|string
     */
    public function downloadParentTeacherConferenceList($DivisionCourseId = null)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && !empty($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createParentTeacherConferenceList($tblDivisionCourse))) {
            $fileLocation = Person::useService()->createParentTeacherConferenceListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Elternabende ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadClubMemberList()
    {

        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB))
        && !empty($tblPersonList = $tblGroup->getPersonList())
        && !empty($PersonList = Person::useService()->createClubMemberList($tblPersonList))
        ){
            foreach ($PersonList as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);
            // download
            $fileLocation = Person::useService()->createClubMemberListExcel($PersonList, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Vereinsmitgliederliste ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadInterestedPersonList()
    {

        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
        $tblPersonList = $tblGroup->getPersonList();
        if (!empty($tblPersonList) && !empty($TableContent = Person::useService()->createInterestedPersonList($tblPersonList))) {
            foreach ($TableContent as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $TableContent);
            $fileLocation = Person::useService()->createInterestedPersonListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Interessentenliste ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @return false|string
     */
    public function downloadSchoolFeeList()
    {

        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        if (!empty($tblPersonList = $tblGroup->getPersonList())
        && !empty($TableContent = Person::useService()->createSchoolFeeList($tblPersonList))) {
            foreach ($TableContent as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $TableContent);
            $fileLocation = Person::useService()->createSchoolFeeListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Schulgeldliste ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return string|bool
     */
    public function downloadPrintClassList($DivisionCourseId = null)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = Person::useService()->createPrintClassList($tblDivisionCourse))) {
            $fileLocation = Person::useService()->createPrintClassListExcel($TableContent, $tblPersonList, $tblDivisionCourse);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Klassenliste ".$tblDivisionCourse->getDisplayName()
                ." ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }
}
