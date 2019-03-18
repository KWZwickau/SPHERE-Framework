<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
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

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        return (new Data($this->getBinding()))->getInvoiceAll();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByPersonCauser(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getInvoiceByPersonCauser($tblPerson);
    }

    /**
     * @param $Year
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByYear($Year = '')
    {

        return (new Data($this->getBinding()))->getInvoiceAllByYear($Year);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     * @param string    $Year
     * @param string    $Month
     *
     * @return TblInvoice[]|bool
     */
    public function getInvoiceByPersonCauserAndItemAndYearAndMonth(
        TblPerson $tblPerson,
        TblItem $tblItem,
        $Year,
        $Month
    ){
        $isInvoice = false;
        if(($tblInvoiceList = $this->getInvoiceAllByPersonCauser($tblPerson))){
            foreach($tblInvoiceList as $tblInvoice) {
                if($tblInvoice->getYear() == $Year && $tblInvoice->getMonth() == $Month){
                    if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                        foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                            if(($tblInvoiceItem = $tblInvoiceItemDebtor->getServiceTblItem())){
                                if($tblInvoiceItem->getId() == $tblItem->getId()){
                                    $isInvoice = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $isInvoice;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorByInvoiceAndItem($tblInvoice, $tblItem);
    }

    /**
     * @param TblInvoiceItemDebtor $tblInvoiceItemDebtor
     * @param bool                 $IsPaid
     *
     * @return bool
     */
    public function changeInvoiceItemDebtorIsPaid(TblInvoiceItemDebtor $tblInvoiceItemDebtor, $IsPaid = false)
    {

        return (new Data($this->getBinding()))->changeInvoiceItemDebtorIsPaid($tblInvoiceItemDebtor, $IsPaid);
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceCreditorById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceCreditorById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblInvoiceItemDebtor
     */
    public function getInvoiceItemDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorById($Id);
    }

    /**
     * @param bool $IsPaid
     *
     * @return false|TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByIsPaid($IsPaid = false)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorByIsPaid($IsPaid);
    }

    /**
     * @param string $Year
     * @param string $Month
     * @param string $BasketName
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByYearAndMonth($Year, $Month, $BasketName = '')
    {

        return (new Data($this->getBinding()))->getInvoiceByYearAndMonth($Year, $Month, $BasketName);
    }

    /**
     * @param $IntegerNumber
     * @param $Year
     * @param $Month
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceByIntegerAndYearAndMonth($IntegerNumber, $Year, $Month)
    {

        return (new Data($this->getBinding()))->getInvoiceByIntegerAndYearAndMonth($IntegerNumber, $Year, $Month);
    }

    /**
     * @param $Year
     * @param $Month
     *
     * @return int
     */
    public function getMaxInvoiceNumberByYearAndMonth($Year, $Month)
    {

        return (new Data($this->getBinding()))->getMaxInvoiceNumberByYearAndMonth($Year, $Month);
    }

    /**
     * @param int $YearsAgo
     * @param int $YearFuture
     *
     * @return array
     */
    public function getYearList($YearsAgo = 3, $YearFuture = 1)
    {

        $Now = new \DateTime();
        $Year = $Now->format('Y');
        $YearList = array();
        for($i = $YearsAgo; $i > 0; $i--) {
            $YearList[(int)$Year - $i] = (int)$Year - $i;
        }
        for($j = 0; $j <= $YearFuture; $j++) {
            $YearList[(int)$Year + $j] = (int)$Year + $j;
        }


        return $YearList;
    }

    /**
     * @param null $From
     * @param null $To
     *
     * @return array
     */
    public function getMonthList($From = null, $To = null)
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
        // Zeitraum eingrenzen
        if($From !== null & $To !== null){
            foreach($MonthList as $Key => &$Month) {
                if($Key < $From || $Key > $To){
                    $Month = false;
                }
            }
            $MonthList = array_filter($MonthList);
        }

        return $MonthList;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|Redirect
     */
    public function createInvoice(TblBasket $tblBasket)
    {
        /** Shopping Content */
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket);
        if(!$tblBasketVerificationList){
            return false;
        }

        $DebtorInvoiceList = array();
        $Month = $tblBasket->getMonth();
        $Year = $tblBasket->getYear();
        $TargetTime = $tblBasket->getTargetTime();
        $BasketName = $tblBasket->getName();
        $MaxInvoiceNumber = Invoice::useService()->getMaxInvoiceNumberByYearAndMonth($Year, $Month);

        // First Look and remove existing Invoice
        // entfernen aller DebtorSelection zu welchen es schon in der aktuellen Rechnungsphase Rechnungen gibt.
        array_walk($tblBasketVerificationList,
            function(TblBasketVerification &$tblBasketVerification) use ($Month, $Year){
                $tblPerson = $tblBasketVerification->getServiceTblPersonCauser();
                $tblItem = $tblBasketVerification->getServiceTblItem();

                // entfernen der Beiträge mit 0€
                if($tblBasketVerification->getValue() === '0.0000'){
                    $tblBasketVerification = false;
                } elseif($tblPerson && $tblItem) { // Entfernen aller Beiträge
                    if(Invoice::useService()->getInvoiceByPersonCauserAndItemAndYearAndMonth($tblPerson, $tblItem,
                        $Year, $Month)){
                        // Entfernen des Beitrag's aus der Abrechnung
                        Basket::useService()->destroyBasketVerification($tblBasketVerification);
                        // Rechnung vorhanden -> keine neue Rechnung anlegen!
                        $tblBasketVerification = false;
                    }
                }
            });
        /** @var TblBasketVerification[] $tblBasketVerificationList */
        $tblBasketVerificationList = array_filter($tblBasketVerificationList);

        /** fill Invoice/Creditor */
        if(($tblCreditor = $tblBasket->getServiceTblCreditor())){
            $CreditorId = $tblCreditor->getCreditorId();
            $Owner = $tblCreditor->getOwner();
            $BankName = $tblCreditor->getBankName();
            $IBAN = $tblCreditor->getIBAN();
            $BIC = $tblCreditor->getBIC();
        } else {
            $tblCreditor = null;
            $CreditorId = $Owner = $BankName = $IBAN = $BIC = '';
        }
        $InvoiceCreditorList['CreditorId'] = $CreditorId;
        $InvoiceCreditorList['Owner'] = $Owner;
        $InvoiceCreditorList['BankName'] = $BankName;
        $InvoiceCreditorList['IBAN'] = $IBAN;
        $InvoiceCreditorList['BIC'] = $BIC;
        $InvoiceCreditorList['serviceTblCreditor'] = $tblCreditor;

        $tblInvoiceCreditor = (new Data($this->getBinding()))->createInvoiceCreditorList($InvoiceCreditorList);

        // Vorbereiten aller Rechnugen
        array_walk($tblBasketVerificationList,
            function(TblBasketVerification $tblBasketVerification) use (
                &$MaxInvoiceNumber,
                &$DebtorInvoiceList,
                $tblInvoiceCreditor
            ){
                $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
                $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
                /** fill Invoice (groupByDebtorAndCauser) */
                $PersonDebtorId = $tblPersonDebtor->getId();
                $PersonCauserId = $tblPersonCauser->getId();

                $GroupString = $PersonDebtorId.'#'.$PersonCauserId;
                /** Fill InvoiceList */
                if(!isset($DebtorInvoiceList[$GroupString])){
                    $MaxInvoiceNumber++;
                    $DebtorInvoiceList[$GroupString]['Identifier'] = $MaxInvoiceNumber;
                    $DebtorInvoiceList[$GroupString]['servicePersonCauser'] = $tblPersonCauser;
                    $DebtorInvoiceList[$GroupString]['InvoiceCreditor'] = $tblInvoiceCreditor;
                }
            });

        if(!empty($DebtorInvoiceList)){
            // Erstellen aller Rechnungen im Bulk
            (new Data($this->getBinding()))->createInvoiceList($DebtorInvoiceList, $Month, $Year, $TargetTime,
                $BasketName);
        }

        $InvoiceItemDebtorList = array();
        $i = 0;
        array_walk($tblBasketVerificationList, function(TblBasketVerification $tblBasketVerification) use (
            &$i,
            &$InvoiceItemDebtorList,
            $Month,
            $Year,
            $DebtorInvoiceList
        ){
            $i++;
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            /** get Invoice from BulkSave */
            $PersonDebtorId = $tblPersonDebtor->getId();
            $PersonCauserId = $tblPersonCauser->getId();
            $GroupString = $PersonDebtorId.'#'.$PersonCauserId;
            $tblInvoice = $this->getInvoiceByIntegerAndYearAndMonth($DebtorInvoiceList[$GroupString]['Identifier'],
                $Year, $Month);
            /** fill Invoice/ItemDebtor */
            if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                $Name = $tblItem->getName();
                $Description = $tblItem->getDescription();
            } else {
                $tblItem = null;
                $Name = $Description = '';
            }
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            $tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor);
            if($tblDebtorNumberList && !empty($tblDebtorNumberList)){
                // ToDO mehrere Debitoren-Nr.?
                // erste Debtitor Nummer wird gezogen
                $tblDebtorNumber = current($tblDebtorNumberList);
                $DebtorNumber = $tblDebtorNumber->getDebtorNumber();
            } else {
                $DebtorNumber = '';
            }
            if(($tblBankReference = $tblBasketVerification->getServiceTblBankReference())){
                $Reference = $tblBankReference->getReferenceNumber();
            } else {
                $tblBankReference = null;
                $Reference = '';
            }
            if(($tblBankAccount = $tblBasketVerification->getServiceTblBankAccount())){
                $Owner = $tblBankAccount->getOwner();
                $BankName = $tblBankAccount->getBankName();
                $IBAN = $tblBankAccount->getIBAN();
                $BIC = $tblBankAccount->getBIC();
            } else {
                $tblBankAccount = null;
                $Owner = $BankName = $IBAN = $BIC = '';
            }
            if(!($tblPaymentType = $tblBasketVerification->getServiceTblPaymentType())){
                $tblPaymentType = null;
            }
            $InvoiceItemDebtorList[$GroupString][$i]['Invoice'] = $tblInvoice;
            $InvoiceItemDebtorList[$GroupString][$i]['Name'] = $Name;
            $InvoiceItemDebtorList[$GroupString][$i]['Description'] = $Description;
            $InvoiceItemDebtorList[$GroupString][$i]['Value'] = $tblBasketVerification->getValue();
            $InvoiceItemDebtorList[$GroupString][$i]['Quantity'] = $tblBasketVerification->getQuantity();
            $InvoiceItemDebtorList[$GroupString][$i]['Invoice'] = $tblInvoice;
            $InvoiceItemDebtorList[$GroupString][$i]['DebtorNumber'] = $DebtorNumber;
            $InvoiceItemDebtorList[$GroupString][$i]['BankReference'] = $Reference;
            $InvoiceItemDebtorList[$GroupString][$i]['Owner'] = $Owner;
            $InvoiceItemDebtorList[$GroupString][$i]['BankName'] = $BankName;
            $InvoiceItemDebtorList[$GroupString][$i]['IBAN'] = $IBAN;
            $InvoiceItemDebtorList[$GroupString][$i]['BIC'] = $BIC;
            $InvoiceItemDebtorList[$GroupString][$i]['TblItem'] = $tblItem;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblPersonDebtor'] = $tblPersonDebtor;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblBankReference'] = $tblBankReference;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblBankAccount'] = $tblBankAccount;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblPaymentType'] = $tblPaymentType;
        });

        // create
        if(!empty($InvoiceItemDebtorList)){
            (new Data($this->getBinding()))->createInvoiceItemDebtorList($InvoiceItemDebtorList);
        }

        // Abrechnung leeren
//        foreach ($tblBasketVerificationList as $tblBasketVerification) {
//            Basket::useService()->destroyBasketVerification($tblBasketVerification);
//        }
        // Set Basket to Done
        Basket::useService()->changeBasketDone($tblBasket);

        $Invoice = array('Year' => $tblBasket->getYear(), 'Month' => $tblBasket->getMonth());
        return new Redirect('/Billing/Bookkeeping/Invoice/View', Redirect::TIMEOUT_SUCCESS, array(
            'Invoice' => $Invoice
        ));
    }
}
