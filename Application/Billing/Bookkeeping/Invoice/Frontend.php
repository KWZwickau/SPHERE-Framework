<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
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
        $Stage->setMessage('Zeigt alle vorhandenen Rechnungen an');
//        $Stage->addButton(new Standard('Aufträge', '/Billing/Bookkeeping/Invoice/Control', new Ok(), null, 'Freigeben von Rechnungen'));
        new Backward();

        $tblInvoiceAll = Invoice::useService()->getInvoiceAll();
        if ($tblInvoiceAll) {
            foreach ($tblInvoiceAll as &$tblInvoice) {
                $tblInvoice->FullName = $tblInvoice->getServiceTblPerson()->getFullName();

                $tblDebtor = $tblInvoice->getTblDebtor();
                $tblReference = $tblDebtor->getServiceTblBankReference();
                $tblInvoice->Reference = $tblReference->getReference();

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
                    $tblInvoice->ItemList = $ItemString;
                } else {
                    $tblInvoice->ItemList = '';
                }
                $tblInvoice->Price = Invoice::useService()->getPriceString($Price);
                $tblInvoice->Option = new Standard('', '', new Listing(), null, 'Auswahl');

            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblInvoiceAll, null,
                                array('InvoiceNumber' => 'Rechnungsnummer',
                                      'DebtorNumber'  => 'Debitorennummer',
                                      'FullName'      => 'Debitor',
                                      'Reference'     => 'Referenz',
                                      'ItemList'      => 'Artikel',
                                      'Price'         => 'Gesamtpreis',
                                      'Option'        => ''))
                        )
                    )
                )
            )
        );

        return $Stage;
    }
}
