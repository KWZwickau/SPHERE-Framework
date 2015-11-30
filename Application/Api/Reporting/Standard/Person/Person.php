<?php
namespace SPHERE\Application\Api\Reporting\Standard\Person;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Standard\Person
 */
class Person
{

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = ReportingPerson::useService()->createClassList($tblDivision);
            if ($studentList) {
                $fileLocation = ReportingPerson::useService()->createClassListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Klassenliste " . $tblDivision->getTblLevel()->getName() . $tblDivision->getName()
                    . " " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadExtendedClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = ReportingPerson::useService()->createExtendedClassList($tblDivision);
            if ($studentList) {
                $fileLocation = ReportingPerson::useService()->createExtendedClassListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Erweiterte_Klassenliste " . $tblDivision->getTblLevel()->getName() . $tblDivision->getName()
                    . " " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadBirthdayClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = ReportingPerson::useService()->createBirthdayClassList($tblDivision);
            if ($studentList) {
                $fileLocation = ReportingPerson::useService()->createBirthdayClassListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Birthday_Klassenliste " . $tblDivision->getTblLevel()->getName() . $tblDivision->getName()
                    . " " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadMedicalInsuranceClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = ReportingPerson::useService()->createMedicalInsuranceClassList($tblDivision);
            if ($studentList) {
                $fileLocation = ReportingPerson::useService()->createMedicalInsuranceClassListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Krankenkasse_Klassenliste " . $tblDivision->getTblLevel()->getName() . $tblDivision->getName()
                    . " " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $GroupId
     * @return bool|string
     */
    public function downloadGroupList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $groupList = ReportingPerson::useService()->createGroupList($tblGroup);
            if ($groupList) {
                $fileLocation = ReportingPerson::useService()->createGroupListExcel($groupList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Gruppenliste " . $tblGroup->getName()
                    . " " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }

}