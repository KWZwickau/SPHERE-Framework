<?php

namespace SPHERE\Application\Api\Reporting\DeclarationBasis;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

/**
 * Class DeclarationBasis
 * @package SPHERE\Application\Api\Reporting\DeclarationBasis
 */
class DeclarationBasis implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadDivisionReport'
        ));
    }

    public static function useService()
    {
        // Implement useService() method.
    }

    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param null $YearId
     *
     * @return string
     */
    public function downloadDivisionReport($YearId = null)
    {

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $fileLocation = \SPHERE\Application\Reporting\DeclarationBasis\DeclarationBasis::useService()->createDivisionReportExcel($tblYear);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Stichtagsmeldung SBA" . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
        }

        return 'Schuljahr nicht gefunden!';
    }
}