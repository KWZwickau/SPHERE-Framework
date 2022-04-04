<?php
namespace SPHERE\Application\Api\Reporting\Custom\Gersdorf;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Custom\Gersdorf\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Gersdorf
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
            $PersonList = Person::useService()->createClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Gersdorf Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadSignList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createSignList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createSignListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Gersdorf Unterschriftenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadElectiveClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createElectiveClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createElectiveClassListExcel($PersonList, $tblPersonList, $DivisionId);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Gersdorf Klassenliste Fremdsprachen ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadClassPhoneList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createClassPhoneList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createClassPhoneListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Gersdorf Erweiterte Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadTeacherList($DivisionId = null)
    {

        $PersonList = Person::useService()->createTeacherList();
        if ($PersonList) {
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonList) {
                $fileLocation = Person::useService()->createTeacherListExcel($PersonList, $tblPersonList);
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Lehrerliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }
        return false;
    }
}
