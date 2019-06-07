<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Billing\Inventory
 */
class Import implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DownloadTemplateInvoice',
            __NAMESPACE__.'\Import::downloadTemplateInvoice'
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

    public function downloadTemplateInvoice()
    {

        $file = "Common/Style/Resource/Template/Fakturierung Import.xlsx";
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=Fakturierung Import.xlsx");
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

}