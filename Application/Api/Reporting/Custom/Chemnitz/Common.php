<?php
namespace SPHERE\Application\Api\Reporting\Custom\Chemnitz;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Chemnitz
 */
class Common
{

    /**
     * @return string
     */
    public function downloadClassList()
    {

        $studentList = Person::useService()->createClassList();
        $fileLocation = Person::useService()->createClassListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(),"Chemnitz Klassenliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadStaffList()
    {

        $staffList = Person::useService()->createStaffList();
        $fileLocation = Person::useService()->createStaffListExcel($staffList);

        return FileSystem::getDownload($fileLocation->getRealPath(),"Chemnitz Mitarbeiterliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadMedicList()
    {

        $studentList = Person::useService()->createMedicList();
        $fileLocation = Person::useService()->createMedicListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(),"Chemnitz Arztliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadParentTeacherConferenceList()
    {
        $studentList = Person::useService()->createParentTeacherConferenceList();
        $fileLocation = Person::useService()->createParentTeacherConferenceListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(),"Chemnitz Elternabende.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadClubMemberList()
    {

        $clubMemberList = Person::useService()->createClubMemberList();
        $fileLocation = Person::useService()->createClubMemberListExcel($clubMemberList);

        return FileSystem::getDownload($fileLocation->getRealPath(),"Chemnitz Vereinsmitgliederliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadInterestedPersonList()
    {

        $interestedPersonList = Person::useService()->createInterestedPersonList();
        $fileLocation = Person::useService()->createInterestedPersonListExcel($interestedPersonList);

        return FileSystem::getDownload($fileLocation->getRealPath(),"Chemnitz Interessentenliste.xls")->__toString();
    }

    /**
     * @return string
     */
    public function downloadSchoolFeeList()
    {

        $studentList = Person::useService()->createSchoolFeeList();
        $fileLocation = Person::useService()->createSchoolFeeListExcel($studentList);

        return FileSystem::getDownload($fileLocation->getRealPath(),"Chemnitz Schulgeldliste.xls")->__toString();
    }
}
