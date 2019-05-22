<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 22.05.2019
 * Time: 10:50
 */

namespace SPHERE\Application\Api\Billing\Invoice;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use MOC\V\Core\FileSystem\FileSystem;

/**
 * Class InvoiceDownload
 *
 * @package SPHERE\Application\Api\Billing\Invoice
 */
class InvoiceDownload implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Causer/Download', __NAMESPACE__.'\InvoiceDownload::downloadInvoiceCauserList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Debtor/Download', __NAMESPACE__.'\InvoiceDownload::downloadInvoiceDebtorList'
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
     * @param $Year
     * @param $Month
     * @param string $BasketName
     * @param string $ItemName
     *
     * @return bool|string
     */
    public function downloadInvoiceCauserList(
        $Year,
        $Month,
        $BasketName = '',
        $ItemName = ''
    ) {
        if(($fileLocation = Invoice::useService()->createInvoiceCauserListExcel($Year, $Month, $BasketName, $ItemName))){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Rechnungsliste-Beitragsverursacher-' . $Year . '-' . $Month
                . ($BasketName == '' ? '' : '-' . $BasketName)
                . ($ItemName == '' ? '' : '-' . $ItemName)
                . '.xlsx')->__toString();
        }
        return false;
    }

    /**
     * @param $Year
     * @param $Month
     * @param string $BasketName
     *
     * @return bool|string
     */
    public function downloadInvoiceDebtorList(
        $Year,
        $Month,
        $BasketName = ''
    ) {
        if(($fileLocation = Invoice::useService()->createInvoiceDebtorListExcel($Year, $Month, $BasketName))){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Rechnungsliste-Beitragszahler-' . $Year . '-' . $Month
                . ($BasketName == '' ? '' : '-' . $BasketName)
                . '.xlsx')->__toString();
        }
        return false;
    }
}