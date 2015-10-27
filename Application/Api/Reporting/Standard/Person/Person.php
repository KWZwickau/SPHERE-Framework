<?php
namespace SPHERE\Application\Api\Reporting\Standard;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Reporting\Standard\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Standard
 */
class PersonList
{

    /**
     * @return \MOC\V\Core\FileSystem\Component\IBridgeInterface
     */
    public function downloadClassList()
    {

        $fileLocation = "Klassenliste.xls";
        $studentList = Person::useService()->createClassList();
        Person::useService()->createClassListExcel($studentList, $fileLocation);

        return FileSystem::getDownload($fileLocation);
    }
}