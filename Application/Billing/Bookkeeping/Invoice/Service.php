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
     * @param $value
     *
     * @return string
     */
    public function getPriceString($value)
    {

        $result = number_format($value, 2, ',', '.');
        $result .= '€';
        return $result;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function createInvoice(TblBasket $tblBasket)
    {
        /** Warenkorb Inhalt */
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

            /** Bezahler suchen */
            $tblDebtorSelect = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            if ($tblDebtorSelect) {
                $tblBankReference = $tblDebtorSelect->getTblBankReference();
                $tblDebtor = $tblDebtorSelect->getTblDebtor();

                if ($tblBankReference && $tblDebtor) {
                    /** Invoice/tblDebtor füllen */
                    $DebtorInvoiceId = (new Data($this->getBinding()))->createDebtor($tblDebtor, $tblBankReference)->getId();
                    /** Invoice/tblItem füllen */
                    $DebtorItemList[$DebtorInvoiceId]['Item'][] = (new Data($this->getBinding()))->createItem($tblBasketVerification)->getId();
                    $DebtorItemList[$DebtorInvoiceId]['Quantity'][] = $Quantity;
                    $DebtorItemList[$DebtorInvoiceId]['Value'][] = $Price;
                }
            }
        }


        /** ToDO Rechnungsnummer übergeben */
        $tblInvoiceList = Invoice::useService()->getInvoiceAll();
        $count = Count($tblInvoiceList);

        /** Invoice/tblInvoice und Invoice/ füllen */
        foreach ($DebtorItemList as $DebtorInvoiceId => $DebtorBill) {
            $count++;
            $tblDebtor = Invoice::useService()->getDebtorById($DebtorInvoiceId);

            if ($tblDebtor) {
                $tblPerson = $tblDebtor->getServiceTblDebtor()->getServiceTblPerson();
                if ($tblPerson) {

                    //get Address
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    if (!$tblAddress) {
                        $tblAddress = null;
                    }
                    //get Mail
                    $tblMail = null;
                    $tblToPersonListMail = Mail::useService()->getMailAllByPerson($tblPerson);
                    if ($tblToPersonListMail) {
                        $tblMail = $tblToPersonListMail[0]->getTblMail();
                    }
                    //get Phone
                    $tblPhone = null;
                    $tblToPersonListPhone = Phone::useService()->getPhoneAllByPerson($tblPerson);
                    if ($tblToPersonListPhone) {
                        $tblPhone = $tblToPersonListPhone[0]->getTblPhone();
                    }

                    $InvoiceId = (new Data($this->getBinding()))->createInvoice($tblDebtor, $count, $tblAddress, $tblMail, $tblPhone)->getId();
                    $DebtorItemList[$DebtorInvoiceId]['InvoiceId'] = $InvoiceId;
                }
            }
        }

        foreach ($DebtorItemList as $DebtorBill) {
            $tblInvoice = Invoice::useService()->getInvoiceById($DebtorBill['InvoiceId']);

            foreach ($DebtorBill['Item'] as $Item) {
//                print_r(' '.$Item.' - '.$DebtorBill['InvoiceId'].'<br/>');
                $tblItem = Invoice::useService()->getItemById($Item);
                (new Data($this->getBinding()))->createInvoiceItem($tblInvoice, $tblItem);
            }
        }

        return true;
    }
}
