<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblBalance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblBalance
     */
    public function getBalanceById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblBalance', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblBalance
     */
    public function getBalanceByInvoice(TblInvoice $tblInvoice)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblBalance')->findOneBy(
            array(TblBalance::ATTR_SERVICE_BILLING_INVOICE => $tblInvoice->getId())
        );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPayment
     */
    public function getPaymentById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblPayment', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblPayment[]
     */
    public function getPaymentAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblPayment')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceHasFullPaymentAll()
    {

        $invoiceHasFullPaymentAll = array();
        $balanceAll = $this->getBalanceAll();
        if ($balanceAll) {
            foreach ($balanceAll as $balance) {
                $invoice = $balance->getServiceBillingInvoice();
                $sumInvoicePrice = Invoice::useService()->sumPriceItemAllByInvoice($invoice);
                $sumPaymentPrice = $this->sumPriceItemByBalance($balance);

                $sumInvoicePrice = round($sumInvoicePrice, 2);
                $sumPaymentPrice = round($sumPaymentPrice, 2);

                if ($sumInvoicePrice <= $sumPaymentPrice) {
                    $invoiceHasFullPaymentAll[] = $invoice;
                }
            }
        }

        return ( empty( $invoiceHasFullPaymentAll ) ? false : $invoiceHasFullPaymentAll );
    }

    /**
     * @return bool|TblBalance[]
     */
    public function getBalanceAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblBalance')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return float
     */
    public function sumPriceItemByBalance(TblBalance $tblBalance)
    {

        $sum = 0.00;
        $tblPaymentList = $this->getPaymentByBalance($tblBalance);
        foreach ($tblPaymentList as $tblPayment) {
            $sum += $tblPayment->getValue();
        }

        return $sum;
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return bool|TblPayment[]
     */
    public function getPaymentByBalance(TblBalance $tblBalance)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblPayment')->findBy(
            array(TblPayment::ATTR_TBL_BALANCE => $tblBalance->getId())
        );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceHasExportDateAll()
    {

        $invoiceHasExportDateAll = array();
        $balanceAll = $this->getBalanceAll();
        if ($balanceAll) {
            foreach ($balanceAll as $balance) {
                $invoice = $balance->getServiceBillingInvoice();
                $BalanceDate = $balance->getExportDate();

                if ($BalanceDate !== false) {
                    $invoiceHasExportDateAll[] = $invoice;
                }
            }
        }

        return ( empty( $invoiceHasExportDateAll ) ? false : $invoiceHasExportDateAll );
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return string
     */
    public function sumPriceItemStringByBalance(TblBalance $tblBalance)
    {

        return str_replace('.', ',', round($this->sumPriceItemByBalance($tblBalance), 2))." €";
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function checkPaymentFromDebtorExistsByDebtor(TblDebtor $tblDebtor)
    {

        /** @var TblBalance[] $balanceAllByDebtor */
        $balanceAllByDebtor = $this->getConnection()->getEntityManager()->getEntity('TblBalance')->findBy(
            array(TblBalance::ATTR_SERVICE_BILLING_BANKING => $tblDebtor->getId())
        );
        foreach ($balanceAllByDebtor as $balance) {
            $Entity = $this->getConnection()->getEntityManager()->getEntity('TblPayment')->findOneBy(
                array(TblPayment::ATTR_TBL_BALANCE => $balance->getId())
            );
            if ($Entity !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblDebtor  $serviceBilling_Banking
     * @param TblInvoice $serviceBilling_Invoice
     * @param            $ExportDate
     * @param null       $BankName
     * @param null       $IBAN
     * @param null       $BIC
     * @param null       $Owner
     * @param null       $CashSign
     *
     * @return bool
     */
    public function createBalance(
        TblDebtor $serviceBilling_Banking,
        TblInvoice $serviceBilling_Invoice,
        $ExportDate = null,
        $BankName = null,
        $IBAN = null,
        $BIC = null,
        $Owner = null,
        $CashSign = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBalance')->findOneBy(array(
            TblBalance::ATTR_SERVICE_BILLING_BANKING => $serviceBilling_Banking->getId(),
            TblBalance::ATTR_SERVICE_BILLING_INVOICE => $serviceBilling_Invoice->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblBalance();
            $Entity->setServiceBillingBanking($serviceBilling_Banking);
            $Entity->setServiceBillingInvoice($serviceBilling_Invoice);
            if ($ExportDate !== null) {
                $Entity->setExportDate($ExportDate);
            }
            if ($BankName !== null) {
                $Entity->setBankName($BankName);
            }
            if ($IBAN !== null) {
                $Entity->setIBAN($IBAN);
            }
            if ($BIC !== null) {
                $Entity->setBIC($BIC);
            }
            if ($Owner !== null) {
                $Entity->setOwner($Owner);
            }
            if ($CashSign !== null) {
                $Entity->setCashSign($CashSign);
            }
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return bool
     */
    public function createSetExportDateBalance(TblBalance $tblBalance)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblBalance $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblBalance->getId());
        $Protocol = clone $Entity;

        if (null !== $Entity) {
            $Entity->setExportDate(new \DateTime('now'));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return bool
     */
    public function removeBalance(TblBalance $tblBalance)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBalance')->findOneBy(
            array('Id' => $tblBalance->getId())
        );

        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblBalance $tblBalance
     * @param            $Value
     * @param \DateTime  $Date
     *
     * @return TblPayment|null|object
     */
    public function createPayment(TblBalance $tblBalance, $Value, \DateTime $Date)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPayment')->findOneBy(array(
            'tblBalance' => $tblBalance->getId(),
            'Value'      => $Value,
            'Date'       => $Date
        ));

        if (null === $Entity) {
            $Entity = new TblPayment();
            $Entity->setTblBalance($tblBalance);
            $Entity->setValue($Value);
            $Entity->setDate($Date);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPayment $tblPayment
     *
     * @return bool
     */
    public function removePayment(TblPayment $tblPayment)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPayment')->findOneBy(
            array('Id' => $tblPayment->getId())
        );

        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
