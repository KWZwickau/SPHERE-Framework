<?php
namespace SPHERE\Application\Api\Document\Custom\Gersdorf;

use SPHERE\Application\Api\Document\Creator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Gersdorf extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Emergency/Create', __CLASS__.'::createEmergencyPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MetaDataComparison/Create', __CLASS__.'::createMetaDataComparisonPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MetaDataComparison/Division/CreateMulti', __CLASS__.'::createMetaDataComparisonByDivisionPdf'
        ));
    }

    /**
     * @param null  $PersonId
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createEmergencyPdf($PersonId = null, $Data)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\Emergency', Creator::PAPERORIENTATION_PORTRAIT, $Data);
    }

    /**
     * @param null  $PersonId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createMetaDataComparisonPdf($PersonId = null, $Data = array())
    {

        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\MetaDataComparison', Creator::PAPERORIENTATION_PORTRAIT, $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createMetaDataComparisonByDivisionPdf($DivisionCourseId, bool $Redirect = true)
    {
        return Creator::createMultiDataComparisonPdf($DivisionCourseId, $Redirect);
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }
}