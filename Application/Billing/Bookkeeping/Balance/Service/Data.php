<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblBalance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Element;

class Data
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct( Binding $Connection )
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        /**
         * TblPayment
         */
//        $this->actionCreatePaymentType('SEPA-Lastschrift');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblBalance
     */
    public function entityBalanceById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblBalance', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblBalance
     */
    public function entityBalanceByInvoice( TblInvoice $tblInvoice )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblBalance' )->findOneBy(
            array( TblBalance::ATTR_SERVICE_BILLING_INVOICE => $tblInvoice->getId() )
        );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblBalance[]
     */
    public function entityBalanceAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblBalance' )->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPayment
     */
    public function entityPaymentById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblPayment', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return bool|TblPayment[]
     */
    public function entityPaymentByBalance( TblBalance $tblBalance )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblPayment' )->findBy(
            array( TblPayment::ATTR_TBL_BALANCE => $tblBalance->getId() )
        );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblPayment[]
     */
    public function entityPaymentAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblPayment' )->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function entityInvoiceHasFullPaymentAll()
    {

        $invoiceHasFullPaymentAll = array();
        $balanceAll = $this->entityBalanceAll();
        if ( $balanceAll ) {
            foreach ( $balanceAll as $balance ) {
                $invoice = $balance->getServiceBillingInvoice();
                $sumInvoicePrice = Invoice::useService()->sumPriceItemAllByInvoice( $invoice );
                $sumPaymentPrice = $this->sumPriceItemByBalance( $balance );

                $sumInvoicePrice = round( $sumInvoicePrice, 2 );
                $sumPaymentPrice = round( $sumPaymentPrice, 2 );

                if ( $sumInvoicePrice <= $sumPaymentPrice ) {
                    $invoiceHasFullPaymentAll[] = $invoice;
                }
            }
        }

        return ( empty( $invoiceHasFullPaymentAll ) ? false : $invoiceHasFullPaymentAll );
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function entityInvoiceHasExportDateAll()
    {

        $invoiceHasExportDateAll = array();
        $balanceAll = $this->entityBalanceAll();
        if ( $balanceAll ) {
            foreach ( $balanceAll as $balance ) {
                $invoice = $balance->getServiceBillingInvoice();
                $BalanceDate = $balance->getExportDate();

                if ( $BalanceDate !== false ) {
                    $invoiceHasExportDateAll[] = $invoice;
                }
            }
        }

        return ( empty( $invoiceHasExportDateAll ) ? false : $invoiceHasExportDateAll );
    }

    /**
     * @param TblBalance $tblBalance
     * @return string
     */
    public function sumPriceItemStringByBalance( TblBalance $tblBalance )
    {

        return str_replace( '.', ',', round( $this->sumPriceItemByBalance( $tblBalance ), 2 ) )." €";
    }

    /**
     * @param TblBalance $tblBalance
     * @return float
     */
    public function sumPriceItemByBalance( TblBalance $tblBalance )
    {

        $sum = 0.00;
        $tblPaymentList = $this->entityPaymentByBalance( $tblBalance );
        foreach ( $tblPaymentList as $tblPayment ) {
            $sum += $tblPayment->getValue();
        }

        return $sum;
    }

    /**
     * @param TblDebtor $tblDebtor
     * @return bool
     */
    public function checkPaymentFromDebtorExistsByDebtor( TblDebtor $tblDebtor )
    {

        /** @var TblBalance[] $balanceAllByDebtor */
        $balanceAllByDebtor = $this->Connection->getEntityManager()->getEntity( 'TblBalance' )->findBy(
            array( TblBalance::ATTR_SERVICE_BILLING_BANKING => $tblDebtor->getId() )
        );
        foreach ( $balanceAllByDebtor as $balance ) {
            $Entity = $this->Connection->getEntityManager()->getEntity( 'TblPayment' )->findOneBy(
                array( TblPayment::ATTR_TBL_BALANCE => $balance->getId() )
            );
            if ( $Entity !== null ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblDebtor $serviceBilling_Banking
     * @param TblInvoice $serviceBilling_Invoice
     * @param $ExportDate
     *
     * @return bool
     */
    public function actionCreateBalance( TblDebtor $serviceBilling_Banking, TblInvoice $serviceBilling_Invoice, $ExportDate )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblBalance' )->findOneBy( array(
            TblBalance::ATTR_SERVICE_BILLING_BANKING => $serviceBilling_Banking->getId(),
            TblBalance::ATTR_SERVICE_BILLING_INVOICE => $serviceBilling_Invoice->getId()
        ) );

        if ( null === $Entity ) {
            $Entity = new TblBalance();
            $Entity->setServiceBillingBanking( $serviceBilling_Banking );
            $Entity->setServiceBillingInvoice( $serviceBilling_Invoice );
            if ( $ExportDate !== null ) {
                $Entity->setExportDate( $ExportDate );
            }
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(),
                $Entity );

            return true;
        }

        return false;
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return bool
     */
    public function actionSetExportDateBalance( TblBalance $tblBalance )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblBalance $Entity */
        $Entity = $Manager->getEntityById( 'TblInvoice', $tblBalance->getId() );
        $Protocol = clone $Entity;

        if ( null !== $Entity ) {
            $Entity->setExportDate( new \DateTime( 'now' ) );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createUpdateEntry( $this->Connection->getDatabase(),
                $Protocol,
                $Entity );

            return true;
        }

        return false;
    }

    /**
     * @param TblBalance $tblBalance
     *
     * @return bool
     */
    public function actionRemoveBalance( TblBalance $tblBalance )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblBalance' )->findOneBy(
            array( 'Id' => $tblBalance->getId() )
        );

        if ( null !== $Entity ) {
            /**@var Element $Entity*/
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(),
                $Entity );
            $Manager->killEntity( $Entity );

            return true;
        }

        return false;
    }

    /**
     * @param TblBalance $tblBalance
     * @param $Value
     * @param \DateTime $Date
     *
     * @return TblPayment|null|object
     */
    public function actionCreatePayment( TblBalance $tblBalance, $Value, \DateTime $Date )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblPayment' )->findOneBy( array(
            'tblBalance' => $tblBalance->getId(),
            'Value'      => $Value,
            'Date'       => $Date ) );

        if ( null === $Entity ) {
            $Entity = new TblPayment();
            $Entity->setTblBalance( $tblBalance );
            $Entity->setValue( $Value );
            $Entity->setDate( $Date );

            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(),
                $Entity );
        }

        return $Entity;
    }

    /**
     * @param TblPayment $tblPayment
     *
     * @return bool
     */
    public function actionRemovePayment( TblPayment $tblPayment )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblPayment' )->findOneBy(
            array( 'Id' => $tblPayment->getId() )
        );

        if ( null !== $Entity ) {
            /**@var Element $Entity*/
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(),
                $Entity );
            $Manager->killEntity( $Entity );

            return true;
        }

        return false;
    }
}