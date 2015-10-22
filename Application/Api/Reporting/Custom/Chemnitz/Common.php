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
     * @return \MOC\V\Core\FileSystem\Component\IBridgeInterface
     */
    public function downloadClassList()
    {

        $fileLocation = "Chemnitz Klassenliste.xls";
        $studentList = Person::useService()->createClassList();
        Person::useService()->createClassListExcel($studentList, $fileLocation);

        return FileSystem::getDownload($fileLocation);
    }
}
