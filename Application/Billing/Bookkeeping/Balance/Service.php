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
     *
     * @return array
     */
    public function getPriceListByItemAndYear(
        TblItem $tblItem,
        $Year,
        $MonthFrom = '1',
        $MonthTo = '12'
    ) {
        $PriceList = array();
//        $MonthList = Invoice::useService()->getMonthList((int)$MonthFrom, (int)$MonthTo);
//        foreach($MonthList as $Month => $MonthName) {
//
//            if(($tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Year, $Month)) && $tblItem) {
//                array_walk($tblInvoiceList,
//                    function(TblInvoice $tblInvoice) use (&$PriceList, $tblItem) {
//                        if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoiceAndItem($tblInvoice,
//                            $tblItem))) {
//                            if(count($tblInvoiceItemDebtorList)) {
//                                /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
//                                $tblInvoiceItemDebtor = current($tblInvoiceItemDebtorList);
//                                $this->fillPriceListByCauser($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
//                            } else {
//                                foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
//                                    $this->fillPriceListByCauser($PriceList, $tblInvoiceItemDebtor, $tblInvoice);
//                                }
//                            }
//                        }
//                    });
//            }
//        }


        $ResultList = $this->getPriceList($tblItem, $Year, $MonthFrom, $MonthTo);
        foreach($ResultList as $Key => $RowContent){
            $PersonDebtorId = isset($RowContent['PersonDebtorId']) ? $RowContent['PersonDebtorId'] : false;
            $PeronCauserId = isset($RowContent['PeronCauserId']) ? $RowContent['PeronCauserId'] : false;
            $timeString = isset($RowContent['Year']) && isset($RowContent['Month']) ? $RowContent['Year'].'/'.$RowContent['Month'] : false;
            if($PersonDebtorId && $PeronCauserId && $timeString){
                if(isset($RowContent['IsPaid']) && $RowContent['IsPaid']) {
                    $PriceList[$PersonDebtorId][$PeronCauserId]['Sum'][] = $RowContent['Value'];
                    $PriceList[$PersonDebtorId][$PeronCauserId]['Price'][$timeString] = $RowContent['Value'];
                            } else {
                    $PriceList[$PersonDebtorId][$PeronCauserId]['PriceMissing'][$timeString] = $RowContent['Value'];
                            }
                        }
            }

        if(!empty($PriceList)) {
            foreach($PriceList as &$Debtor) {
                foreach($Debtor as &$PriceArray) {
                    $PriceArray['Sum'] = array_sum($PriceArray['Sum']);
                }
            }
        }


        return $PriceList;
    }

    /** @deprecated
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
     * @param array $PriceList
     *
     * @return array
     */
    public function getTableContentByPriceList($PriceList = array())
    {

        $tableContent = array();
        if(!empty($PriceList)) {
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))) {
                    foreach($CauserList as $CauserId => $Value) {
                        $this->fillColumnRowPriceList($tableContent, $tblPersonDebtor, $CauserId, $Value);
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
     * @param array $PersonList
     *
     * @return array $PersonList
     */
    private function sortPersonListByDebtorName($PersonList = array())
    {

        $lastName = array();
        $firstName = array();
        if(!empty($PersonList)){
            foreach ($PersonList as $Key => $row) {

                $lastName[$Key] = strtoupper($row['DebtorLastName']);
                $firstName[$Key] = strtoupper($row['DebtorFirstName']);
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $PersonList);
        }

//        Consumer::useService()->getGermanSortBySetting();

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
                            $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                            if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))) {
                                $Item['StreetName'] = $tblAddress->getStreetName();
                                $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                                $Item['Code'] = $tblAddress->getTblCity()->getCode();
                                $Item['City'] = $tblAddress->getTblCity()->getName();
                                $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                            }
                            array_push($PersonList, $Item);
                        }
                    }
                }
            }
        }
        $PersonList = $this->sortPersonListByDebtorName($PersonList);

        if(!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;

            $export->setValue($export->getCell($column++, $row), "Anrede Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Titel Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Vorname Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Nachname Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Vorname Beitragsverursacher");
            $export->setValue($export->getCell($column++, $row), "Nachname Beitragsverursacher");
            $export->setValue($export->getCell($column++, $row), "Summe");
            $export->setValue($export->getCell($column++, $row), "Straße");
            $export->setValue($export->getCell($column++, $row), "Str.Nr.");
            $export->setValue($export->getCell($column++, $row), "PLZ");
            $export->setValue($export->getCell($column++, $row), "Stadt");
            $export->setValue($export->getCell($column++, $row), "Ortsteil");
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

                $export->setValue($export->getCell($column++, $row), $PersonData['StreetName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
                $export->setValue($export->getCell($column++, $row), $PersonData['City']);
                $export->setValue($export->getCell($column++, $row), $PersonData['District']);

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

    /**
     * @param TblItem $tblItem
     * @param         $Year
     * @param         $MonthFrom
     * @param         $MonthTo
     *
     * @return array|bool
     */
    public function getPriceList(TblItem $tblItem, $Year, $MonthFrom, $MonthTo)
    {

        return (new Data($this->getBinding()))->getPriceList($tblItem, $Year, $MonthFrom, $MonthTo);
    }
}