<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Api\Billing\Invoice\ApiInvoiceIsPaid;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param array $Invoice
     *
     * @return Stage
     */
    public function frontendInvoiceView($Invoice = array())
    {
        if(empty($Invoice)){
            $Now = new \DateTime();
            $Invoice['Year'] = $Now->format('Y');
            $Invoice['Month'] = (int)$Now->format('m');
        }
        if(isset($Invoice['Year'])){
            $_POST['Invoice']['Year'] = $Invoice['Year'];
        }
        if(isset($Invoice['Month'])){
            $_POST['Invoice']['Month'] = $Invoice['Month'];
        }
        if(!isset($Invoice['BasketName'])){
            $Invoice['BasketName'] = '';
        }

        $Stage = new Stage('Rechnungsliste', 'Sicht Beitragszahler');
        $TableContent = Invoice::useService()->getInvoiceDebtorList($Invoice['Year'], $Invoice['Month'], $Invoice['BasketName'], true);

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($this->formInvoiceFilter())
                ),
                empty($TableContent) ? null : new LayoutRow(new LayoutColumn(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen', '/Api/Billing/Invoice/Debtor/Download',
                        new Download(), array(
                            'Year'   => $Invoice['Year'],
                            'Month'   => $Invoice['Month'],
                            'BasketName' => isset($Invoice['BasketName']) ? $Invoice['BasketName'] : ''
                        ))
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null, array(
                            'DebtorPerson'  => 'Beitragszahler',
                            'DebtorNumber'  => 'Debitoren-Nr.',
                            'BasketName'    => 'Name der Abrechnung',
                            'CauserPerson'  => 'Beitragsverursacher',
                            'Time'          => 'Abrechnungszeitraum',
                            'TargetTime'    => 'Fälligkeitsdatum',
                            'InvoiceNumber' => 'Rechnungsnummer',
                            'PaymentType'   => 'Zahlungsart',
                            'DisplaySumPrice'      => 'Gesamtbetrag',
//                            'Option' => '',
                        ), array(
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => array(1, 6)),
                                array('type' => 'de_date', 'targets' => array(5)),
//                                array("orderable" => false, "targets"   => -1),
                            ),
                            'order'      => array(
//                            array(1, 'desc'),
                                array(6, 'desc')
                            ),
                            'responsive' => false,
                        ))
                    )
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @param array $Invoice
     *
     * @return Stage
     */
    public function frontendInvoiceCauserView($Invoice = array())
    {
        if(empty($Invoice)){
            $Now = new \DateTime();
            $Invoice['Year'] = $Now->format('Y');
            $Invoice['Month'] = (int)$Now->format('m');
        }
        if(isset($Invoice['Year'])){
            $_POST['Invoice']['Year'] = $Invoice['Year'];
        }
        if(isset($Invoice['Month'])){
            $_POST['Invoice']['Month'] = $Invoice['Month'];
        }
        if(!isset($Invoice['BasketName'])){
            $Invoice['BasketName'] = '';
        }

        $Stage = new Stage('Rechnungsliste', 'Sicht Beitragsverursacher');
        $TableContent = Invoice::useService()->getInvoiceCauserList(
            $Invoice['Year'],
            $Invoice['Month'],
            isset($Invoice['BasketName']) ? $Invoice['BasketName'] : '',
            isset($Invoice['ItemName']) ? $Invoice['ItemName'] : '',
            true
        );

        $Stage->setContent(
            ApiInvoiceIsPaid::receiverService()
            .new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn($this->formInvoiceFilter(true))
                    ),
                    empty($TableContent) ? null : new LayoutRow(new LayoutColumn(
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen', '/Api/Billing/Invoice/Causer/Download',
                            new Download(), array(
                                'Year'   => $Invoice['Year'],
                                'Month'   => $Invoice['Month'],
                                'BasketName' => isset($Invoice['BasketName']) ? $Invoice['BasketName'] : '',
                                'ItemName' => isset($Invoice['ItemName']) ? $Invoice['ItemName'] : ''
                            ))
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'CauserPerson'  => 'Beitragsverursacher',
                                'Item'          => 'Beitragsarten',
                                'DebtorPerson'  => 'Beitragszahler',
                                'BasketName'    => 'Name der Abrechnung',
                                'Time'          => 'Abrechnungszeitraum',
                                'InvoiceNumber' => 'Rechnungsnummer',
                                'PaymentType'   => 'Zahlungsart',
                                'ItemQuantity'  => 'Menge',
                                'ItemPrice'     => new ToolTip('EP', 'Einzelpreis'),
                                'ItemSumPrice'  => new ToolTip('GP', 'Gesamtpreis'),
                                'IsPaid'        => 'Offene Posten',
//                                'Option' => '',
                            ), array(
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => array(0,1, 2, 3, 4, 5, 6)),
//                                    array('type' => 'de_date', 'targets' => array(2)),
                                    array("orderable" => false, "targets" => -1),
                                ),
                                'order'      => array(
//                            array(1, 'desc'),
                                    array(5, 'desc')
                                ),
                                'responsive' => false,
                            ))
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param bool $HasItemFilter
     *
     * @return Well
     */
    public function formInvoiceFilter($HasItemFilter = false)
    {

        $YearList = Invoice::useService()->getYearList(3, 1);
        $MonthList = Invoice::useService()->getMonthList();

        $BasketNameList = array();
        if(($tblBasketList = Basket::useService()->getBasketAll())){
            foreach($tblBasketList as $tblBasket) {
                $BasketNameList[] = $tblBasket->getName();
            }
            $BasketNameList = array_unique($BasketNameList);
        }

        $width = $HasItemFilter ? 3 : 4;
        $columns[] = new FormColumn(new SelectBox('Invoice[Year]', 'Jahr', $YearList), $width);
        $columns[] = new FormColumn(new SelectBox('Invoice[Month]', 'Monat', $MonthList, null, true, null), $width);
        $columns[] = new FormColumn(new AutoCompleter('Invoice[BasketName]', 'Name der Abrechnung', '', $BasketNameList), $width);
        if ($HasItemFilter) {
            $ItemNameList = array();
            if(($tblItemList = Item::useService()->getItemAll())){
                foreach($tblItemList as $tblItem) {
                    $ItemNameList[] = $tblItem->getName();
                }
                $ItemNameList = array_unique($ItemNameList);
            }

            $columns[] = new FormColumn(new AutoCompleter('Invoice[ItemName]', 'Beitragsart', '', $ItemNameList), $width);
        }

        return new Well(new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(new Title('Rechnungen filtern'))
                ),
                new FormRow($columns),
                new FormRow(array(
//                    new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn('')))), 2),
//                    new FormColumn(new Primary('Filter', new Filter()), 10),
                    new FormColumn(new Primary('Filter', new Filter())),
                ))
            ))
        ));
    }

    public function frontendUnPaid()
    {

        $Stage = new Stage('Offene Posten', 'Übersicht');
        $TableContent = array();
        if($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByIsPaid()){
            array_walk($tblInvoiceItemDebtorList,
                function(TblInvoiceItemDebtor $tblInvoiceItemDebtor) use (&$TableContent){
                    $item['DebtorPerson'] = '';
                    $item['Item'] = $tblInvoiceItemDebtor->getName();
                    $item['ItemQuantity'] = $tblInvoiceItemDebtor->getQuantity();
                    $item['ItemPrice'] = $tblInvoiceItemDebtor->getPriceString();
                    $item['ItemSumPrice'] = $tblInvoiceItemDebtor->getSummaryPrice();
                    $item['InvoiceNumber'] = '';
                    $item['CauserPerson'] = '';
                    $item['Time'] = '';
                    $item['BasketName'] = '';
                    if($tblInvoiceItemDebtor->getDebtorPerson()){
                        $item['DebtorPerson'] = $tblInvoiceItemDebtor->getDebtorPerson();
                    }
                    if($tblInvoice = $tblInvoiceItemDebtor->getTblInvoice()){
                        $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                        $item['CauserPerson'] = $tblInvoice->getLastName().', '.$tblInvoice->getFirstName();
                        $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                        $item['BasketName'] = $tblInvoice->getBasketName();
                    }

                    array_push($TableContent, $item);
                });
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null, array(
                            'InvoiceNumber' => 'Rechnungsnummer',
                            'Time'          => 'Abrechnungszeitraum',
                            'BasketName'    => 'Name der Abrechnung',
                            'CauserPerson'  => 'Beitragsverursacher',
                            'DebtorPerson'  => 'Beitragszahler',
                            'Item'          => 'Beitragsart',
                            'ItemQuantity'  => 'Anzahl',
                            'ItemPrice'     => 'Einzelpreis',
                            'ItemSumPrice'  => 'Gesamtpreis'
                        ), array(
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => array(0, 6, 7, 8)),
//                                array('type' => 'de_date', 'targets' => array(2)),
//                                array("orderable" => false, "targets"   => -1),
                            ),
                            'order'      => array(
//                            array(1, 'desc'),
                                array(0, 'desc')
                            ),
                            'responsive' => false,
                        ))
                    )
                )
            )
        ));

        return $Stage;
    }
}
