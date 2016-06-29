<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBalance()
    {

        $Stage = new Stage();
        $Stage->setTitle('Posten');
        $Stage->setDescription('Offen');
        new Backward();

        $tblInvoiceList = Invoice::useService()->getInvoiceByIsPaid();
        $TableContent = array();
        if ($tblInvoiceList) {

            array_walk($tblInvoiceList, function (TblInvoice $tblInvoice) use (&$TableContent) {
                $Content['FullName'] = $tblInvoice->getServiceTblPerson()->getFullName();
                $Content['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $Content['DebtorNumber'] = '';
                $Content['Reference'] = '';
                $tblDebtor = $tblInvoice->getTblDebtor();
                if ($tblDebtor) {
                    $Content['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                    $Content['Reference'] = $tblDebtor->getBankReference();
                }

                $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);
                $Price = 0.00;
                $ItemCount = array();
                if (!empty( $tblItemList )) {
                    foreach ($tblItemList as &$tblItem) {
                        $Price += $tblItem->getSummaryPriceInt();
                        if (!empty( $ItemCount[$tblItem->getName()] )) {
                            $ItemCount[$tblItem->getName()] += $tblItem->getQuantity();
                        } else {
                            $ItemCount[$tblItem->getName()] = $tblItem->getQuantity();
                        }
                        $tblItem = $tblItem->getName();
                    }
                    $tblItemList = array_unique($tblItemList);
                    foreach ($tblItemList as &$Item) {
                        foreach ($ItemCount as $ItemName => $value) {
                            if ($Item === $ItemName) {
                                $Item = $value.'x '.$Item;
                            }
                        }
                    }

                    $ItemString = implode(', ', $tblItemList);
                    $Content['ItemList'] = $ItemString;
                } else {
                    $Content['ItemList'] = '';
                }
                $Content['Price'] = Invoice::useService()->getPriceString($Price);
                $Content['Option'] = new Standard('', '', new Listing(), null, 'Auswahl');

                array_push($TableContent, $Content);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new Listing().' Übersicht', 'der offenen Posten'),
                            ( empty( $TableContent ) ? new Warning('Keine offenen Rechnungen vorhanden') :
                                new TableData($TableContent, null,
                                    array('InvoiceNumber' => 'Rechnungsnummer',
                                          'DebtorNumber'  => 'Debitorennummer',
                                          'FullName'      => 'Debitor',
                                          'Reference'     => 'Referenz',
                                          'ItemList'      => 'Artikel',
                                          'Price'         => 'Gesamtpreis',
                                          'Option'        => ''))
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }
}
