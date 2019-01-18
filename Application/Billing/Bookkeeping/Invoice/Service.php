<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
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
    public function getInvoiceAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getInvoiceByPersonCauser($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     * @param string    $Year
     * @param string    $Month
     *
     * @return TblInvoice[]|bool
     */
    public function getInvoiceByPersonCauserAndItemAndYearAndMonth(TblPerson $tblPerson, TblItem $tblItem, $Year, $Month)
    {
        $isInvoice = false;
        if(($tblInvoiceList = $this->getInvoiceAllByPerson($tblPerson))){
            foreach($tblInvoiceList as $tblInvoice){
                if($tblInvoice->getYear() == $Year && $tblInvoice->getMonth() == $Month){
                    if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                        foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
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
     * @param $Year
     * @param $Month
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByYearAndMonth($Year, $Month)
    {

        return (new Data($this->getBinding()))->getInvoiceByYearAndMonth($Year, $Month);
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
     * @param TblBasket $tblBasket
     *
     * @return bool|Redirect
     */
    public function createInvoice(TblBasket $tblBasket)
    {
        /** Shopping Content */
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket);
        if (!$tblBasketVerificationList) {
            return false;
        }

        // Set Basket to Done
        Basket::useService()->changeBasketDone($tblBasket);

        $DebtorInvoiceList = array();
        $Month = $tblBasket->getMonth();
        $Year = $tblBasket->getYear();
        $TargetTime = $tblBasket->getTargetTime();
        $MaxInvoiceNumber = Invoice::useService()->getMaxInvoiceNumberByYearAndMonth($Year, $Month);

        // First Look and remove existing Invoice
        // entfernen aller DebtorSelection zu welchen es schon in der aktuellen Rechnungsphase Rechnungen gibt.
        foreach($tblBasketVerificationList as &$tblBasketVerification){
            $tblPerson = $tblBasketVerification->getServiceTblPersonCauser();
            $tblItem = $tblBasketVerification->getServiceTblItem();
            if($tblPerson && $tblItem){
                if(Invoice::useService()->getInvoiceByPersonCauserAndItemAndYearAndMonth($tblPerson, $tblItem, $Year, $Month)) {
                    // Entfernen des Beitrag's aus der Abrechnung
                    Basket::useService()->destroyBasketVerification($tblBasketVerification);
                    // Rechnung vorhanden -> keine neue Rechnung anlegen!
                    $tblBasketVerification = false;
                }
            }
        }
        /** @var TblBasketVerification[] $tblBasketVerificationList */
        $tblBasketVerificationList = array_filter($tblBasketVerificationList);

        /** fill Invoice/Creditor */
        //ToDO choose Creditor
        if(($tblCreditorList = Creditor::useService()->getCreditorAll())){
            $tblCreditor = current($tblCreditorList);
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
        foreach($tblBasketVerificationList as $tblBasketVerification){
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            /** fill Invoice (groupByDebtorAndCauser) */
            $PersonDebtorId = $tblPersonDebtor->getId();
            $PersonCauserId = $tblPersonCauser->getId();
            $GroupString = $PersonDebtorId.'-'.$PersonCauserId;
            /** Fill InvoiceList */
            if(!isset($DebtorInvoiceList[$GroupString])){
                $MaxInvoiceNumber++;
                $DebtorInvoiceList[$GroupString]['Identifier'] = $MaxInvoiceNumber;
                $DebtorInvoiceList[$GroupString]['servicePersonCauser'] = $tblPersonCauser;
                $DebtorInvoiceList[$GroupString]['InvoiceCreditor'] = $tblInvoiceCreditor;
            }
        }
        if(!empty($DebtorInvoiceList)){
            // Erstellen aller Rechnungen im Bulk
            (new Data($this->getBinding()))->createInvoiceList($DebtorInvoiceList, $Month, $Year, $TargetTime);
        }

        $InvoiceItemDebtorList = array();
        $i = 0;
        foreach ($tblBasketVerificationList as $tblBasketVerification) {
            $i++;
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            /** get Invoice from BulkSave */
            $PersonDebtorId = $tblPersonDebtor->getId();
            $PersonCauserId = $tblPersonCauser->getId();
            $GroupString = $PersonDebtorId.'-'.$PersonCauserId;
            $tblInvoice = $this->getInvoiceByIntegerAndYearAndMonth($DebtorInvoiceList[$GroupString]['Identifier'], $Year, $Month);
            /** fill Invoice/ItemDebtor */
            if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                $Name = $tblItem->getName();
                $Description = $tblItem->getDescription();
            } else {
                $tblItem = null;
                $Name = $Description = '';
            }
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            $DebtorNumber = Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor);
            if($DebtorNumber && !empty($DebtorNumber)){
                // ToDO mehrere Debit.-Nr.?
                // erste Debtitor Nummer wird gezogen
                $DebtorNumber = current($DebtorNumber);
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
        }

        // create
        if(!empty($InvoiceItemDebtorList)){
            (new Data($this->getBinding()))->createInvoiceItemDebtorList($InvoiceItemDebtorList);
        }

        // ToDO Warenkorb deaktivieren nicht leeren
        // Warenkorb leeren
//        foreach ($tblBasketVerificationList as $tblBasketVerification) {
//            Basket::useService()->destroyBasketVerification($tblBasketVerification);
//        }

        $Invoice = array('Year' => $tblBasket->getYear(), 'Month' => $tblBasket->getMonth());
        return new Redirect('/Billing/Bookkeeping/InvoiceView', Redirect::TIMEOUT_SUCCESS, array(
            'Invoice' => $Invoice
        ));
    }
}
