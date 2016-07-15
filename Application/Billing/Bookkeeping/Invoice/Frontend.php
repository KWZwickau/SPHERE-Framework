<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
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
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendInvoiceList()
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnungen');
        $Stage->setDescription('Übersicht');
//        $Stage->addButton(new Standard('Aufträge', '/Billing/Bookkeeping/Invoice/Control', new Ok(), null, 'Freigeben von Rechnungen'));
//        new Backward();

        $tblInvoiceAll = Invoice::useService()->getInvoiceAll();
        $TableContent = array();
        if ($tblInvoiceAll) {

            array_walk($tblInvoiceAll, function (TblInvoice $tblInvoice) use (&$TableContent) {
                $Content['FullName'] = $tblInvoice->getServiceTblPerson()->getFullName();
                $Content['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $Content['DebtorNumber'] = '';
                $Content['Reference'] = '';

                $tblDebtorList = Invoice::useService()->getDebtorAllByInvoice($tblInvoice);
                $DebtorNumberArray = array();
                $DebtorReferenceArray = array();
                if ($tblDebtorList) {
                    foreach ($tblDebtorList as $tblDebtor) {
                        $DebtorNumberArray[] = $tblDebtor->getDebtorNumber();
                        $DebtorReferenceArray[] = $tblDebtor->getBankReference();
                    }
                    $DebtorNumberArray = array_filter(array_unique($DebtorNumberArray));
                    $DebtorReferenceArray = array_filter(array_unique($DebtorReferenceArray));
                    $DebtorNumberString = implode(', ', $DebtorNumberArray);
                    $DebtorReferenceString = implode(', ', $DebtorReferenceArray);
                    $Content['DebtorNumber'] = $DebtorNumberString;
                    $Content['Reference'] = $DebtorReferenceString;
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
                if ($tblInvoice->getIsPaid()) {
                    $Content['Paid'] = new Success(new Check());
                } else {
                    $Content['Paid'] = new Danger(new Unchecked());
                }
                if ($tblInvoice->getIsReversal()) {
                    $Content['Reversal'] = new Danger(new Check());
                } else {
                    $Content['Reversal'] = '';
                }
                $Content['Option'] = new Standard('', '', new Listing(), null, 'Auswahl');

                array_push($TableContent, $Content);
            });
        }


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new ListingTable().' Übersicht', 'aller vorhandenen Rechnungen'),
                            ( empty( $TableContent ) ? new Warning('Keine Rechnungen vorhanden') :
                                new TableData($TableContent, null,
                                    array('InvoiceNumber' => 'Rechnungsnummer',
                                          'DebtorNumber'  => 'Debitorennummer',
                                          'FullName'      => 'Debitor',
                                          'Reference'     => 'Mandatsreferenz(en)',
                                          'ItemList'      => 'Artikel',
                                          'Price'         => 'Gesamtpreis',
                                          'Paid'          => 'Bezahlt',
                                          'Reversal'      => 'Storniert',
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
