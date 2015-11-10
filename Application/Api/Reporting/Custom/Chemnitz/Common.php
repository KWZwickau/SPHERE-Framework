<?php
namespace SPHERE\Application\Api\Reporting\Custom\Chemnitz;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person;

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
     * @return string
     */
    public function downloadStaffList()
    {

        $staffList = Person::useService()->createStaffList();
        $fileLocation = Person::useService()->createStaffListExcel($staffList);

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Chemnitz Mitarbeiterliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadMedicList()
    {

        $studentList = Person::useService()->createMedicList();
        $fileLocation = Person::useService()->createMedicListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Chemnitz Arztliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadParentTeacherConferenceList()
    {
        $studentList = Person::useService()->createParentTeacherConferenceList();
        $fileLocation = Person::useService()->createParentTeacherConferenceListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Chemnitz Elternabende " . date("Y-m-d H:i:s") . ".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadClubMemberList()
    {

        $clubMemberList = Person::useService()->createClubMemberList();
        $fileLocation = Person::useService()->createClubMemberListExcel($clubMemberList);

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Chemnitz Vereinsmitgliederliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadInterestedPersonList()
    {

        $interestedPersonList = Person::useService()->createInterestedPersonList();
        $fileLocation = Person::useService()->createInterestedPersonListExcel($interestedPersonList);

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Chemnitz Interessentenliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadSchoolFeeList()
    {

        $studentList = Person::useService()->createSchoolFeeList();
        $fileLocation = Person::useService()->createSchoolFeeListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Chemnitz Schulgeldliste " . date("Y-m-d H:i:s") . ".xls")->__toString();
    }
}
