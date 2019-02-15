<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Api\Billing\Invoice\ApiInvoiceIsPaid;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Info;
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
        $tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Invoice['Year'], $Invoice['Month'],
            $Invoice['BasketName']);
        $TableContent = array();
        if($tblInvoiceList){
            array_walk($tblInvoiceList, function(TblInvoice $tblInvoice) use (&$TableContent){
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                $item['TargetTime'] = $tblInvoice->getTargetTime();
                $item['BasketName'] = $tblInvoice->getBasketName();
                $item['CauserPerson'] = '';
                if($tblPersonCauser = $tblInvoice->getServiceTblPersonCauser()){
                    $item['CauserPerson'] = $tblPersonCauser->getLastFirstName();
                }
                $item['DebtorPerson'] = '';
                $item['DebtorNumber'] = '';
                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                    $tblInvoiceItemDebtor = current($tblInvoiceItemDebtorList);
                    $item['DebtorPerson'] = $tblInvoiceItemDebtor->getDebtorPerson();
                    $item['DebtorNumber'] = $tblInvoiceItemDebtor->getDebtorNumber();
                    $ItemList = array();
                    $ItemPrice = 0;
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                        $ItemList[] = $tblInvoiceItemDebtor->getName();
                        $ItemPrice += $tblInvoiceItemDebtor->getQuantity() * $tblInvoiceItemDebtor->getValue();
                    }
                    $ItemString = implode(', ', $ItemList);
                    // convert to Frontend
                    $item['SumPrice'] = number_format($ItemPrice, 2).' €&nbsp;&nbsp;&nbsp;'.new ToolTip(new Info(),
                            $ItemString);
                }
//                $item['Option'] = '';

                array_push($TableContent, $item);
            });
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($this->formInvoiceFilter())
                ),
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null, array(
                            'InvoiceNumber' => 'Abr.-Nr.',
                            'Time'          => 'Abrechnungszeitraum',
                            'TargetTime'    => 'Fälligkeitsdatum',
                            'CauserPerson'  => 'Beitragsverursacher',
                            'DebtorPerson'  => 'Beitragszahler',
                            'DebtorNumber'  => 'Debitoren-Nr.',
                            'SumPrice'      => 'Gesamtbetrag',
                            'BasketName'    => 'Name der Abrechnung',
//                            'Option' => '',
                        ), array(
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => array(0, 6)),
                                array('type' => 'de_date', 'targets' => array(2)),
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
        $tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Invoice['Year'], $Invoice['Month'],
            $Invoice['BasketName']);
        $TableContent = array();
        if($tblInvoiceList){
            array_walk($tblInvoiceList, function(TblInvoice $tblInvoice) use (&$TableContent){
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                $item['BasketName'] = $tblInvoice->getBasketName();
//                $item['TargetTime'] = $tblInvoice->getTargetTime();
                //ToDO Person aus Service oder fester string?
                $item['CauserPerson'] = $tblInvoice->getLastName().', '.$tblInvoice->getFirstName();


                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                        $item['DebtorPerson'] = '';
                        $item['Item'] = '';
                        $item['ItemQuantity'] = '';
                        $item['ItemPrice'] = 0;
                        $item['ItemSumPrice'] = 0;

                        $item['DebtorPerson'] = $tblInvoiceItemDebtor->getDebtorPerson();
                        $item['Item'] = $tblInvoiceItemDebtor->getName();
                        $item['ItemQuantity'] = $tblInvoiceItemDebtor->getQuantity();
                        $item['ItemPrice'] = $tblInvoiceItemDebtor->getValue();
                        $item['ItemSumPrice'] = $tblInvoiceItemDebtor->getQuantity() * $tblInvoiceItemDebtor->getValue();

                        $CheckBox = (new CheckBox('IsPaid', ' ', $tblInvoiceItemDebtor->getId()))->ajaxPipelineOnClick(
                            ApiInvoiceIsPaid::pipelineChangeIsPaid($tblInvoiceItemDebtor->getId()));
                        if(!$tblInvoiceItemDebtor->getIsPaid()){
                            $CheckBox->setChecked();
                        }

                        $item['IsPaid'] = ApiInvoiceIsPaid::receiverIsPaid($CheckBox, $tblInvoiceItemDebtor->getId());
//                        $item['Option'] = '';
                        // convert to Frontend
                        $item['ItemPrice'] = number_format($item['ItemPrice'], 2).' €';
                        $item['ItemSumPrice'] = number_format($item['ItemSumPrice'], 2).' €';
                        array_push($TableContent, $item);
                    }
                }
            });
        }

        $Stage->setContent(
            ApiInvoiceIsPaid::receiverService()
            .new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn($this->formInvoiceFilter())
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'Item'          => 'Beitragsarten',
                                'ItemQuantity'  => 'Menge',
                                'ItemPrice'     => new ToolTip('EP', 'Einzelpreis'),
                                'ItemSumPrice'  => new ToolTip('GP', 'Gesamtpreis'),
                                'CauserPerson'  => 'Beitragsverursacher',
                                'Time'          => 'Abrechnungszeitraum',
                                'DebtorPerson'  => 'Debitor',
                                'InvoiceNumber' => 'Abr.-Nr.',
                                'BasketName'    => 'Name der Abrechnung',
                                'IsPaid'        => 'Offene Posten',
//                                'Option' => '',
                            ), array(
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => array(1, 2, 3, 7)),
//                                    array('type' => 'de_date', 'targets' => array(2)),
                                    array("orderable" => false, "targets" => -1),
                                ),
                                'order'      => array(
//                            array(1, 'desc'),
                                    array(7, 'desc')
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

    public function formInvoiceFilter()
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

        return new Well(new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(new Title('Rechnungen filtern'))
                ),
                new FormRow(array(
                    new FormColumn(new SelectBox('Invoice[Year]', 'Jahr', $YearList), 4),
                    new FormColumn(new SelectBox('Invoice[Month]', 'Monat', $MonthList, null, true, null), 4),
                    new FormColumn(new AutoCompleter('Invoice[BasketName]', 'Name der Abrechnung', '', $BasketNameList),
                        4),
                )),
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
