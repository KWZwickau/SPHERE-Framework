<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 08:21
 */

namespace SPHERE\Application\Api\Reporting\Standard\Company;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Corporation\Group\Group;

class Company
{
    /**
     * @param null $GroupId
     * @return bool|string
     */
    public function downloadGroupList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $groupList = \SPHERE\Application\Reporting\Standard\Company\Company::useService()->createGroupList($tblGroup);
            if ($groupList) {
                $fileLocation = \SPHERE\Application\Reporting\Standard\Company\Company::useService()->createGroupListExcel($groupList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Firmengruppenliste " . $tblGroup->getName()
                    . " " . date("Y-m-d H:i:s") . ".xls")->__toString();
            }
        }

        return false;
    }
}