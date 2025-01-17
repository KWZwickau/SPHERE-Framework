<?php
namespace SPHERE\Application\Api\Education\Certificate;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Certificate extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/Preview', __NAMESPACE__ . '\Generator\Creator::previewPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/PreviewLeave', __NAMESPACE__ . '\Generator\Creator::previewLeavePdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/Download', __NAMESPACE__ . '\Generator\Creator::downloadPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/History/DownloadZip', __NAMESPACE__ . '\Generator\Creator::downloadHistoryZip'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/PreviewMultiPdf', __NAMESPACE__ . '\Generator\Creator::previewMultiPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/PreviewMultiLeavePdf', __NAMESPACE__ . '\Generator\Creator::previewMultiLeavePdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/DownLoadMultiPdf', __NAMESPACE__ . '\Generator\Creator::downloadMultiPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/DownLoadMultiLeavePdf', __NAMESPACE__ . '\Generator\Creator::downloadMultiLeavePdf'
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
}
