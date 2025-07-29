<?php
namespace SPHERE\Application\Api\Reporting\Custom\Hoga;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Reporting\Custom\Hoga\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Hoga
 */
class Common
{
    /**
     * @return string|bool
     */
    public function downloadCleverReach()
    {

        if(($TableContent = Person::useService()->createCleverReachList()) && !empty($TableContent)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            $fileLocation = Person::useService()->createCleverReachExcel($fileLocation, $TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "CleverReach_".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadCSVCleverReach()
    {

        if(($TableContent = Person::useService()->createCleverReachList()) && !empty($TableContent)) {
            $fileLocation = Storage::createFilePointer('csv');
            $fileLocation = Person::useService()->createCleverReachExcel($fileLocation, $TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "CleverReach ".date("Y-m-d").".csv")->__toString();
        }
        return false;
    }
}
