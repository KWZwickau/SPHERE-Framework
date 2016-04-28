<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
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

    public function getItemByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getItemByInvoice($tblInvoice);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function createInvoice(TblBasket $tblBasket)
    {

        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerificationList) {
            return false;
        }

        foreach ($tblBasketVerificationList as $tblBasketVerification) {
            $tblDebtorInvoice = false;
            $tblItemInvoice = false;

            $tblPerson = $tblBasketVerification->getServiceTblPerson();
            $tblItem = $tblBasketVerification->getServiceTblItem();
//            $Quantity = $tblBasketVerification->getQuantity();
//            $Price = $tblBasketVerification->getValue();

            $tblDebtorSelect = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            if ($tblDebtorSelect) {
                $tblBankReference = $tblDebtorSelect->getTblBankReference();
                $tblDebtor = $tblDebtorSelect->getTblDebtor();

                if ($tblBankReference && $tblDebtor) {
                    $tblDebtorInvoice = (new Data($this->getBinding()))->createDebtor($tblDebtor, $tblBankReference);
                }
            }
            $tblItemInvoice = (new Data($this->getBinding()))->createTblItem($tblBasketVerification);
        }

        return true;
    }
}
