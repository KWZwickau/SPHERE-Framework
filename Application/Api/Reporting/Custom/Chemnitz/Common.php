<?php
namespace SPHERE\Application\Api\Reporting\Custom\Chemnitz;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person;
use SPHERE\Common\Frontend\Message\Repository\Warning;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Chemnitz
 */
class Common
{

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = Person::useService()->createClassList($tblDivision);
            if ($studentList) {
                $fileLocation = Person::useService()->createClassListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Klassenliste " . $tblDivision->getName() ." " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function downloadStaffList()
    {

        $staffList = Person::useService()->createStaffList();

        if ($staffList) {
            $fileLocation = Person::useService()->createStaffListExcel($staffList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Mitarbeiterliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
        }

        return false;
    }

    /**
     * @param $DivisionId
     *
     * @return bool|string
     */
    public function downloadMedicList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = Person::useService()->createMedicList($tblDivision);
            if ($studentList) {
                $fileLocation = Person::useService()->createMedicListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Arztliste " . $tblDivision->getName() ." " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadParentTeacherConferenceList($DivisionId = null)
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = Person::useService()->createParentTeacherConferenceList($tblDivision);
            if ($studentList) {
                $fileLocation = Person::useService()->createParentTeacherConferenceListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Elternabende " . $tblDivision->getName() ." " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadClubMemberList()
    {

        $clubMemberList = Person::useService()->createClubMemberList();

        if ($clubMemberList)
        {
        $fileLocation = Person::useService()->createClubMemberListExcel($clubMemberList);

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Chemnitz Vereinsmitgliederliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadInterestedPersonList()
    {

        $interestedPersonList = Person::useService()->createInterestedPersonList();
        if ($interestedPersonList) {
            $fileLocation = Person::useService()->createInterestedPersonListExcel($interestedPersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Interessentenliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadSchoolFeeList()
    {

        $studentList = Person::useService()->createSchoolFeeList();
        if ($studentList) {
            $fileLocation = Person::useService()->createSchoolFeeListExcel($studentList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Chemnitz Schulgeldliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
        }

        return false;
    }
}
