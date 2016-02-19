<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Balance\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        /**
         * TblPayment
         */
        $this->createPaymentType('SEPA-Lastschrift');
        $this->createPaymentType('SEPA-Überweisung');
        $this->createPaymentType('Bar');
    }

    /**
     * @param $PaymentType
     *
     * @return TblPaymentType|null|object
     */
    public function createPaymentType($PaymentType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPaymentType')->findOneBy(array(TblPaymentType::ATTR_NAME => $PaymentType));
        if (null === $Entity) {
            $Entity = new TblPaymentType();
            $Entity->setName($PaymentType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
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
     * @param $Id
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType', $Id);
    }

    /**
     * @return false|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType');
    }

    /**
     * @param $Id
     *
     * @return false|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice', $Id);
    }

    /**
     * @param \SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice $serviceInvoice
     *
     * @return false|TblInvoice[]
     */
    public function getInvoiceByServiceInvoice(
        \SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice $serviceInvoice
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice',
            array(TblInvoice::SERVICE_INVOICE_INVOICE => $serviceInvoice->getId()));
    }

    /**
     * @param $Name
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType',
            array(TblPaymentType::ATTR_NAME => $Name));
    }

    /**
     * @param TblPaymentType $tblPaymentType
     * @param                $Value
     * @param string         $Usage
     *
     * @return TblPayment|null|object
     */
    public function createPayment(TblPaymentType $tblPaymentType, $Value, $Usage)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPayment')->findOneBy(array(
            'tblPaymentType' => $tblPaymentType->getId(),
            'Value'          => $Value,
            'Usage'          => $Usage
        ));

        if (null === $Entity) {
            $Entity = new TblPayment();
            $Entity->setTblPaymentType($tblPaymentType);
            $Entity->setValue($Value);
            $Entity->setUsage($Usage);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param \SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice $serviceInvoice
     * @param TblPayment                                                                $tblPayment
     * @param                                                                           $InvoiceNumber
     * @param bool                                                                      $IsPaid
     *
     * @return null|object|TblInvoice
     */
    public function createInvoice(
        \SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice $serviceInvoice,
        TblPayment $tblPayment,
        $InvoiceNumber,
        $IsPaid = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblInvoice')->findOneBy(array(
            TblInvoice::SERVICE_INVOICE_INVOICE => $serviceInvoice->getId(),
            TblInvoice::ATTR_TBL_PAYMENT        => $tblPayment->getId(),
            TblInvoice::ATTR_INVOICE_NUMBER     => $InvoiceNumber
        ));

        if (null === $Entity) {
            $Entity = new TblInvoice();
            $Entity->setServiceInvoice($serviceInvoice);
            $Entity->setTblPayment($tblPayment);
            $Entity->setInvoiceNumber($InvoiceNumber);
            $Entity->setIsPaid($IsPaid);

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
