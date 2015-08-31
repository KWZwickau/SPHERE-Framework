<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblBalance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\IServiceInterface;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
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

        return (new Data($this->Binding))->checkPaymentFromDebtorExistsByDebtor($tblDebtor);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblBalance
     */
    public function entityBalanceByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->Binding))->entityBalanceByInvoice($tblInvoice);
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return float
     */
    public function sumPriceItemByBalance(TblBalance $tblBalance)
    {

        return (new Data($this->Binding))->sumPriceItemByBalance($tblBalance);
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return string
     */
    public function sumPriceItemStringByBalance(TblBalance $tblBalance)
    {

        return (new Data($this->Binding))->sumPriceItemStringByBalance($tblBalance);
    }

    /**
     * @param TblDebtor  $serviceBilling_Banking
     * @param TblInvoice $serviceBilling_Invoice
     * @param            $ExportDate
     *
     * @return bool
     */
    public function actionCreateBalance(
        TblDebtor $serviceBilling_Banking,
        TblInvoice $serviceBilling_Invoice,
        $ExportDate
    ) {

        return (new Data($this->Binding))->actionCreateBalance($serviceBilling_Banking, $serviceBilling_Invoice,
            $ExportDate);
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function entityInvoiceHasFullPaymentAll()
    {

        return (new Data($this->Binding))->entityInvoiceHasFullPaymentAll();
    }

    /**
     * @return bool|TblPayment[]
     */
    public function entityPaymentAll()
    {

        return (new Data($this->Binding))->entityPaymentAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblBalance
     */
    public function entityBalanceById($Id)
    {

        return (new Data($this->Binding))->entityBalanceById($Id);
    }
}
