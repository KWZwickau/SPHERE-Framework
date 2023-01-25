<?php
namespace SPHERE\Application\Api\Reporting\CustomEKBO\BerlinZentrum;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\BerlinZentrum
 */
class Common
{

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadSuSList()
    {

        if(($TableContent = Person::useService()->createSuSList())){
            $fileLocation = Person::useService()->createSuSListExcel($TableContent);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "SuS Gesamtliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
        }
        return false;
    }
}
