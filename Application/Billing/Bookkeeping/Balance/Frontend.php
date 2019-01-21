<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendBalance($Balance = array())
    {

        $Stage = new Stage('Beleg-Druck');

        if(!isset($_POST['Balance']['Item']) && ($tblItem = Item::useService()->getItemByName('Schulgeld'))){
            $_POST['Balance']['Item'] = $tblItem->getId();
        }
        if(!isset($Invoice['Year'])){
            $Now = new \DateTime();
            $_POST['Balance']['Year'] = $Now->format('Y');
            $_POST['Balance']['From'] = '01.01.'.$Now->format('Y');
        }

        if(!empty($Balance)){
            $Year = $Balance['Year'];
            $ItemId = $Balance['Item'];
            //ToDO Invoice über Zeitraum ziehen
//            $From = $Balance['From'];
//            $To = $Balance['To'];
            $tblItem = Item::useService()->getItemById($ItemId);
            $PriceList = array();
            if(($tblInvoiceList = Invoice::useService()->getInvoiceAllByYear($Year)) && $tblItem){
                array_walk($tblInvoiceList, function (TblInvoice $tblInvoice) use (&$PriceList, $tblItem){
                    $tblPersonCauser = $tblInvoice->getServiceTblPersonCauser();
                    if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoiceAndItem($tblInvoice, $tblItem))){
                        if(count($tblInvoiceItemDebtorList)){
                            /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                            $tblInvoiceItemDebtor = current($tblInvoiceItemDebtorList);
                            if($tblInvoiceItemDebtor->getIsPaid()){
                                $tblDebtor = $tblInvoiceItemDebtor->getServiceTblPersonDebtor();
                                if($tblDebtor && $tblPersonCauser){
                                    $PriceList[$tblDebtor->getId()][$tblPersonCauser->getId()][] = $tblInvoiceItemDebtor->getQuantity()*$tblInvoiceItemDebtor->getValue();
                                }
                            }
                        } else {
                            foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
                                if($tblInvoiceItemDebtor->getIsPaid()){
                                    $tblDebtor = $tblInvoiceItemDebtor->getServiceTblPersonDebtor();
                                    if($tblDebtor && $tblPersonCauser){
                                        $PriceList[$tblDebtor->getId()][$tblPersonCauser->getId()][] = $tblInvoiceItemDebtor->getQuantity()*$tblInvoiceItemDebtor->getValue();
                                    }
                                }
                            }
                        }
                    }
                });
            }
            if(!empty($PriceList)){
                foreach($PriceList as &$Debtor){
                    foreach($Debtor as &$PriceArray){
                        $PriceArray = array_sum($PriceArray);
                    }
                }
            }
        }
        $TableContent = array();
        if(!empty($PriceList)) {
            foreach ($PriceList as $DebtorId => $CauserList){
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))){
                    foreach($CauserList as $CauserId => $Value){
                        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))) {
                            $item['Debtor'] = $tblPersonDebtor->getLastFirstName();
                            $item['Causer'] = $tblPersonCauser->getLastFirstName();
                            $item['Value'] = Balance::useService()->getPriceString($Value);
                            array_push($TableContent, $item);
                        }
                    }
                }
            }
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($this->formBalanceFilter())
                ),
                new LayoutRow(
                    new LayoutColumn(new TableData($TableContent, null))
                ),
//                // Test DropDown Selectbox
//                new LayoutRow(
//                    new LayoutColumn('<div style="height: 230px;"></div>')
//                )
            ))
        ));

        return $Stage;
    }

    public function formBalanceFilter()
    {

        // SelectBox content
        $YearList = Invoice::useService()->getYearList(1,1);
        $tblItemAll = Item::useService()->getItemAll();

//        $MonthList = Invoice::useService()->getMonthList();

        return new Well(
        new Title('Filterung für Belegdruck', '').
        new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn((new SelectBox('Balance[Year]', 'Jahr', $YearList))->setRequired(), 4),
                    new FormColumn(new DatePicker('Balance[From]', '', 'Zeitraum Von'), 4),
                    new FormColumn(new DatePicker('Balance[To]', '', 'Zeitraum Bis'), 4),

//                    new FormColumn(new SelectBox('Basket[Month]', 'Monat', $MonthList, null, true, null), 4),
                )),
                new FormRow(array(
                    new FormColumn((new SelectBox('Balance[Item]', 'Beitragsart', array('{{ Name }}' => $tblItemAll)))->setRequired(), 4),
                )),
                new FormRow(
                    new FormColumn(new Primary('Filtern', new Filter()))
                )
            ))
        ));
    }
}
