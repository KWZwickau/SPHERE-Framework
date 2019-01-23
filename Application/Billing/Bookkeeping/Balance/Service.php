<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    public function getPriceString($Value)
    {

        return number_format($Value, 2).' €';
    }

    /**
     * @param $Id
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return (new Data($this->getBinding()))->getPaymentTypeById($Id);
    }

    /**
     * @return false|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        return (new Data($this->getBinding()))->getPaymentTypeAll();
    }

    /**
     * @param $Name
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getPaymentTypeByName($Name);
    }

    /**
     * @param TblItem $tblItem
     * @param string  $Year
     * @param string  $MonthFrom
     * @param string  $MonthTo
     *
     * @return array
     */
    public function getPriceListByItemAndYear(TblItem $tblItem, $Year, $MonthFrom = '1', $MonthTo = '12')
    {
        $PriceList = array();
        $MonthList = Invoice::useService()->getMonthList((int)$MonthFrom, (int)$MonthTo);
        foreach($MonthList as $Month => $MonthName){

            if(($tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Year, $Month)) && $tblItem){
                array_walk($tblInvoiceList, function (TblInvoice $tblInvoice) use (&$PriceList, $tblItem){
                    if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoiceAndItem($tblInvoice, $tblItem))){
                        if(count($tblInvoiceItemDebtorList)){
                            /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                            $tblInvoiceItemDebtor = current($tblInvoiceItemDebtorList);
                            $this->fillPriceListByCauser($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
                        } else {
                            foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
                                $this->fillPriceListByCauser($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
                            }
                        }
                    }
                });
            }
        }

        if(!empty($PriceList)){
            foreach($PriceList as &$Debtor){
                foreach($Debtor as &$PriceArray){
                    $PriceArray['Sum'] = array_sum($PriceArray['Sum']);
                }
            }
        }

        return $PriceList;
    }

    /**
     * @param array                $PriceList
     * @param TblInvoiceItemDebtor $tblInvoiceItemDebtor
     * @param TblInvoice           $tblInvoice
     *
     * @return mixed
     */
    private function fillPriceListByCauser(&$PriceList, TblInvoiceItemDebtor $tblInvoiceItemDebtor, TblInvoice $tblInvoice)
    {

        $tblPersonCauser = $tblInvoice->getServiceTblPersonCauser();
        $tblDebtor = $tblInvoiceItemDebtor->getServiceTblPersonDebtor();
        $timeString = $tblInvoice->getMonth().'.'.$tblInvoice->getYear();

        if($tblDebtor && $tblPersonCauser) {
            if($tblInvoiceItemDebtor->getIsPaid()){
                $PriceList[$tblDebtor->getId()][$tblPersonCauser->getId()]['Sum'][] =
                    $tblInvoiceItemDebtor->getSummaryPriceInt();
                $PriceList[$tblDebtor->getId()][$tblPersonCauser->getId()]['Price'][$timeString] =
                    $tblInvoiceItemDebtor->getSummaryPriceInt();
            } else {
                $PriceList[$tblDebtor->getId()][$tblPersonCauser->getId()]['PriceMissing'][$timeString] =
                    $tblInvoiceItemDebtor->getSummaryPriceInt();
            }
        }
        return $PriceList;
    }

    /**
     * @param array $PriceList
     *
     * @return array
     */
    public function getTableContentByPriceList($PriceList = array())
    {

        $tableContent = array();
        if(!empty($PriceList)) {
            asort($PriceList);
            foreach ($PriceList as $DebtorId => $CauserList){
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))){
                    foreach($CauserList as $CauserId => $Value){
                        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))) {
                            $MonthOpenList = array();
                            $MonthList = array();
                            $item['Debtor'] = $tblPersonDebtor->getLastFirstName();
                            $item['Causer'] = $tblPersonCauser->getLastFirstName();
                            $item['Value'] = Balance::useService()->getPriceString($Value['Sum']);
                            if(isset($Value['PriceMissing'])){
                                foreach($Value['PriceMissing'] as $Time => $PriceMissing){
                                    $MonthOpenList[] = new DangerText(Balance::useService()->getPriceString($PriceMissing).' ('.$Time.')');
                                }
                            }
                            foreach($Value['Price'] as $Time => $Price){
                                $MonthList[] = Balance::useService()->getPriceString($Price).' ('.$Time.')';
                            }
                            if(!empty($MonthOpenList)){
                                $ToolTipMonthPrice = new Bold('Offene Posten<br/>').implode('<br/>', $MonthOpenList).new Ruler();
                                $ToolTipMonthPrice .= new Bold('Bezahlt<br/>').implode('<br/>', $MonthList);
                            } else {
                                $ToolTipMonthPrice = 'Bezahlt<br/>'.implode('<br/>', $MonthList);
                            }

                            $item['Value'] = (new ToolTip($item['Value'], htmlspecialchars($ToolTipMonthPrice)))->enableHtml();

                            array_push($tableContent, $item);
                        }
                    }
                }
            }
        }
        return $tableContent;
    }

    /**
     * @param array  $PriceList
     *
     * @param string $ItemName
     *
     * @param string $From
     * @param string $To
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createBalanceListExcel($PriceList, $ItemName = '', $From = '', $To = '')
    {

        $MonthList = Invoice::useService()->getMonthList();
        $StartMonth = $MonthList[$From];
        $ToMonth = $MonthList[$To];

        $PersonList = array();
        if(!empty($PriceList)) {
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))) {
                    foreach($CauserList as $CauserId => $Value) {
                        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))) {
//                            $Item['Debtor'] = '';
//                            $Item['Causer'] = '';
                            $Item['Value'] = '';
                            $Item['Debtor'] = $tblPersonDebtor->getLastFirstName();
                            $Item['Causer'] = $tblPersonCauser->getLastFirstName();
                            $Item['Value'] = Balance::useService()->getPriceString($Value['Sum']);
                            $Item['Address'] = '';
                            if(($tblAddress = Address::useService()->getDeliverAddressByPerson($tblPersonDebtor))){
                                $Item['Address'] = $tblAddress->getGuiString();
                            }
                            array_push($PersonList, $Item);
                        }
                    }
                }
            }
        }


        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;
            // First Row
            $export->setValue($export->getCell($column++, $row), 'Beitragsart:');
            $export->setValue($export->getCell($column++, $row), $ItemName);
            $export->setValue($export->getCell($column++, $row), 'Zeitraum:');
            $export->setValue($export->getCell($column, $row), $StartMonth.' - '.$ToMonth);
            $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row++))->setFontBold();

            $column = 0;
            // head
            $export->setValue($export->getCell($column++, $row), "Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Beitragsverursacher");
            $export->setValue($export->getCell($column++, $row), "Summe");
            $export->setValue($export->getCell($column, $row), "Adresse");
            foreach ($PersonList as $PersonData) {
                $column = 0;
                $row++;
                $export->setValue($export->getCell($column++, $row), $PersonData['Debtor']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Causer']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Value']);
                $export->setValue($export->getCell($column, $row), $PersonData['Address']);
            }

            //Column width
            $column = 0;
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(22);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(22);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(35);


            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}