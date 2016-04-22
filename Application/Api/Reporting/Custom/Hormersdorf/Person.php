<?php
namespace SPHERE\Application\Api\Reporting\Custom\Hormersdorf;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Reporting\Custom\Hormersdorf\Person\Person as HormersdorfPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Hormersdorf
 */
class Person
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
            $PersonList = HormersdorfPerson::useService()->createClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = HormersdorfPerson::useService()->createClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xls")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function downloadStaffList()
    {

        $PersonList = HormersdorfPerson::useService()->createStaffList();

        if ($PersonList) {
            $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));
            if ($tblPersonList) {
                $fileLocation = HormersdorfPerson::useService()->createStaffListExcel($PersonList, $tblPersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Mitarbeiterliste (Geburtstage) ".date("Y-m-d H:i:s").".xls")->__toString();
            }
        }

        return false;
    }
}
