<?php
namespace SPHERE\Application\Api\Reporting\Custom\Radebeul;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Reporting\Custom\Radebeul\Person\Person as RadebeulPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Radebeul
 */
class Person
{

    /**
     * @param string $DivisionCourseId
     *
     * @return bool|string
     */
    public function downloadParentTeacherConferenceList(string $DivisionCourseId)
    {

        if(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        && ($tblPersonList = $tblDivisionCourse->getStudents())
        && !empty($TableContent = RadebeulPerson::useService()->createParentTeacherConferenceList($tblDivisionCourse))
        ) {
            $fileLocation = RadebeulPerson::useService()->createParentTeacherConferenceListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Radebeul Anwesenheitsliste für Elternabende ".$tblDivisionCourse->getDisplayName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public function downloadDenominationList()
    {

        list($TableContent, $countArray) = RadebeulPerson::useService()->createDenominationList();;
        if(!empty($TableContent)) {
            $fileLocation = RadebeulPerson::useService()->createDenominationListExcel($TableContent, $countArray);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Radebeul Religionszugehörigkeit "." ".date("Y-m-d").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param string $GroupId
     *
     * @return bool|string
     */
    public function downloadPhoneList(string $GroupId)
    {

        if(($tblGroup = Group::useService()->getGroupById($GroupId))
        && !empty($TableContent = RadebeulPerson::useService()->createPhoneList($tblGroup))) {
            $fileLocation = RadebeulPerson::useService()->createPhoneListExcel($TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Radebeul Telefonliste ".$tblGroup->getName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param string $GroupId
     *
     * @return bool|string
     */
    public function downloadKindergartenList(string $GroupId)
    {

        if(($tblGroup = Group::useService()->getGroupById($GroupId))
        && !empty($TableContent = RadebeulPerson::useService()->createKindergartenList($tblGroup))) {
            $fileLocation = RadebeulPerson::useService()->createKindergartenListExcel($tblGroup, $TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Radebeul Kinderhausliste ".$tblGroup->getName()." ".date("Y-m-d").".xlsx")
                ->__toString();
        }
        return false;
    }

    /**
     * @param string $GroupId
     *
     * @return bool|string
     */
    public function downloadRegularSchoolList(string $GroupId)
    {

        if(($tblGroup = Group::useService()->getGroupById($GroupId))
        && !empty($TableContent = RadebeulPerson::useService()->createRegularSchoolList($tblGroup))) {
            $fileLocation = RadebeulPerson::useService()->createRegularSchoolListExcel($TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Radebeul Stammschulenliste ".$tblGroup->getName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param string $GroupId
     *
     * @return bool|string
     */
    public function downloadDiseaseList(string $GroupId)
    {

        if(($tblGroup = Group::useService()->getGroupById($GroupId))
        && !empty($TableContent = RadebeulPerson::useService()->createDiseaseList($tblGroup))) {
            $fileLocation = RadebeulPerson::useService()->createDiseaseListExcel($tblGroup, $TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(),"Radebeul Allergieliste ".$tblGroup->getName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param string $PLZ
     *
     * @return bool|string
     */
    public function downloadNursery(string $PLZ = '')
    {

        if(($tblGroup = Group::useService()->getGroupByName('Hort'))
        && (!empty($TableContent = RadebeulPerson::useService()->createNursery($tblGroup, $PLZ)))) {
            $fileLocation = RadebeulPerson::useService()->createNurseryExcel($TableContent, $PLZ);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Radebeul Stichtagsmeldung Deckblatt ".$tblGroup->getName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param string $PLZ
     *
     * @return bool|string
     */
    public function downloadNurseryList(string $PLZ = '')
    {

        if(($tblGroup = Group::useService()->getGroupByName('Hort'))
        && !empty($TableContent = RadebeulPerson::useService()->createNursery($tblGroup, $PLZ))) {
            $fileLocation = RadebeulPerson::useService()->createNurseryListExcel($TableContent, $PLZ);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Radebeul Stichtagsmeldung ".$tblGroup->getName()." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}