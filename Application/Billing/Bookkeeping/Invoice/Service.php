<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Person\Person;
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
     * @return bool|Service\Entity\TblInvoice[]
     */
    public function getInvoiceByIsPaid($Check = false)
    {

        return (new Data($this->getBinding()))->getInvoiceByIsPaid($Check);
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
     * @param TblInvoice $tblInvoice
     * @param bool       $IsReversal
     *
     * @return bool
     */
    public function changeInvoiceIsReversal(TblInvoice $tblInvoice, $IsReversal = true)
    {

        return (new Data($this->getBinding()))->changeInvoiceIsReversal($tblInvoice, $IsReversal);
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
     * @return false|TblItem
     */
    public function getItemById($Id)
    {

        return (new Data($this->getBinding()))->getItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorById($Id);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblInvoiceItem[]
     */
    public function getInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getInvoiceItemAllByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|Service\Entity\TblItem[]
     */
    public function getItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getItemAllByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function getTblPersonAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getTblPersonAllByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson
     */
    public function getTblPersonByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getTblPersonByInvoiceAndItem($tblInvoice, $tblItem);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getPriceString($value)
    {

        $result = number_format($value, 2, ',', '.');
        $result .= ' €';
        return $result;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return array|bool
     */
    public function reviewInvoiceData(TblBasket $tblBasket)
    {
        /** Shopping Content */
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerificationList) {
            return false;
        }
        $Invoice = array();
        $ItemCount = 0;
        foreach ($tblBasketVerificationList as $tblBasketVerification) {
            $tblPerson = $tblBasketVerification->getServiceTblPerson();
            $tblItem = $tblBasketVerification->getServiceTblItem();
            $Quantity = $tblBasketVerification->getQuantity();
            $Value = $tblBasketVerification->getValue();
            $Price = $tblBasketVerification->getPrice();
            $PriceSum = $tblBasketVerification->getSummaryPrice();

            /** search Customer */
            $tblDebtorSelect = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            if ($tblDebtorSelect) {
                $tblBankReference = $tblDebtorSelect->getTblBankReference();
                $tblDebtor = $tblDebtorSelect->getTblDebtor();

                if ($tblDebtorSelect->getServiceTblPaymentType()->getName() == 'SEPA-Lastschrift') {
                    $tblPaymentTypeId = $tblDebtorSelect->getServiceTblPaymentType()->getId();
                    $tblPaymentType = $tblDebtorSelect->getServiceTblPaymentType();
                } else {
                    $tblPaymentTypeId = 0;
                    $tblPaymentType = $tblDebtorSelect->getServiceTblPaymentType();
                }
                if ($tblBankReference && $tblDebtor) {
                    /** fill Invoice/tblDebtor */
                    $seperator = $tblDebtor->getId().$tblPaymentTypeId.$tblBankReference;
                    /** fill Invoice/tblItem */
                    $Invoice[$seperator][$ItemCount]['PersonFrom'] = $tblPerson->getFullName();
                    $Invoice[$seperator][$ItemCount]['PersonTo'] = $tblDebtor->getServiceTblPerson()->getFullName();
                    $Invoice[$seperator][$ItemCount]['PaymentType'] = $tblPaymentType->getName();
                    $Invoice[$seperator][$ItemCount]['Item'] = $tblBasketVerification->getServiceTblItem()->getName();
                    $Invoice[$seperator][$ItemCount]['Quantity'] = $Quantity;
                    $Invoice[$seperator][$ItemCount]['Price'] = $Price;
                    $Invoice[$seperator][$ItemCount]['PriceSum'] = $PriceSum;
                    $Invoice[$seperator][$ItemCount]['Value'] = $Value;
                    $ItemCount++;
                } elseif ($tblDebtor) {
                    /** fill Invoice/tblDebtor */
                    $seperator = $tblDebtor->getId().$tblPaymentTypeId.'0';
                    /** fill Invoice/tblItem */
                    $Invoice[$seperator][$ItemCount]['PersonFrom'] = $tblPerson->getFullName();
                    $Invoice[$seperator][$ItemCount]['PersonTo'] = $tblDebtor->getServiceTblPerson()->getFullName();
                    $Invoice[$seperator][$ItemCount]['PaymentType'] = $tblPaymentType->getName();
                    $Invoice[$seperator][$ItemCount]['Item'] = $tblBasketVerification->getServiceTblItem()->getName();
                    $Invoice[$seperator][$ItemCount]['Quantity'] = $Quantity;
                    $Invoice[$seperator][$ItemCount]['Price'] = $Price;
                    $Invoice[$seperator][$ItemCount]['PriceSum'] = $PriceSum;
                    $Invoice[$seperator][$ItemCount]['Value'] = $Value;
                    $ItemCount++;
                }
            }
        }

        return $Invoice;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function createInvoice(TblBasket $tblBasket)
    {
        /** Shopping Content */
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerificationList) {
            return false;
        }
        $DebtorItemList = array();
        foreach ($tblBasketVerificationList as $tblBasketVerification) {


            $tblPerson = $tblBasketVerification->getServiceTblPerson();
            $tblItem = $tblBasketVerification->getServiceTblItem();
            $Quantity = $tblBasketVerification->getQuantity();
            $Price = $tblBasketVerification->getValue();

            /** search Customer */
            $tblDebtorSelect = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            if ($tblDebtorSelect) {
                $tblBankReference = $tblDebtorSelect->getTblBankReference();
                $tblDebtor = $tblDebtorSelect->getTblDebtor();

                if ($tblBankReference && $tblDebtor) {
                    /** fill Invoice/tblDebtor */
                    $DebtorInvoiceId = (new Data($this->getBinding()))->createDebtor($tblDebtor, $tblBankReference)->getId();
                    /** fill Invoice/tblItem */
                    $DebtorItemList[$DebtorInvoiceId]['Item'][] = (new Data($this->getBinding()))->createItem($tblBasketVerification)->getId();
                    $DebtorItemList[$DebtorInvoiceId]['Quantity'][] = $Quantity;
                    $DebtorItemList[$DebtorInvoiceId]['Value'][] = $Price;
                    $DebtorItemList[$DebtorInvoiceId]['PersonId'][] = $tblPerson->getId();
                } elseif ($tblDebtor) {
                    /** fill Invoice/tblDebtor */
                    $DebtorInvoiceId = (new Data($this->getBinding()))->createDebtor($tblDebtor, null)->getId();
                    /** fill Invoice/tblItem */
                    $DebtorItemList[$DebtorInvoiceId]['Item'][] = (new Data($this->getBinding()))->createItem($tblBasketVerification)->getId();
                    $DebtorItemList[$DebtorInvoiceId]['Quantity'][] = $Quantity;
                    $DebtorItemList[$DebtorInvoiceId]['Value'][] = $Price;
                    $DebtorItemList[$DebtorInvoiceId]['PersonId'][] = $tblPerson->getId();
                }
            }
        }


        /** ToDO Rechnungsnummer übergeben */
        $tblInvoiceList = Invoice::useService()->getInvoiceAll();
        $count = Count($tblInvoiceList);

        /** fill Invoice/tblInvoice */
        foreach ($DebtorItemList as $DebtorInvoiceId => $DebtorBill) {
            $count++;
            $tblDebtor = Invoice::useService()->getDebtorById($DebtorInvoiceId);

            if ($tblDebtor) {
                $tblPersonDebtor = $tblDebtor->getServiceTblDebtor()->getServiceTblPerson();
                if ($tblPersonDebtor) {

                    //get Address
                    $tblAddress = Address::useService()->getAddressByPerson($tblPersonDebtor);
                    if (!$tblAddress) {
                        $tblAddress = null;
                    }
                    //get Mail
                    $tblMail = null;
                    $tblToPersonListMail = Mail::useService()->getMailAllByPerson($tblPersonDebtor);
                    if ($tblToPersonListMail) {
                        $tblMail = $tblToPersonListMail[0]->getTblMail();
                    }
                    //get Phone
                    $tblPhone = null;
                    $tblToPersonListPhone = Phone::useService()->getPhoneAllByPerson($tblPersonDebtor);
                    if ($tblToPersonListPhone) {
                        $tblPhone = $tblToPersonListPhone[0]->getTblPhone();
                    }

                    $InvoiceId = (new Data($this->getBinding()))->createInvoice($tblDebtor, $count, $tblAddress, $tblMail, $tblPhone)->getId();
                    $DebtorItemList[$DebtorInvoiceId]['InvoiceId'] = $InvoiceId;
                }
            }
        }

        /** fill Invoice/tblInvoiceItem */
        foreach ($DebtorItemList as $DebtorBill) {
            $tblInvoice = Invoice::useService()->getInvoiceById($DebtorBill['InvoiceId']);

            foreach ($DebtorBill['Item'] as $key => $Item) {

                $tblPerson = Person::useService()->getPersonById($DebtorBill['PersonId'][$key]);
                if ($tblPerson) {
                    $tblItem = Invoice::useService()->getItemById($Item);
                    (new Data($this->getBinding()))->createInvoiceItem($tblInvoice, $tblItem, $tblPerson);
                }
            }
        }
        return true;
    }
}
