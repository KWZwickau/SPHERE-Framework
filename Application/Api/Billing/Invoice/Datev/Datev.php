<?php
namespace SPHERE\Application\Api\Billing\Invoice\Datev;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Billing\Bookkeeping\Export\Export;

/**
 * Class Datev
 *
 * @package SPHERE\Application\Api\Billing\Invoice\Datev
 */
class Datev
{

    /**
     * @return bool|string
     */
    public function downloadInvoiceAllDatev()
    {

        $TableHeader = array('StudentNumber' => 'Schülernummer',
                             'Item'          => 'Artikel',
                             'ItemPrice'     => 'Summe',
                             'Reference'     => 'Mandat',
                             'BillDate'      => 'Belegdatum',
                             'Date'          => 'Fälligkeitsdatum',
                             'BookingText'   => 'Buchungstext');
        $TableContent = Export::useService()->createInvoiceListDatev($TableHeader);
        if ($TableContent) {
            $fileLocation = Export::useService()->createInvoiceListExcel($TableContent, $TableHeader);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Datev ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }
}
