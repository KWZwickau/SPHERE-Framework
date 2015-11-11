<?php
namespace SPHERE\Application\Api\Reporting\Standard\Person;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Standard\Person
 */
class Person
{

    /**
     * @return string
     */
    public function downloadClassList()
    {

        $studentList = ReportingPerson::useService()->createClassList();
        $fileLocation = ReportingPerson::useService()->createClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Klassenliste ".date('Y-m-d H:i:s').".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadExtendedClassList()
    {

        $studentList = ReportingPerson::useService()->createExtendedClassList();
        $fileLocation = ReportingPerson::useService()->createExtendedClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Erweiterte_Klassenliste ".date('Y-m-d H:i:s').".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadBirthdayClassList()
    {

        $studentList = ReportingPerson::useService()->createBirthdayClassList();
        $fileLocation = ReportingPerson::useService()->createBirthdayClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Birthday_Klassenliste ".date('Y-m-d H:i:s').".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadMedicalInsuranceClassList()
    {

        $studentList = ReportingPerson::useService()->createMedicalInsuranceClassList();
        $fileLocation = ReportingPerson::useService()->createMedicalInsuranceClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Krankenkasse_Klassenliste ".date('Y-m-d H:i:s').".xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadEmployeeList()
    {

        $employeeList = ReportingPerson::useService()->createEmployeeList();
        $fileLocation = ReportingPerson::useService()->createEmployeeListExcel($employeeList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Krankenkasse_Klassenliste ".date('Y-m-d H:i:s').".xls")->__toString();
    }

}