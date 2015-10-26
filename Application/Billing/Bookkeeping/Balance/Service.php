<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblBalance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\System\Database\Binding\AbstractService;

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
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblPayment[]
     */
    public function checkPaymentFromDebtorExistsByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->getBinding()))->checkPaymentFromDebtorExistsByDebtor($tblDebtor);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblBalance
     */
    public function getBalanceByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getBalanceByInvoice($tblInvoice);
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return float
     */
    public function sumPriceItemByBalance(TblBalance $tblBalance)
    {

        return (new Data($this->getBinding()))->sumPriceItemByBalance($tblBalance);
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return string
     */
    public function sumPriceItemStringByBalance(TblBalance $tblBalance)
    {

        return (new Data($this->getBinding()))->sumPriceItemStringByBalance($tblBalance);
    }

    /**
     * @param TblDebtor  $serviceBilling_Banking
     * @param TblInvoice $serviceBilling_Invoice
     * @param            $ExportDate
     * @param            $BankName
     * @param            $IBAN
     * @param            $BIC
     * @param            $Owner
     * @param            $CashSign
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

        return (new Data($this->getBinding()))->createBalance($serviceBilling_Banking, $serviceBilling_Invoice,
            $ExportDate,
            $BankName,
            $IBAN,
            $BIC,
            $Owner,
            $CashSign);
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceHasFullPaymentAll()
    {

        return (new Data($this->getBinding()))->getInvoiceHasFullPaymentAll();
    }

    /**
     * @return bool|TblPayment[]
     */
    public function getPaymentAll()
    {

        return (new Data($this->getBinding()))->getPaymentAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblBalance
     */
    public function getBalanceById($Id)
    {

        return (new Data($this->getBinding()))->getBalanceById($Id);
    }
}
