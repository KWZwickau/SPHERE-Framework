<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Facade\CustomerDirectDebitFacade;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
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
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
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
     * @param string  $DivisionId
     *
     * @return array
     */
    public function getPriceListByItemAndYear(
        TblItem $tblItem,
        $Year,
        $MonthFrom = '1',
        $MonthTo = '12',
        $DivisionId = '0'
    ){
        $PriceList = array();
        $ResultList = $this->getPriceList($tblItem, $Year, $MonthFrom, $MonthTo);
        if($ResultList){
            foreach($ResultList as $Key => $RowContent) {
                $PersonDebtorId = isset($RowContent['PersonDebtorId']) ? $RowContent['PersonDebtorId'] : false;
                $PersonCauserId = isset($RowContent['PeronCauserId']) ? $RowContent['PeronCauserId'] : false;
                $timeString = isset($RowContent['Year']) && isset($RowContent['Month']) ? $RowContent['Year'].'/'.$RowContent['Month'] : false;
                if($PersonDebtorId && $PersonCauserId && $timeString){
                    if(isset($RowContent['IsPaid']) && $RowContent['IsPaid']){
                        $PriceList[$PersonDebtorId][$PersonCauserId]['Sum'][] = $RowContent['Value'];
                        $PriceList[$PersonDebtorId][$PersonCauserId]['Price'][$timeString] = $RowContent['Value'];
                    } else {
                        $PriceList[$PersonDebtorId][$PersonCauserId]['PriceMissing'][$timeString] = $RowContent['Value'];
                    }
                }
            }
        }

        if(!empty($PriceList)){
            foreach($PriceList as &$Debtor) {
                foreach($Debtor as &$PriceArray) {
                    if(isset($PriceArray['Sum'])){
                        $PriceArray['Sum'] = array_sum($PriceArray['Sum']);
                    } else {
                        $PriceArray['Sum'] = 0;
                    }
                }
            }
        }

        // use only division matched Person's
        if(!empty($PriceList) && $DivisionId !== '0' && ($tblDivision = Division::useService()->getDivisionById($DivisionId))){
            $tblPersonList = Division::useService()->getPersonAllByDivisionList(array($tblDivision));
            foreach($PriceList as &$DebtorList){
                foreach($DebtorList as $CauserId => &$Content){
                    $tblPersonCauser = Person::useService()->getPersonById($CauserId);
                    if(!in_array($tblPersonCauser, $tblPersonList)){
                        $Content = false;
                    }
                }
                // remove mismatched Student
                $DebtorList = array_filter($DebtorList);
            }
            // remove empty DebtorList
            $PriceList = array_filter($PriceList);
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
        if(!empty($PriceList)){
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))){
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

        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))){
            $MonthOpenList = array();
            $MonthList = array();
            $item['Debtor'] = $tblPersonDebtor->getLastFirstName();
            $item['Causer'] = $tblPersonCauser->getLastFirstName();
            $item['Value'] = Balance::useService()->getPriceString($Value['Sum']);
            $item['Info'] = '';
            if(isset($Value['PriceMissing'])){
                foreach($Value['PriceMissing'] as $Time => $PriceMissing) {
                    $MonthOpenList[] = new DangerText(Balance::useService()->getPriceString($PriceMissing).' ('.$Time.')');
                }
                $item['Info'] = new DangerText(new ToolTip(new EyeOpen(), 'Offene Posten'));
            }
            if(isset($Value['Price'])){
                foreach($Value['Price'] as $Time => $Price) {
                    $MonthList[] = Balance::useService()->getPriceString($Price).' ('.$Time.')';
                }
            }
            if(!empty($MonthOpenList)){
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
            foreach($PersonList as $Key => $row) {

                $lastName[$Key] = strtoupper($row['DebtorLastName']);
                $firstName[$Key] = strtoupper($row['DebtorFirstName']);
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $PersonList);
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
        if(!empty($PriceList)){
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))){
                    foreach($CauserList as $CauserId => $Value) {
                        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))){
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
                            if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))){
                                $Item['StreetName'] = $tblAddress->getStreetName();
                                $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                                $Item['Street'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
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

        if(!empty($PersonList)){

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

                $export->setValue($export->getCell($column++, $row), $PersonData['Street']);
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
     * @param string $Year
     * @param string $Month
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createMonthOverViewExcel($Year, $Month)
    {
        $resultList =  Balance::useService()->getPriceSummaryByMonth($Year, $Month);
        if(!empty($resultList)){
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;
            $export->setValue($export->getCell($column++, $row), "Beitragsart");
            $export->setValue($export->getCell($column++, $row), "Abrechnungszeitraum");
            $export->setValue($export->getCell($column, $row), "Summe");

            foreach($resultList as $result) {
                $column = 0;
                $row++;
                $export->setValue($export->getCell($column++, $row), $result['Name']);
                $export->setValue($export->getCell($column++, $row), $result['Year'].str_pad($result['Month'], 2, '0', STR_PAD_LEFT));
                $export->setValue($export->getCell($column, $row), Balance::useService()->getPriceString($result['Summary']));
            }

            //Column width
            $column = 0;
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(12);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }

        return false;
    }

    /**
     * @param string $Year
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createYearOverViewExcel($Year)
    {
        $resultList =  Balance::useService()->getPriceSummaryByYear($Year);
        $MonthList = Invoice::useService()->getMonthList();
        if(!empty($resultList)){
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;

            $export->setValue($export->getCell($column++, $row), "Beitragsart");
            $export->setValue($export->getCell($column++, $row), "Abrechnungszeitraum");
            $export->setValue($export->getCell($column, $row), "Summe");

            $YearSummary = array();
            $TimeInterval = $resultList[0]['Month'];
            $row++;
            $row++;
            $column = 0;
            $export->setValue($export->getCell($column, $row), $MonthList[$resultList[0]['Month']]);
            $export->setStyle($export->getCell($column, $row))->setFontBold();
            foreach($resultList as $result) {
                $column = 0;
                $row++;
                if($TimeInterval != $result['Month']){
                    $export->setValue($export->getCell($column, $row), $MonthList[$result['Month']]);
                    $export->setStyle($export->getCell($column, $row))->setFontBold();
                    $row++;
                    $TimeInterval = $result['Month'];
                }
                $export->setValue($export->getCell($column++, $row), $result['Name']);
                $export->setValue($export->getCell($column++, $row), $result['Year'].'.'.str_pad($result['Month'], 2, '0', STR_PAD_LEFT));
                $export->setValue($export->getCell($column, $row), Balance::useService()->getPriceString($result['Summary']));
//                $export->setValue($export->getCell($column, $row), $result['Summary']);

                if(!isset($YearSummary[$result['Name']])){
                    $YearSummary[$result['Name']] = 0;
                    $YearSummary[$result['Name']] += $result['Summary'];
                } else {
                    $YearSummary[$result['Name']] += $result['Summary'];
                }
            }
            if(!empty($YearSummary)){
                $column = 0;
                $row++;
                $row++;
                $export->setValue($export->getCell($column, $row), 'Jahreszusammenfassung:');
                $export->setStyle($export->getCell($column, $row))->setFontBold();
                foreach($YearSummary as $Item => $Value){
                    $column = 0;
                    $row++;
                    $export->setValue($export->getCell($column++, $row), $Item);
                    $export->setValue($export->getCell($column++, $row), $Year.'.'.'1-12');
                    $export->setValue($export->getCell($column, $row), Balance::useService()->getPriceString($Value));
//                    $export->setValue($export->getCell($column, $row), $Value);
                }
            }

            //Column width
            $column = 0;
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(20);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(20);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(20);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblItem      $tblItem
     * @param string       $Year
     * @param string       $MonthFrom
     * @param string       $MonthTo
     *
     * @return array|bool
     */
    public function getPriceList(TblItem $tblItem, $Year, $MonthFrom, $MonthTo)
    {

        return (new Data($this->getBinding()))->getPriceList($tblItem, $Year, $MonthFrom, $MonthTo);
    }

    /**
     * @param string $Year
     * @param string $Month
     *
     * @return array|bool
     */
    public function getPriceSummaryByMonth($Year, $Month)
    {

        return (new Data($this->getBinding()))->getPriceSummaryByMonth($Year, $Month);
    }

    /**
     * @param string $Year
     *
     * @return array|bool
     */
    public function getPriceSummaryByYear($Year)
    {

        return (new Data($this->getBinding()))->getPriceSummaryByYear($Year);
    }

    public function createDatevCsv(TblBasket $tblBasket)
    {

        $tblInvoiceList = Invoice::useService()->getInvoiceByBasket($tblBasket);
        if(!$tblInvoiceList){
            return false;
        }

        if ($tblInvoiceList) {
            $now = new \DateTime();
            $TestTime = $now->format('Ymdhis').'000'; // ToDO 3 Stellen nach der Sekunde mit anzeigen (Tausendstelsekunden)
            $fileLocation = Storage::createFilePointer('csv');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $export->setValue($export->getCell("0", $row), "EXTF");
            $export->setValue($export->getCell("1", $row), "510");
            $export->setValue($export->getCell("2", $row), "21");
            $export->setValue($export->getCell("3", $row), "Buchungsstapel");
            $export->setValue($export->getCell("4", $row), "7");
            $export->setValue($export->getCell("5", $row), $TestTime);
            $export->setValue($export->getCell("6", $row), "");
            $export->setValue($export->getCell("7", $row), "RE");
            $export->setValue($export->getCell("8", $row), "");
            $export->setValue($export->getCell("9", $row), "");
            $export->setValue($export->getCell("10", $row), "1");   // Berater
            $export->setValue($export->getCell("11", $row), "1");   // Mandat
            $export->setValue($export->getCell("12", $row), "20190101");// WJ-Beginn
            $export->setValue($export->getCell("13", $row), "7");   //Sachkonten Nummernlänge
            $export->setValue($export->getCell("14", $row), $tblBasket->getTargetTime());//Buchungsstapel von
            $export->setValue($export->getCell("15", $row), $tblBasket->getTargetTime());//Buchungsstapel bis
            $export->setValue($export->getCell("16", $row), "");    //Bezeichnung
            $export->setValue($export->getCell("17", $row), "");    //Diktatkürzel
            $export->setValue($export->getCell("18", $row), "1");   //Buchungstyp 1 = Finanzbuchführung 2 = Jahresabschluss
            $export->setValue($export->getCell("19", $row), "");    //Rechnungslegungszweck
            $export->setValue($export->getCell("20", $row), "0");   //Festschreibung 0 = keine Festschreibung 1 = Festschreibung
            $export->setValue($export->getCell("21", $row++), "EUR"); //Währungskennzeichen

            $export->setValue($export->getCell("0", $row), "Umsatz (ohne Soll-/Haben-Kennzeichen)");
            $export->setValue($export->getCell("1", $row), "Soll-/Haben-Kennzeichen");
            $export->setValue($export->getCell("2", $row), "WKZ Umsatz");
            $export->setValue($export->getCell("3", $row), "Kurs");
            $export->setValue($export->getCell("4", $row), "Basisumsatz");
            $export->setValue($export->getCell("5", $row), "WKZ Basisumsatz");
            $export->setValue($export->getCell("6", $row), "Konto");
            $export->setValue($export->getCell("7", $row), "Gegenkonto (ohne BU-Schlüssel)");
            $export->setValue($export->getCell("8", $row), "BU-Schlüssel");
            $export->setValue($export->getCell("9", $row), "Belegdatum");
            $export->setValue($export->getCell("10", $row), "Belegfeld 1");
            $export->setValue($export->getCell("11", $row), "Belegfeld 2");
            $export->setValue($export->getCell("12", $row), "Skonto");
            $export->setValue($export->getCell("13", $row), "Buchungstext");
            $export->setValue($export->getCell("14", $row), "Postensperre");
            $export->setValue($export->getCell("15", $row), "Diverse Adressnummer");
            $export->setValue($export->getCell("16", $row), "Geschäftspartnerbank");
            $export->setValue($export->getCell("17", $row), "Sachverhalt");
            $export->setValue($export->getCell("18", $row), "Zinssperre");
            $export->setValue($export->getCell("19", $row), "Beleglink");
            $export->setValue($export->getCell("20", $row), "Beleginfo – Art 1");
            $export->setValue($export->getCell("21", $row), "Beleginfo – Inhalt 1");
            $export->setValue($export->getCell("22", $row), "Beleginfo – Art 2");
            $export->setValue($export->getCell("23", $row), "Beleginfo – Inhalt 2");
            $export->setValue($export->getCell("24", $row), "Beleginfo – Art 3");
            $export->setValue($export->getCell("25", $row), "Beleginfo – Inhalt 3");
            $export->setValue($export->getCell("26", $row), "Beleginfo – Art 4");
            $export->setValue($export->getCell("27", $row), "Beleginfo – Inhalt 4");
            $export->setValue($export->getCell("28", $row), "Beleginfo – Art 5");
            $export->setValue($export->getCell("29", $row), "Beleginfo – Inhalt 5");
            $export->setValue($export->getCell("30", $row), "Beleginfo – Art 6");
            $export->setValue($export->getCell("31", $row), "Beleginfo – Inhalt 6");
            $export->setValue($export->getCell("32", $row), "Beleginfo – Art 7");
            $export->setValue($export->getCell("33", $row), "Beleginfo – Inhalt 7");
            $export->setValue($export->getCell("34", $row), "Beleginfo – Art 8");
            $export->setValue($export->getCell("35", $row), "Beleginfo – Inhalt 8");
            $export->setValue($export->getCell("36", $row), "KOST1 – Kostenstelle");
            $export->setValue($export->getCell("37", $row), "KOST2 – Kostenstelle");
            $export->setValue($export->getCell("38", $row), "KOST-Menge");
            $export->setValue($export->getCell("39", $row), "EU-Mitgliedstaat u. USt-IdNr.");
            $export->setValue($export->getCell("40", $row), "EU-Steuersatz");
            $export->setValue($export->getCell("41", $row), "Abw. Versteuerungsart");
            $export->setValue($export->getCell("42", $row), "Sachverhalt L+L");
            $export->setValue($export->getCell("43", $row), "Funktionsergänzung L+L");
            $export->setValue($export->getCell("44", $row), "BU 49 Hauptfunktionstyp");
            $export->setValue($export->getCell("45", $row), "BU 49 Hauptfunktionsnummer(");
            $export->setValue($export->getCell("46", $row), "BU 49 Funktionsergänzung");
            $export->setValue($export->getCell("47", $row), "Zusatzinformation – Art 1");
            $export->setValue($export->getCell("48", $row), "Zusatzinformation – Inhalt 1");
            $export->setValue($export->getCell("49", $row), "Zusatzinformation – Art 2");
            $export->setValue($export->getCell("50", $row), "Zusatzinformation – Inhalt 2");
            $export->setValue($export->getCell("51", $row), "Zusatzinformation – Art 3");
            $export->setValue($export->getCell("52", $row), "Zusatzinformation – Inhalt 3");
            $export->setValue($export->getCell("53", $row), "Zusatzinformation – Art 4");
            $export->setValue($export->getCell("54", $row), "Zusatzinformation – Inhalt 4");
            $export->setValue($export->getCell("55", $row), "Zusatzinformation – Art 5");
            $export->setValue($export->getCell("56", $row), "Zusatzinformation – Inhalt 5");
            $export->setValue($export->getCell("57", $row), "Zusatzinformation – Art 6");
            $export->setValue($export->getCell("58", $row), "Zusatzinformation – Inhalt 6");
            $export->setValue($export->getCell("59", $row), "Zusatzinformation – Art 7");
            $export->setValue($export->getCell("60", $row), "Zusatzinformation – Inhalt 7");
            $export->setValue($export->getCell("61", $row), "Zusatzinformation – Art 8");
            $export->setValue($export->getCell("62", $row), "Zusatzinformation – Inhalt 8");
            $export->setValue($export->getCell("63", $row), "Zusatzinformation – Art 9");
            $export->setValue($export->getCell("64", $row), "Zusatzinformation – Inhalt 9");
            $export->setValue($export->getCell("65", $row), "Zusatzinformation – Art 10");
            $export->setValue($export->getCell("66", $row), "Zusatzinformation – Inhalt 10");
            $export->setValue($export->getCell("67", $row), "Zusatzinformation – Art 11");
            $export->setValue($export->getCell("68", $row), "Zusatzinformation – Inhalt 11");
            $export->setValue($export->getCell("69", $row), "Zusatzinformation – Art 12");
            $export->setValue($export->getCell("70", $row), "Zusatzinformation – Inhalt 12");
            $export->setValue($export->getCell("71", $row), "Zusatzinformation – Art 13");
            $export->setValue($export->getCell("72", $row), "Zusatzinformation – Inhalt 13");
            $export->setValue($export->getCell("73", $row), "Zusatzinformation – Art 14");
            $export->setValue($export->getCell("74", $row), "Zusatzinformation – Inhalt 14");
            $export->setValue($export->getCell("75", $row), "Zusatzinformation – Art 15");
            $export->setValue($export->getCell("76", $row), "Zusatzinformation – Inhalt 15");
            $export->setValue($export->getCell("77", $row), "Zusatzinformation – Art 16");
            $export->setValue($export->getCell("78", $row), "Zusatzinformation – Inhalt 16");
            $export->setValue($export->getCell("79", $row), "Zusatzinformation – Art 17");
            $export->setValue($export->getCell("80", $row), "Zusatzinformation – Inhalt 17");
            $export->setValue($export->getCell("81", $row), "Zusatzinformation – Art 18");
            $export->setValue($export->getCell("82", $row), "Zusatzinformation – Inhalt 18");
            $export->setValue($export->getCell("83", $row), "Zusatzinformation – Art 19");
            $export->setValue($export->getCell("84", $row), "Zusatzinformation – Inhalt 19");
            $export->setValue($export->getCell("85", $row), "Zusatzinformation – Art 20");
            $export->setValue($export->getCell("86", $row), "Zusatzinformation – Inhalt 20");
            $export->setValue($export->getCell("87", $row), "Stück");
            $export->setValue($export->getCell("88", $row), "Gewicht");
            $export->setValue($export->getCell("89", $row), "Zahlweise");
            $export->setValue($export->getCell("90", $row), "Forderungsart");
            $export->setValue($export->getCell("91", $row), "Veranlagungsjahr");
            $export->setValue($export->getCell("92", $row), "Zugeordnete Fälligkeit");
            $export->setValue($export->getCell("93", $row), "Skontotyp");
            $export->setValue($export->getCell("94", $row), "Auftragsnummer");
            $export->setValue($export->getCell("95", $row), "Buchungstyp");
            $export->setValue($export->getCell("96", $row), "USt-Schlüssel (Anzahlungen)");
            $export->setValue($export->getCell("97", $row), "EU-Mitgliedstaat (Anzahlungen)");
            $export->setValue($export->getCell("98", $row), "Sachverhalt L+L (Anzahlungen)");
            $export->setValue($export->getCell("99", $row), "EU-Steuersatz (Anzahlungen)");
            $export->setValue($export->getCell("100", $row), "Erlöskonto (Anzahlungen)");
            $export->setValue($export->getCell("101", $row), "Herkunft-Kz");
            $export->setValue($export->getCell("102", $row), "Leerfeld");
            $export->setValue($export->getCell("103", $row), "KOST-Datum");
            $export->setValue($export->getCell("104", $row), "SEPA-Mandatsreferenz");
            $export->setValue($export->getCell("105", $row), "Skontosperre");   // 0
            $export->setValue($export->getCell("106", $row), "Gesellschaftername");
            $export->setValue($export->getCell("107", $row), "Beteiligtennummer");
            $export->setValue($export->getCell("108", $row), "Identifikationsnummer");
            $export->setValue($export->getCell("109", $row), "Zeichnernummer");
            $export->setValue($export->getCell("110", $row), "Postensperre bis");
            $export->setValue($export->getCell("111", $row), "Bezeichnung SoBil-Sachverhalt");
            $export->setValue($export->getCell("112", $row), "Kennzeichen SoBil-Sachverhalt");
            $export->setValue($export->getCell("113", $row), "Festschreibung"); // 0
            $export->setValue($export->getCell("114", $row), "Leistungsdatum");
            $export->setValue($export->getCell("115", $row), "Datum Zuord. Steuerperiode");


            foreach ($tblInvoiceList as $tblInvoice) {

                $Summary = 0;
                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
                        $Summary = $Summary + (int)$tblInvoiceItemDebtor->getSummaryPriceInt();
                    }
                }
                $Summary = str_replace(',', '', $Summary);
                $Summary = str_replace('.', ',', $Summary);

                $row++;

                $export->setValue($export->getCell("0", $row), '');
                $export->setValue($export->getCell("1", $row), 'S');
                $export->setValue($export->getCell("2", $row), $Summary);
                $export->setValue($export->getCell("3", $row), 'EUR');
                $export->setValue($export->getCell("4", $row), '');// Kurs
                $export->setValue($export->getCell("5", $row), '');// Basisumsatz
                $export->setValue($export->getCell("6", $row), '');// WKZ Basisumsatz
                $export->setValue($export->getCell("7", $row), '');// Konto
                $export->setValue($export->getCell("8", $row), '');// Gegenkonto (ohne BU-Schlüssel)
                $export->setValue($export->getCell("9", $row), '');// BU-Schlüssel
                $export->setValue($export->getCell("10", $row), '');// Belegdatum Format?
                $export->setValue($export->getCell("11", $row), '');// Belegfeld 1
                $export->setValue($export->getCell("12", $row), '');// Belegfeld 2
                $export->setValue($export->getCell("13", $row), '');// Skonto
                $export->setValue($export->getCell("14", $row), '');
                $export->setValue($export->getCell("15", $row), '');
                $export->setValue($export->getCell("16", $row), '');
                $export->setValue($export->getCell("17", $row), '');
                $export->setValue($export->getCell("18", $row), '');
                $export->setValue($export->getCell("19", $row), '');
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param array     $CheckboxList
     *
     * @return bool|\Digitick\Sepa\TransferFile\Facade\CustomerDirectDebitFacade
     */
    public function createSepaContent(TblBasket $tblBasket, $CheckboxList = array())
    {

        $tblInvoiceList = Invoice::useService()->getInvoiceByBasket($tblBasket);
        if(!$tblInvoiceList){
            return false;
        }

        $SepaPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');

        //Set the custom header (Spanish banks example) information
        $header = new GroupHeader(date('Y-m-d-H-i-s'), 'Me');
        $header->setInitiatingPartyId('DE21WVM1234567890');

        $directDebit = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($header, 'pain.008.001.02');
        $InvoiceCount = 0;
        // Bearbeitung der in der Abrechnung liegenden Posten
        foreach($tblInvoiceList as $tblInvoice){

            $PaymentId = $tblInvoice->getId().'_PaymentId';
            $countSepaPayment = 0;

            $tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice);
            // Dient nur der SEPA-Prüfung
            if($tblInvoiceItemDebtorList){
                foreach($tblInvoiceItemDebtorList as &$tblInvoiceItemDebtor){
                    if(($tblPaymentType = $tblInvoiceItemDebtor->getServiceTblPaymentType())){
                        if($tblPaymentType->getId() == $SepaPaymentType){
                            if($tblInvoiceItemDebtor->getIsPaid()){
                                $countSepaPayment++;
                            } else {
                                // Offene posten ignorieren
                                $tblInvoiceItemDebtor = false;
                            }
                        } else {
                            // Zahlung mit anderem Zahlungstyp als SEPA-Lastschrift wird ignoriert
                            $tblInvoiceItemDebtor = false;
                        }
                    } else {
                        // Zahlung ohne Zahlungstyp wird ignoriert
                        $tblInvoiceItemDebtor = false;
                    }
                }
            }
            if($countSepaPayment == 0){
                // überspringt rechnungen ohne Sepa-Lastschrift
                continue;
            }
            // entfernen der false Werte
            $tblInvoiceItemDebtorList = array_filter($tblInvoiceItemDebtorList);
            $InvoiceCount++;
            $this->addPaymentInfo($directDebit, $tblInvoice, $PaymentId);

            if(!empty($tblInvoiceItemDebtorList)){
                $this->addTransfer($directDebit, $tblInvoiceItemDebtorList, $PaymentId);
            }
        }
        // Bearbeitung der Offenen Posten
        if(!empty($CheckboxList)){
            foreach($CheckboxList as $tblInvoiceItemDebtorId){
                $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($tblInvoiceItemDebtorId);
                if($tblInvoiceItemDebtor){
                    $tblInvoice = $tblInvoiceItemDebtor->getTblInvoice();
                    $PaymentId = $tblInvoice->getId().'_PaymentId';
                    $this->addPaymentInfo($directDebit, $tblInvoice, $PaymentId);
                    $this->addTransfer($directDebit, array($tblInvoiceItemDebtor), $PaymentId, true);
                }
            }
        }


        if($InvoiceCount == 0){
            return false;
        }

        Basket::useService()->changeBasketDoneSepa($tblBasket);

        return $directDebit;
    }

    private function addPaymentInfo(CustomerDirectDebitFacade $directDebit, TblInvoice $tblInvoice, $PaymentId)
    {

        $tblInvoiceCreditor = $tblInvoice->getTblInvoiceCreditor();
        $directDebit->addPaymentInfo($PaymentId, array(
            'id'                    => $PaymentId,
            'dueDate'               => new \DateTime($tblInvoice->getTargetTime()), // optional. Otherwise default period is used
            'creditorName'          => $tblInvoiceCreditor->getOwner(),
            'creditorAccountIBAN'   => $tblInvoiceCreditor->getIBAN(),
            'creditorAgentBIC'      => $tblInvoiceCreditor->getBIC(),
            'seqType'               => PaymentInformation::S_ONEOFF,
            // Element dient der Angabe, um was für eine SEPA Lastschrift es sich handelt:
            //» SEPA OOFF = einmalige SEPA Lastschrift
            //» SEPA FRST = erste SEPA Lastschift
            //» SEPA RCUR = fortfolgende SEPA Lastschrift
            //» SEPA FNAL = letzte SEPA Lastschrift
            'creditorId'            => $tblInvoiceCreditor->getCreditorId(),
            'localInstrumentCode'   => 'COR1' // default. optional.
            // Element dient der Unterscheidung zwischen den einzelenen SEPA Lastschriften:
            //» SEPA COR1 Lastschrift (aktueller Standard)
        ));
    }

    /**
     * @param CustomerDirectDebitFacade $directDebit
     * @param array                     $tblInvoiceItemDebtorList
     * @param string                    $PaymentId
     * @param bool                      $doPaidInvoice
     *
     * @throws \Digitick\Sepa\Exception\InvalidArgumentException
     */
    private function addTransfer(CustomerDirectDebitFacade $directDebit, $tblInvoiceItemDebtorList, $PaymentId, $doPaidInvoice = false)
    {

        /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
        foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
            if(!$doPaidInvoice){
                // Offene posten ignorieren
                if(!$tblInvoiceItemDebtor->getIsPaid()){
                    continue;
                }
            }

            $ReferenceDate = '';
            if(($tblBankReference = $tblInvoiceItemDebtor->getServiceTblBankReference())){
                $ReferenceDate = $tblBankReference->getReferenceDate();
            }

            // create a payment, it's possible to create multiple payments,
            // "firstPayment" is the identifier for the transactions
            // Add a Single Transaction to the named payment
            $directDebit->addTransfer($PaymentId, array(
                'amount'                => ($tblInvoiceItemDebtor->getSummaryPriceInt() * 100), // "Sepa amount" wird in Cent angegeben
                'debtorIban'            => $tblInvoiceItemDebtor->getIBAN(),
                'debtorBic'             => $tblInvoiceItemDebtor->getBIC(), // Pflichtfeld?
                'debtorName'            => $tblInvoiceItemDebtor->getOwner(), // Vor / Zuname
                'debtorMandate'         => $tblInvoiceItemDebtor->getBankReference(),
                'debtorMandateSignDate' => $ReferenceDate,
                'remittanceInformation' => $tblInvoiceItemDebtor->getName(),
                //            'endToEndId'            => 'Invoice-No X' // optional, if you want to provide additional structured info
            ));
        }
    }
}