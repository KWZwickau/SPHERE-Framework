<?php
namespace SPHERE\Application\Api\Reporting\Custom\BadDueben;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Reporting\Custom\BadDueben\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\BadDueben
 */
class Common
{

    /**
     * @param string $level
     *
     * @return bool|string
     */
    public function downloadClassList($level)
    {

        // Sammeln Personenliste aus level
        $tblPersonList = array();
        if($level){
            if (!empty($DivisionList = Person::useFrontend()->getDivisionListByLevel($level))) {
                if(isset($DivisionList[$level]['Person'])){
                    $tblPersonList = $DivisionList[$level]['Person'];
                }
            }
        }
        if(!empty($tblPersonList)
            && !empty($TableContent = Person::useService()->createClassList($tblPersonList))){
            $fileLocation = Person::useService()->createClassListExcel($TableContent, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Bad Düben Stufenliste ".$level." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
