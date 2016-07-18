<?php
namespace SPHERE\Application\Api\Billing\Invoice;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Billing\Bookkeeping\Export\Export;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Billing\Invoice
 */
class Invoice implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/InvoiceAll/Download', __NAMESPACE__.'\Invoice::downloadInvoiceAll'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Select/Download', __NAMESPACE__.'\Select\Select::downloadInvoiceSelect'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Datev/Download', __NAMESPACE__.'\Datev\Datev::downloadInvoiceAllDatev'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Sfirm/Download', __NAMESPACE__.'\Sfirm\Sfirm::downloadInvoiceAllSfirm'
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
     * @return bool|string
     */
    public function downloadInvoiceAll()
    {

        $TableHeader = array('Debtor'        => 'Debitor',
                             'Name'          => 'Name',
                             'StudentNumber' => 'Schülernummer',
                             'Date'          => 'Fälligkeitsdatum',);
        $TableContent = Export::useService()->createInvoiceList($TableHeader);
        if ($TableContent) {
            $fileLocation = Export::useService()->createInvoiceListExcel($TableContent, $TableHeader);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Offene Posten ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

}
