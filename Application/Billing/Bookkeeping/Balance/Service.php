<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
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
            if(isset($Value['PriceMissing'])){
                foreach($Value['PriceMissing'] as $Time => $PriceMissing) {
                    $MonthOpenList[] = new DangerText(Balance::useService()->getPriceString($PriceMissing).' ('.$Time.')');
                }
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

    public function createSepaContent($Month = '', $Year = '')
    {

        $tblInvoiceList = Invoice::useService()->getInvoiceByYearAndMonth($Year, $Month);

        //Set the custom header (Spanish banks example) information
        $header = new GroupHeader(date('Y-m-d-H-i-s'), 'Me');
        $header->setInitiatingPartyId('DE21WVM1234567890');

        $directDebit = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($header, 'pain.008.001.02');

        foreach($tblInvoiceList as $tblInvoice){
            $tblInvoiceCreditor = $tblInvoice->getTblInvoiceCreditor();
             $PaymentId = $tblInvoice->getId().'_PaymentId';
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
                'localInstrumentCode'   => 'CORE' // default. optional.
                // Element dient der Unterscheidung zwischen den einzelenen SEPA Lastschriften:
                //» SEPA CORE Lastschrift
                //» SEPA COR1 Lastschrift
                //» SEPA B2B Lastschrift
            ));
            $tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice);
            if($tblInvoiceItemDebtorList){
                foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
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

        return $directDebit;
    }
}