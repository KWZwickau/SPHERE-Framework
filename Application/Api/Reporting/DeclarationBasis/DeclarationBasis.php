<?php

namespace SPHERE\Application\Api\Reporting\DeclarationBasis;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
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

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @return string
     */
    public function downloadDivisionReport()
    {

        $fileLocation = \SPHERE\Application\Reporting\DeclarationBasis\DeclarationBasis::useService()->createDivisionReportExcel();
        return FileSystem::getDownload($fileLocation->getRealPath(),
            "Stichtagsmeldung SBA"." ".date("Y-m-d H:i:s").".xlsx")->__toString();
    }
}