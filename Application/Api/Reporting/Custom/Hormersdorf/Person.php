<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.01.2016
 * Time: 16:34
 */

namespace SPHERE\Application\Api\Reporting\Custom\Hormersdorf;

use MOC\V\Core\FileSystem\FileSystem;
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
}