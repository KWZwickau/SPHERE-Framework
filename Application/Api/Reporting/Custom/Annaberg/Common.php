<?php

namespace SPHERE\Application\Api\Reporting\Custom\Annaberg;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Reporting\Custom\Annaberg\Person\Person;

class Common
{
    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadPrintClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createPrintClassList($tblDivision);
            if ($PersonList) {
                $fileLocation = Person::useService()->createPrintClassListExcel($PersonList, $tblDivision);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Klassenliste ".$tblDivision->getDisplayName()
                    ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }
}