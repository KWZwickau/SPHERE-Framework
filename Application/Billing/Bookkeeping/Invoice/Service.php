<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
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
     * @param bool $Check
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByIsPaid($Check = true)
    {

        return (new Data($this->getBinding()))->getInvoiceByIsPaid($Check);
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
     * @param bool       $IsPaid
     *
     * @return bool
     */
    public function changeInvoiceIsPaid(TblInvoice $tblInvoice, $IsPaid = true)
    {

        return (new Data($this->getBinding()))->changeInvoiceIsPaid($tblInvoice, $IsPaid);
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
        $DebtorInvoiceList = array();
        $Month = $tblBasket->getMonth();
        $Year = $tblBasket->getYear();
        $TargetTime = $tblBasket->getTargetTime();
        $MaxInvoiceNumber = Invoice::useService()->getMaxInvoiceNumberByYearAndMonth($Year, $Month);

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
                $DebtorInvoiceList[$GroupString] = $MaxInvoiceNumber;
            }
        }
        if(!empty($DebtorInvoiceList)){
            // Erstellen aller Rechnungen im Bulk
            (new Data($this->getBinding()))->createInvoiceList($DebtorInvoiceList, $Month, $Year, $TargetTime);
        }

        $InvoiceCauserList = array();
        $InvoiceItemDebtorList = array();
        $InvoiceCreditorList = array();
        $i = 0;
        foreach ($tblBasketVerificationList as $tblBasketVerification) {
            $i++;
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            /** get Invoice from BulkSave */
            $PersonDebtorId = $tblPersonDebtor->getId();
            $PersonCauserId = $tblPersonCauser->getId();
            $GroupString = $PersonDebtorId.'-'.$PersonCauserId;
            $tblInvoice = $this->getInvoiceByIntegerAndYearAndMonth($DebtorInvoiceList[$GroupString], $Year, $Month);
            /** fill Invoice/Causer */
            if(!($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())){
                $tblPersonCauser = null;
            }
            $InvoiceCauserList[$GroupString]['Invoice'] = $tblInvoice;
            $InvoiceCauserList[$GroupString]['servicePersonCauser'] = $tblPersonCauser;
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
                // ToDO mehrere Debitor-Nummern?
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
            /** fill Invoice/Creditor */
            //ToDO choose Creditor
            if(($tblCreditor = Creditor::useService()->getCreditorById(1))){
                $CreditorId = $tblCreditor->getCreditorId();
                $Owner = $tblCreditor->getOwner();
                $BankName = $tblCreditor->getBankName();
                $IBAN = $tblCreditor->getIBAN();
                $BIC = $tblCreditor->getBIC();
            } else {
                $tblCreditor = null;
                $CreditorId = $Owner = $BankName = $IBAN = $BIC = '';
            }
            $InvoiceCreditorList[$GroupString]['Invoice'] = $tblInvoice;
            $InvoiceCreditorList[$GroupString]['CreditorId'] = $CreditorId;
            $InvoiceCreditorList[$GroupString]['Owner'] = $Owner;
            $InvoiceCreditorList[$GroupString]['BankName'] = $BankName;
            $InvoiceCreditorList[$GroupString]['IBAN'] = $IBAN;
            $InvoiceCreditorList[$GroupString]['BIC'] = $BIC;
            $InvoiceCreditorList[$GroupString]['serviceTblCreditor'] = $tblCreditor;
        }

        // create
        if(!empty($InvoiceCauserList)){
            (new Data($this->getBinding()))->createInvoiceCauserList($InvoiceCauserList);
        }
        if(!empty($InvoiceItemDebtorList)){
            (new Data($this->getBinding()))->createInvoiceItemDebtorList($InvoiceItemDebtorList);
        }
        if(!empty($InvoiceCreditorList)){
            (new Data($this->getBinding()))->createInvoiceCreditorList($InvoiceCreditorList);
        }

        // ToDO Warenkorb deaktivieren nicht leeren
        // Warenkorb leeren
//        foreach ($tblBasketVerificationList as $tblBasketVerification) {
//            Basket::useService()->destroyBasketVerification($tblBasketVerification);
//        }

        return new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_SUCCESS);
    }
}
