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
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Info;
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
        if(!$doSimulation && $withData) {
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
     * @param bool    $IsCauserGroup
     *
     * @return array
     */
    public function getPriceListByItemAndYear(
        TblItem $tblItem,
        $Year,
        $MonthFrom = '1',
        $MonthTo = '12',
        $IsCauserGroup = false
    ) {
        $PriceList = array();
        $MonthList = Invoice::useService()->getMonthList((int)$MonthFrom, (int)$MonthTo);
        foreach($MonthList as $Month => $MonthName) {

            if(($tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Year, $Month)) && $tblItem) {
                array_walk($tblInvoiceList,
                    function(TblInvoice $tblInvoice) use (&$PriceList, $tblItem, $IsCauserGroup) {
                        if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoiceAndItem($tblInvoice,
                            $tblItem))) {
                            // Wahl der Zusammensetzung (Gruppierung der Preise)
                            if(!$IsCauserGroup) {
                                if(count($tblInvoiceItemDebtorList)) {
                                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                                    $tblInvoiceItemDebtor = current($tblInvoiceItemDebtorList);
                                    $this->fillPriceListByCauser($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
                                } else {
                                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                                        $this->fillPriceListByCauser($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
                                    }
                                }
                            } else {
                                if(count($tblInvoiceItemDebtorList)) {
                                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                                    $tblInvoiceItemDebtor = current($tblInvoiceItemDebtorList);
                                    $this->fillPriceListByDebtor($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
                                } else {
                                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                                        $this->fillPriceListByDebtor($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
                                    }
                                }
                            }
                        }
                    });
            }
        }

        if(!empty($PriceList)) {
            if(!$IsCauserGroup) {
                foreach($PriceList as &$Debtor) {
                    foreach($Debtor as &$PriceArray) {
                        $PriceArray['Sum'] = array_sum($PriceArray['Sum']);
                    }
                }
            } else {
                foreach($PriceList as &$PriceArray) {
                    $PriceArray['Sum'] = array_sum($PriceArray['Sum']);
                }
            }
        }

//        Debugger::screenDump($PriceList);
//        exit;
        return $PriceList;
    }

    /**
     * @param array                $PriceList
     * @param TblInvoiceItemDebtor $tblInvoiceItemDebtor
     * @param TblInvoice           $tblInvoice
     *
     * @return mixed
     */
    private function fillPriceListByCauser(
        &$PriceList,
        TblInvoiceItemDebtor $tblInvoiceItemDebtor,
        TblInvoice $tblInvoice
    ) {

        $tblPersonCauser = $tblInvoice->getServiceTblPersonCauser();
        $tblDebtor = $tblInvoiceItemDebtor->getServiceTblPersonDebtor();
        $timeString = $tblInvoice->getYear().'/'.$tblInvoice->getMonth();

        if($tblDebtor && $tblPersonCauser) {
            if($tblInvoiceItemDebtor->getIsPaid()) {
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
     * @param array                $PriceList
     * @param TblInvoiceItemDebtor $tblInvoiceItemDebtor
     * @param TblInvoice           $tblInvoice
     *
     * @return mixed
     */
    private function fillPriceListByDebtor(
        &$PriceList,
        TblInvoiceItemDebtor $tblInvoiceItemDebtor,
        TblInvoice $tblInvoice
    ) {

        $tblPersonCauser = $tblInvoice->getServiceTblPersonCauser();
        $tblDebtor = $tblInvoiceItemDebtor->getServiceTblPersonDebtor();
        $timeString = $tblInvoice->getYear().'/'.$tblInvoice->getMonth();

        if($tblDebtor && $tblPersonCauser) {
            if($tblInvoiceItemDebtor->getIsPaid()) {
                $PriceList[$tblPersonCauser->getId()]['Sum'][] =
                    $tblInvoiceItemDebtor->getSummaryPriceInt();
                $PriceList[$tblPersonCauser->getId()]['Price'][$timeString] =
                    $tblInvoiceItemDebtor->getSummaryPriceInt();
                $PriceList[$tblPersonCauser->getId()]['DebtorId'][$tblDebtor->getId()] = true;
            } else {
                $PriceList[$tblPersonCauser->getId()]['PriceMissing'][$timeString] =
                    $tblInvoiceItemDebtor->getSummaryPriceInt();
                $PriceList[$tblPersonCauser->getId()]['DebtorId'][$tblDebtor->getId()] = true;
            }
        }
        return $PriceList;
    }

    /**
     * @param array $PriceList
     * @param bool  $IsCauserGroup
     *
     * @return array
     */
    public function getTableContentByPriceList($PriceList = array(), $IsCauserGroup = false)
    {

        $tableContent = array();
        if(!empty($PriceList)) {
            asort($PriceList);
            if(!$IsCauserGroup) {
                foreach($PriceList as $DebtorId => $CauserList) {
                    if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))) {
                        foreach($CauserList as $CauserId => $Value) {
                            $this->fillColumnRowPriceList($tableContent, $tblPersonDebtor, $CauserId, $Value);
                        }
                    }
                }
            } else {
                foreach($PriceList as $CauserId => $Value) {
                    if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))) {
                        foreach($Value['DebtorId'] as $DebtorId => $DebtorTrue) {
                            if(($tblPersonDebtor = Person::useService()->getPersonById($CauserId))) {
                                $this->fillColumnRowPriceList($tableContent, $tblPersonDebtor, $CauserId, $Value);
                            }
                        }
                    }
                }
            }
        }
        return $tableContent;
    }

    /**
     * @param array     $tableContent
     * @param TblPerson $tblPersonDebtor
     * @param string    $CauserId
     * @param array     $Value
     */
    private function fillColumnRowPriceList(&$tableContent, TblPerson $tblPersonDebtor, $CauserId, $Value)
    {

        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))) {
            $MonthOpenList = array();
            $MonthList = array();
            $item['Debtor'] = $tblPersonDebtor->getLastFirstName();
            $item['Causer'] = $tblPersonCauser->getLastFirstName();
            $item['Value'] = Balance::useService()->getPriceString($Value['Sum']);
            if(isset($Value['PriceMissing'])) {
                foreach($Value['PriceMissing'] as $Time => $PriceMissing) {
                    $MonthOpenList[] = new DangerText(Balance::useService()->getPriceString($PriceMissing).' ('.$Time.')');
                }
            }
            foreach($Value['Price'] as $Time => $Price) {
                $MonthList[] = Balance::useService()->getPriceString($Price).' ('.$Time.')';
            }
            if(!empty($MonthOpenList)) {
                $ToolTipMonthPrice = new Bold('Offene Posten<br/>').implode('<br/>',
                        $MonthOpenList).new Ruler();
                $ToolTipMonthPrice .= new Bold('Bezahlt<br/>').implode('<br/>', $MonthList);
            } else {
                $ToolTipMonthPrice = 'Bezahlt<br/>'.implode('<br/>', $MonthList);
            }

            $item['Value'] .= '&nbsp;&nbsp;&nbsp;'
                .(new ToolTip(new Info(), htmlspecialchars($ToolTipMonthPrice)))->enableHtml();

            array_push($tableContent, $item);
        }
    }

    /**
     * @param array $PriceList
     *
     * @return array
     */
    private function createPersonCauserList($PriceList = array())
    {

        if(!empty($PriceList)) {
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))) {
                    foreach($CauserList as $CauserId => $Value) {
                        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))) {
//                            $Item['Debtor'] = '';
//                            $Item['Causer'] = '';
                            $Item['Value'] = '';
                            // Debtor
                            $Item['DebtorSalutation'] = $tblPersonDebtor->getSalutation();
                            $Item['DebtorTitle'] = $tblPersonDebtor->getTitle();
                            $Item['DebtorFirstName'] = $tblPersonDebtor->getFirstName();
                            $Item['DebtorLastName'] = $tblPersonDebtor->getLastName();
                            // Causer
                            $Item['CauserFirstName'] = $tblPersonCauser->getFirstName();
                            $Item['CauserCallName'] = $tblPersonCauser->getCallName();
                            $Item['CauserLastName'] = $tblPersonCauser->getLastName();

                            $Item['Value'] = Balance::useService()->getPriceString($Value['Sum']);
                            $Item['Address'] = '';
                            if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))) {
                                $Item['Address'] = $tblAddress->getGuiString();
                            }
                            array_push($PersonList, $Item);
                        }
                    }
                }
            }
        }
        return $PersonList;
    }

    /**
     * @param array  $PriceList
     *
     * @param string $ItemName
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createBalanceListExcel($PriceList, $ItemName = '')
    {

        $PersonList = array();
        if(!empty($PriceList)) {
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))) {
                    foreach($CauserList as $CauserId => $Value) {
                        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))) {
//                            $Item['Debtor'] = '';
//                            $Item['Causer'] = '';
                            $Item['Value'] = '';
                            // Debtor
                            $Item['DebtorSalutation'] = $tblPersonDebtor->getSalutation();
                            $Item['DebtorTitle'] = $tblPersonDebtor->getTitle();
                            $Item['DebtorFirstName'] = $tblPersonDebtor->getFirstName();
                            $Item['DebtorLastName'] = $tblPersonDebtor->getLastName();
                            // Causer
                            $Item['CauserFirstName'] = $tblPersonCauser->getFirstName();
                            $Item['CauserLastName'] = $tblPersonCauser->getLastName();

                            $Item['Value'] = Balance::useService()->getPriceString($Value['Sum']);
                            $Item['Address'] = '';
                            if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))) {
                                $Item['Address'] = $tblAddress->getGuiString();
                            }
                            array_push($PersonList, $Item);
                        }
                    }
                }
            }
        }


        if(!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;
//            // First Row
//            $export->setValue($export->getCell($column++, $row), 'Beitragsart:');
//            $export->setValue($export->getCell($column++, $row), $ItemName);
//            $column++;
//            $export->setValue($export->getCell($column++, $row), 'Zeitraum:');
//            $export->setValue($export->getCell($column, $row), $StartMonth.' - '.$ToMonth);
//            $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row++))->setFontBold();

//            $column = 0;
//            // head
//            $export->setValue($export->getCell($column, $row), "Beitragszahler");
//            $column += 4;
//            $export->setValue($export->getCell($column, $row), "Beitragsverursacher");
//            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells()->setFontBold();
//            $export->setStyle($export->getCell(4, $row), $export->getCell(5, $row))->mergeCells()->setFontBold();
////            $column += 3;

            $export->setValue($export->getCell($column++, $row), "Anrede");
            $export->setValue($export->getCell($column++, $row), "Titel");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Summe");
            $export->setValue($export->getCell($column++, $row), "Adresse");
            $export->setValue($export->getCell($column, $row), "Beitragsart");


            foreach($PersonList as $PersonData) {
                $column = 0;
                $row++;
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorSalutation']);
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorTitle']);
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorFirstName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorLastName']);

                $export->setValue($export->getCell($column++, $row), $PersonData['CauserFirstName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['CauserLastName']);

                $export->setValue($export->getCell($column++, $row), $PersonData['Value']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Address']);
                $export->setValue($export->getCell($column, $row), $ItemName);
            }

            //Column width
            $column = 0;
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(12);


            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}