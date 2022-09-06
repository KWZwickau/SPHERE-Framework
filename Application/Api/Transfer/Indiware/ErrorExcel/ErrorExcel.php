<?php
namespace SPHERE\Application\Api\Transfer\Indiware\ErrorExcel;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Transfer\Indiware\Import\Import as ImportIndiware;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareError;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

/**
 * Class ErrorExcel
 *
 * @package SPHERE\Application\Api\Transfer\Indiware\ErrorExcel
 */
class ErrorExcel implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/LectureShip/Download', __NAMESPACE__.'\ErrorExcel::downloadLectureShipError'
        ));
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param string $Type
     * @param string $StringCompareDescription
     *
     * @return Stage|string
     */
    public function downloadLectureShipError($Type = TblIndiwareError::TYPE_LECTURE_SHIP, $StringCompareDescription = 'Klasse_Fach_Lehrer(_Fachgruppe)')
    {

        $fileLocation = ImportIndiware::useService()->getIndiwareErrorExcel($Type, $StringCompareDescription);
        if($fileLocation){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Import_Fehler_Lehraufträge ".date("Y-m-d").".xlsx")->__toString();
        }
        $Stage = new Stage('Download der Fehlermeldungen als Excel');
        $Stage->setContent(new Danger('Keine Fehler für den Download verfügbar'));
        return $Stage;
    }
}