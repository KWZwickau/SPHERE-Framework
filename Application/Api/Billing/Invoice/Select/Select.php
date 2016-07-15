<?php
namespace SPHERE\Application\Api\Billing\Invoice\Select;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Billing\Bookkeeping\Export\Export;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Education\Lesson\Division\Division;

/**
 * Class CheckList
 *
 * @package SPHERE\Application\Api\Billing\Invoice\Select
 */
class Select
{
    /**
     * @param      $DateFrom
     * @param null $DateTo
     * @param null $Division
     * @param null $Item
     *
     * @return bool|string
     *
     */
    public function downloadInvoiceSelect($DateFrom, $DateTo = null, $Division = null, $Item = null)
    {

        $tblDivision = ( $Division == null ? null : Division::useService()->getDivisionById($Division) );
        $tblItem = ( $Item == null ? null : Item::useService()->getItemById($Item) );

        $tblInvoiceList = Export::useService()->getInvoiceListByDate($DateFrom, $DateTo);

        $TableHeader = array('Name'          => 'Name',
                             'StudentNumber' => 'Schülernummer',
                             'Date'          => 'Fälligkeitsdatum',);
        $TableContent = Export::useService()->createInvoiceListByInvoiceListAndDivision($TableHeader, $tblDivision, $tblItem, $tblInvoiceList);
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
