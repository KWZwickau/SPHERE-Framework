<?php
namespace SPHERE\Application\Api\Reporting\Custom\Muldental;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Reporting\Custom\Muldental\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Muldental\Coswig
 */
class Common
{

    /**
     * @param null|string $level
     *
     * @return bool|string
     */
    public function downloadClassList($level = null)
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
                "Muldental Stufenliste ".$level." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
