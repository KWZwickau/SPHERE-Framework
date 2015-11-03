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

        return FileSystem::getDownload($fileLocation->getRealPath(), "Klassenliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadFuxClassList()
    {

        $studentList = ReportingPerson::useService()->createFuxClassList();
        $fileLocation = ReportingPerson::useService()->createFuxClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Fux_Klassenliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadBirthdayClassList()
    {

        $studentList = ReportingPerson::useService()->createBirthdayClassList();
        $fileLocation = ReportingPerson::useService()->createBirthdayClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Birthday_Klassenliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadMedicalInsuranceClassList()
    {

        $studentList = ReportingPerson::useService()->createMedicalInsuranceClassList();
        $fileLocation = ReportingPerson::useService()->createMedicalInsuranceClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(), "Krankenkasse_Klassenliste.xls")->__toString();
    }
}