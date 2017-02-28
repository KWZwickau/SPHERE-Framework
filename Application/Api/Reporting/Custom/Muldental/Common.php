<?php
namespace SPHERE\Application\Api\Reporting\Custom\Muldental;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Reporting\Custom\Muldental\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Muldental\Coswig
 */
class Common
{

    /**
     * @param null|string $LevelId
     * @param null        $YearId
     *
     * @return bool|string
     */
    public function downloadClassList($LevelId = null, $YearId = null)
    {

        // list of division by Year and Level
        $tblLevel = Division::useService()->getLevelById($LevelId);
        $tblYear = Term::useService()->getYearById($YearId);
        $tblDivisionList = array();
        if ($tblLevel && $tblYear) {
            $tblDivisionList = Division::useService()->getDivisionAllByLevelNameAndYear($tblLevel, $tblYear);
        }

        // list of persons(students) by listed divisions
        $tblPersonList = false;
        if (!empty($tblDivisionList)) {
            $tblPersonList = Division::useService()->getPersonAllByDivisionList($tblDivisionList);
        }

        $PersonList = Person::useService()->createClassList($tblDivisionList);
        if (!empty($PersonList) && $tblPersonList) {
            $fileLocation = Person::useService()->createClassListExcel($PersonList, $tblPersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Muldental Stufenliste ".$tblLevel->getName()
                ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
        }
        return false;
    }
}
