<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.01.2016
 * Time: 16:34
 */

namespace SPHERE\Application\Api\Reporting\Custom\Hormersdorf;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Reporting\Custom\Hormersdorf\Person\Person as HormersdorfPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Hormersdorf
 */
class Person
{

    /**
     * @return bool|string
     */
    public function downloadStaffList()
    {

        $staffList = HormersdorfPerson::useService()->createStaffList();

        if ($staffList) {
            $fileLocation = HormersdorfPerson::useService()->createStaffListExcel($staffList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Mitarbeiterliste (Geburtstage) ".date("Y-m-d H:i:s").".xls")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentList = HormersdorfPerson::useService()->createClassList($tblDivision);
            if ($studentList) {
                $fileLocation = HormersdorfPerson::useService()->createClassListExcel($studentList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Klassenliste ".$tblDivision->getDisplayName()
                    ." ".date("Y-m-d H:i:s").".xls")->__toString();
            }
        }

        return false;
    }
}