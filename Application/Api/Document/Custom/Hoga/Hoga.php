<?php

namespace SPHERE\Application\Api\Document\Custom\Hoga;

use SPHERE\Application\Api\Document\Creator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Hoga
 *
 * @package SPHERE\Application\Api\Document\Custom\Hoga
 */
class Hoga extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/EnrollmentDocument/Create', __CLASS__ . '::createEnrollmentDocumentPdf'
        ));
    }

    /**
     * @return IServiceInterface|void
     */
    public static function useService()
    {
    }

    /**
     * @return IFrontendInterface|void
     */
    public static function useFrontend()
    {
    }

    /**
     * @param null|int  $PersonId
     * @param array $Data
     *
     * @return Stage|string
     */
    public static function createEnrollmentDocumentPdf($PersonId = null, $Data = array())
    {
        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\EnrollmentDocument', Creator::PAPERORIENTATION_PORTRAIT, $Data);
    }
}