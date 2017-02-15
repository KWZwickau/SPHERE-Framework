<?php
namespace SPHERE\Application\Api\Reporting\Custom\Muldental;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Reporting\Custom\Muldental\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Muldental\Coswig
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
                        "Muldental Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }
        return false;
    }
}
