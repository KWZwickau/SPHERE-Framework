<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
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

        $Stage = new Stage('Rechnungsliste', 'Sicht Beitragszahler');
        $tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Invoice['Year'], $Invoice['Month']);
        $TableContent = array();
        if($tblInvoiceList){
            array_walk($tblInvoiceList, function(TblInvoice $tblInvoice) use (&$TableContent){
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'.'.$tblInvoice->getMonth(true);
                $item['TargetTime'] = $tblInvoice->getTargetTime();
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
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
                        $ItemList[] = $tblInvoiceItemDebtor->getName();
                        $ItemPrice += $tblInvoiceItemDebtor->getQuantity() * $tblInvoiceItemDebtor->getValue();
                    }
                    $ItemString = implode(', ', $ItemList);
                    // convert to Frontend
                    $item['SumPrice'] = new ToolTip(number_format($ItemPrice, 2).' €', $ItemString);
                }
                $item['Option'] = '';

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
                            'Time' => 'Abrechnungszeitraum',
                            'TargetTime' => 'Fälligkeitsdatum',
                            'CauserPerson' => 'Beitragsverursacher',
                            'DebtorPerson' => 'Beitragszahler',
                            'DebtorNumber' => 'Debit.-Nr.',
                            'SumPrice' => 'Gesamtbetrag',
                            'Option' => '',
                        ), array(
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => array(0)),
                                array('type' => 'de_date', 'targets' => array(2)),
                                array("orderable" => false, "targets"   => -1),
                            ),
                            'order'      => array(
//                            array(1, 'desc'),
                                array(0, 'desc')
                            ),
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

        $Stage = new Stage('Rechnungsliste', 'Sicht Beitragsverursacher');
        $tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Invoice['Year'], $Invoice['Month']);
        $TableContent = array();
        if($tblInvoiceList){
            array_walk($tblInvoiceList, function(TblInvoice $tblInvoice) use (&$TableContent){
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'.'.$tblInvoice->getMonth(true);
//                $item['TargetTime'] = $tblInvoice->getTargetTime();
                $item['CauserPerson'] = '';
                //ToDO neue Funktion erstellen!
//                if(($tblInvoiceCauser = Invoice::useService()->getPersonCauserByInvoice($tblInvoice))){
//                    $item['CauserPerson'] = $tblInvoiceCauser->getLastFirstName();
//                }


                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
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
                        $item['Option'] = '';
                        // convert to Frontend
                        $item['ItemPrice'] = number_format($item['ItemPrice'], 2).' €';
                        $item['ItemSumPrice'] = number_format($item['ItemSumPrice'], 2).' €';
                        array_push($TableContent, $item);
                    }
                }
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
                            'Item' => 'Beitragsarten',
                            'ItemQuantity' => 'Menge',
                            'ItemPrice' => new ToolTip('EP', 'Einzelpreis'),
                            'ItemSumPrice' => new ToolTip('GP', 'Gesamtpreis'),
                            'CauserPerson' => 'Beitragsverursacher',
                            'Time' => 'Abrechnungszeitraum',
                            'DebtorPerson' => 'Debitor',
                            'InvoiceNumber' => 'Abr.-Nr.',
                            'Option' => '',
                        ))
                    )
                )
            ))
        ));

        return $Stage;
    }

    public function formInvoiceFilter()
    {

        $YearList = $this->getYearList();
        $MonthList = $this->getMonthList();
        return new Well(new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(new Title('Rechnungen filtern'), 2),
                    new FormColumn(new SelectBox('Invoice[Year]', 'Jahr', $YearList), 4),
                    new FormColumn(new SelectBox('Invoice[Month]', 'Monat', $MonthList, null, true, null), 4),
                )),
                new FormRow(array(
                    new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn('')))), 2),
                    new FormColumn(new Primary('Filter', new Filter()), 10),
                ))
            ))
        ));
    }

    private function getYearList()
    {

        $Now = new \DateTime();
        $Year = $Now->format('Y');
        $YearList[(int)$Year - 3] = (int)$Year - 3;
        $YearList[(int)$Year - 2] = (int)$Year - 2;
        $YearList[(int)$Year - 1] = (int)$Year - 1;
        $YearList[(int)$Year] = (int)$Year;
        $YearList[(int)$Year + 1] = (int)$Year + 1;

        return $YearList;
    }

    private function getMonthList()
    {

        $MonthList[1] = 'Januar';
        $MonthList[2] = 'Februar';
        $MonthList[3] = 'März';
        $MonthList[4] = 'April';
        $MonthList[5] = 'Mai';
        $MonthList[6] = 'Juni';
        $MonthList[7] = 'Juli';
        $MonthList[8] = 'August';
        $MonthList[9] = 'September';
        $MonthList[10] = 'Oktober';
        $MonthList[11] = 'November';
        $MonthList[12] = 'Dezember';

        return $MonthList;
    }
}
