<?php
namespace SPHERE\Application\Api\Billing\Invoice\Select;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Billing\Bookkeeping\Export\Export;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;

/**
 * Class Select
 *
 * @package SPHERE\Application\Api\Billing\Invoice\Select
 */
class Select
{
    /**
     * @param      $DateFrom
     * @param null $DateTo
     * @param null $Division
     * @param null $Group
     * @param null $Item
     *
     * @return bool|string
     */
    public function downloadInvoiceSelect($DateFrom, $DateTo = null, $Division = null, $Group = null, $Item = null)
    {

        $tblDivision = ( $Division == null ? null : Division::useService()->getDivisionById($Division) );
        $tblGroup = ( $Group == null ? null : Group::useService()->getGroupById($Group) );
        $tblItem = ( $Item == null ? null : Item::useService()->getItemById($Item) );

        $tblInvoiceList = Export::useService()->getInvoiceListByDate($DateFrom, $DateTo);

        $TableHeader = array('Debtor'        => 'Debitor',
                             'Name'          => 'Name',
                             'StudentNumber' => 'Schülernummer',
                             'Date'          => 'Fälligkeitsdatum',);
        $TableContent = Export::useService()->createInvoiceListByInvoiceListAndDivision($TableHeader, $tblDivision, $tblGroup, $tblItem, $tblInvoiceList);
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
