<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
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
//        new Backward();

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
                $Content['Option'] = new Standard('', '/Billing/Bookkeeping/Balance/View', new Listing(),
                    array('Id' => $tblInvoice->getId()));

                array_push($TableContent, $Content);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new ListingTable().' Übersicht', 'der offenen Posten'),
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

    public function frontendBalanceView($Id = null)
    {

        $Stage = new Stage('Rechung', '');
        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Balance', new ChevronLeft()));
        }

        $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);

//        $Quantity = array();
        $TableContent = array();
        $SumPrice = 0;
        if ($tblItemList) {
//            // Doppelte Artikel zusammenfassen
//            foreach($tblItemList as $tblItem){
//                $SumPrice += $tblItem->getValue() * $tblItem->getQuantity();
//                if(empty($Quantity[$tblItem->getServiceTblItem()->getId().$tblItem->getValue()])){
//                    $Quantity[$tblItem->getServiceTblItem()->getId().$tblItem->getValue()] = $tblItem->getQuantity();
//                } else {
//                    $Quantity[$tblItem->getServiceTblItem()->getId().$tblItem->getValue()] += $tblItem->getQuantity();
//                }
//            }
//            $separator = array();
//            array_walk($tblItemList, function( TblItem $tblItem ) use (&$TableContent, &$ItemPanel, $Quantity, &$separator){
//                $Item['Name'] = $tblItem->getName();
//                $Item['Quantity'] = $Quantity[$tblItem->getServiceTblItem()->getId().$tblItem->getValue()];
//                $Item['SinglePrice'] = $tblItem->getPriceString();
//                $Item['SumPrice'] = number_format($tblItem->getValue() * $Quantity[$tblItem->getServiceTblItem()->getId().$tblItem->getValue()], 2).' €';
//
//                // Doppelte Artikel nur einmal aufnehmen
//                if(empty($separator[$tblItem->getServiceTblItem()->getId().$tblItem->getValue()])){
//                    array_push($TableContent, $Item);
//                }
//                $separator[$tblItem->getServiceTblItem()->getId().$tblItem->getValue()] = 'Vorhanden';
//            });
//        }
//
//        // Sortierung nach Name && Preis aufsteigend
//        foreach ($TableContent as $key => $row) {
//            $Name[$key] = strtoupper($row['Name']);
//            $SummaryPrice[$key] = ($row['SinglePrice']);
//        }
//        array_multisort($Name, SORT_ASC, $SummaryPrice, SORT_ASC,  $TableContent);


            array_walk($tblItemList, function (TblItem $tblItem) use (&$TableContent, &$SumPrice) {
                $Item['Name'] = $tblItem->getName();
                $Item['Quantity'] = $tblItem->getQuantity();
                $Item['SinglePrice'] = $tblItem->getPriceString();
                $Item['SumPrice'] = $tblItem->getSummaryPrice();

                $SumPrice += $tblItem->getValue() * $tblItem->getQuantity();


                array_push($TableContent, $Item);
            });
        }

        // Sortierung nach Name && Preis aufsteigend
        foreach ($TableContent as $key => $row) {
            $Name[$key] = strtoupper($row['Name']);
            $SummaryPrice[$key] = ( $row['SumPrice'] );
        }
        array_multisort($Name, SORT_ASC, $SummaryPrice, SORT_ASC, $TableContent);

        $Stage->setContent(
            $this->layoutInvoice($tblInvoice, $SumPrice)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new ListingTable().' Übersicht der Artikel'),
                            new TableData($TableContent, null, array(), false)
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    public function layoutInvoice(TblInvoice $tblInvoice, $SummaryPrice)
    {

        $tblPersonFrom = $tblInvoice->getServiceTblPerson();
        return new Title('Eckdaten der Rechnung')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Panel('Rechnungsnummer:', $tblInvoice->getInvoiceNumber(), Panel::PANEL_TYPE_SUCCESS),
                    ), 4),
                    new LayoutColumn(array(
                        new Panel('Gesamtbetrag:', $SummaryPrice.' €', Panel::PANEL_TYPE_SUCCESS,
                            new PullRight(new Standard('', '/Billing/Bookkeeping/Balance/Paid', new Check(),
                                    array('Id' => $tblInvoice->getId()), 'Bezahlen')
                                .new Standard('', '/Billing/Bookkeeping/Balance/Reversal', new Disable(),
                                    array('Id' => $tblInvoice->getId()), 'Stornieren'))
                        ),
                    ), 4),
                    new LayoutColumn(array(
                        new Panel('Rechnungsempfänger:', $tblPersonFrom->getFullName(), Panel::PANEL_TYPE_SUCCESS),
                    ), 4),
                ))
            )
        );
    }

    public function frontendBalancePaid($Id = null)
    {
        $Stage = new Stage('Bezahlen der Rechnung', 'Manuelles');

        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsPaid()) {
            $Stage->setContent(new Warning('Rechnung bereits bezahlt'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsReversal()) {
            $Stage->setContent(new Warning('Rechnung bereits storniert'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( Invoice::useService()->changeInvoiceIsPaid($tblInvoice, true) ?
                                new Success('Rechnung ist bezahlt.').new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS) :
                                new Warning('Rechnung konnte nicht bezahlt werden'
                                    .new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS))
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    public function frontendBalanceReversal($Id = null)
    {
        $Stage = new Stage('Stornieren der Rechnung');

        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsPaid()) {
            $Stage->setContent(new Warning('Rechnung bereits bezahlt'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsReversal()) {
            $Stage->setContent(new Warning('Rechnung bereits storniert'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( Invoice::useService()->changeInvoiceIsReversal($tblInvoice, true) ?
                                new Success('Rechnung ist storniert.').new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS) :
                                new Warning('Rechnung konnte nicht storniert werden'
                                    .new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS))
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }
}
