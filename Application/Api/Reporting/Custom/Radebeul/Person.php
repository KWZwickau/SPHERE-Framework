<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.09.2016
 * Time: 16:28
 */

namespace SPHERE\Application\Api\Reporting\Custom\Radebeul;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Reporting\Custom\Radebeul\Person\Person as RadebeulPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Radebeul
 */
class Person
{

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadParentTeacherConferenceList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = RadebeulPerson::useService()->createParentTeacherConferenceList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = RadebeulPerson::useService()->createParentTeacherConferenceListExcel($PersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Radebeul Anwesenheitsliste für Elternabende ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }
}