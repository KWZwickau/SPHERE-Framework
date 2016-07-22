<?php
namespace SPHERE\Application\Api\Billing\Invoice;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
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
            __NAMESPACE__.'/Download', __NAMESPACE__.'\Invoice::downloadPrepareInvoice'
        ));

        // ToDO Datev + SFirm
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Datev/Download', __NAMESPACE__.'\Datev\Datev::downloadInvoiceAllDatev'
//        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Sfirm/Download', __NAMESPACE__.'\Sfirm\Sfirm::downloadInvoiceAllSfirm'
//        ));
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
     * @param $Filter
     *
     * @return bool|string
     */
    public function downloadPrepareInvoice(
        $Filter
    ) {

        $Filter = json_decode($Filter);

        if (!empty( $Filter->Error )) {
            return new Warning('Übergabe nicht auswertbar');
        }
        $Filter = current($Filter->Data);

        $Status = ( $Filter->Status == 1 ? 'Offene Rechnungen' : ( $Filter->Status == 2 ? 'Bezahlte Rechnungen' : 'Stornierte Rechnungen' ) );


        $tblInvoiceList = \SPHERE\Application\Transfer\Export\Invoice\Invoice::useService()->getInvoiceListByDate($Filter->DateFrom, $Filter->DateTo, $Filter->Status);

        $TableHeader = array();
        $TableHeader['Payer'] = 'Bezahler';
        if ($Filter->PersonFrom != 0) {
            $TableHeader['PersonFrom'] = 'Leistungsbezieher';
        }
        if ($Filter->StudentNumber != 0) {
            $TableHeader['StudentNumber'] = 'Schüler-Nr.';
        }
        $TableHeader['Date'] = 'Fälligkeitsdatum';
        if ($Filter->IBAN != 0) {
            $TableHeader['IBAN'] = 'IBAN';
        }
        if ($Filter->BIC != 0) {
            $TableHeader['BIC'] = 'BIC';
        }
        $TableHeader['BillDate'] = 'Rechnungsdatum';
        $TableHeader['Reference'] = 'Mandats-Ref.';
        if ($Filter->BankName != 0) {
            $TableHeader['Bank'] = 'Name der Bank';
        }
        $TableHeader['Client'] = 'Mandant';
        $TableHeader['DebtorNumber'] = 'Debitoren-Nr.';
        if ($Filter->Owner != 0) {
            $TableHeader['Owner'] = 'Besitzer';
        }
        $TableHeader['InvoiceNumber'] = 'Buchungstext';
        $TableHeader['Item'] = 'Artikel';
        $TableHeader['ItemPrice'] = 'Einzelpreis';
        $TableHeader['Quantity'] = 'Anzahl';
        $TableHeader['Sum'] = 'Gesamtpreis';
        $TableContent = \SPHERE\Application\Transfer\Export\Invoice\Invoice::useService()->createInvoiceListByPrepare(
//            $TableHeader,
            $tblInvoiceList,
            $Filter->PersonFrom,
            $Filter->StudentNumber,
            $Filter->IBAN,
            $Filter->BIC,
            $Filter->BankName,
            $Filter->Owner);
        if ($TableContent) {
            $fileLocation = \SPHERE\Application\Transfer\Export\Invoice\Invoice::useService()->createInvoiceListExcel($TableContent, $TableHeader);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    $Status." ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

}
