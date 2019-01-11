<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceCreditor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceCauser;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemValue;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param int $Id
     *
     * @return false|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice', $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblInvoiceItemValue
     */
    public function getInvoiceItemValueById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItemValue',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblInvoiceCreditor
     */
    public function getInvoiceCreditorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceCreditor',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblInvoiceDebtor
     */
    public function getInvoiceDebtorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceDebtor',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblInvoiceCauser
     */
    public function getInvoiceCauserById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceCauser',
            $Id);
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')->findAll();
        return (null === $Entity ? false : $Entity);
    }

    /**
     * IsReversal = false
     *
     * @param bool $Check
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByIsPaid($Check = true)
    {
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice',
            array(
                TblInvoice::ATTR_IS_PAID     => $Check,
            ));
        return $EntityList;
    }

    /**
     * @param $InvoiceNumber
     *
     * @return TblInvoice|bool
     */
    public function getInvoiceByNumber($InvoiceNumber)
    {
        /** @var TblInvoice|null $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findOneBy(array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param \DateTime $Date
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByYearAndMonth(\DateTime $Date)
    {

        $EntityList = $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice');
        if($EntityList) {
            /** @var TblInvoice $Entity */
            foreach ($EntityList as &$Entity) {
                if((new \DateTime($Entity->getTargetTime()))->format('ym') != $Date->format('ym')) {
                    $Entity = false;
                }
            }
            $EntityList = array_filter($EntityList);
        }
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param $InvoiceNumber
     * @param $Month
     * @param $Year
     * @param $TargetDate
     * @param $IsPaid
     *
     * @return object|TblInvoice|null
     * @throws \Exception
     */
    public function createInvoice(
        $InvoiceNumber,
        $IntegerNumber,
        $Month,
        $Year,
        $TargetDate,
        $IsPaid

    )
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = null;
        $Entity = $Manager->getEntity('TblInvoice')->findOneBy(
            array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));

        if($Entity === null) {
            $Entity = new TblInvoice();
            $Entity->setInvoiceNumber($Year.$Month.$IntegerNumber);
            $Entity->setInvoiceNumber($Year.$Month.$IntegerNumber);
            $Entity->setMonth($Month);
            $Entity->setYear($Year);
            $Entity->setTargetTime(($TargetDate ? new \DateTime($TargetDate) : null));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param bool       $isReversal
     *
     * @return bool
     */
    public function changeInvoiceIsReversal(TblInvoice $tblInvoice, $isReversal = true)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity) {
            $Entity->setIsReversal($isReversal);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param bool       $isPaid
     *
     * @return bool
     */
    public function changeInvoiceIsPaid(TblInvoice $tblInvoice, $isPaid = true)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity) {
            $Entity->setIsPaid($isPaid);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtorAccounting   $tblDebtor
     * @param TblPaymentType        $tblPaymentType
     * @param TblBankReference|null $tblBankReference
     *
     * @return null|object|TblInvoiceDebtor
     */
    public function createDebtor(
        TblDebtorAccounting $tblDebtor,
        TblPaymentType $tblPaymentType,
        TblBankReference $tblBankReference = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = null;

        if($tblBankReference) {

            $Entity = $Manager->getEntity('TblInvoiceDebtor')->findOneBy(
                array(
                    TblInvoiceDebtor::ATTR_SERVICE_TBL_DEBTOR            => $tblDebtor->getId(),
                    TblInvoiceDebtor::ATTR_SERVICE_TBL_BANKING_REFERENCE => $tblBankReference->getId(),
                    TblInvoiceDebtor::ATTR_SERVICE_TBL_PAYMENT_TYPE      => $tblPaymentType->getId()
                ));

            if($Entity === null) {
                $Entity = new TblInvoiceDebtor();
                $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
                $Entity->setDebtorPerson($tblDebtor->getServiceTblPerson());
                $Entity->setBankReference($tblBankReference->getReference());
                $Entity->setOwner($tblBankReference->getOwner());
                $Entity->setBankName($tblBankReference->getBankName());
                $Entity->setIBAN($tblBankReference->getIBAN());
                $Entity->setBIC($tblBankReference->getBIC());
                $Entity->setServiceTblDebtor($tblDebtor);
                $Entity->setServiceTblBankReference($tblBankReference);
                $Entity->setServiceTblPaymentType($tblPaymentType);

                $Manager->saveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                    $Entity);
            }
        } else {
            $Entity = $Manager->getEntity('TblInvoiceDebtor')->findOneBy(
                array(
                    TblInvoiceDebtor::ATTR_SERVICE_TBL_DEBTOR            => $tblDebtor->getId(),
                    TblInvoiceDebtor::ATTR_SERVICE_TBL_BANKING_REFERENCE => null
                ));

            if($Entity === null) {
                $Entity = new TblInvoiceDebtor();
                $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
                $Entity->setDebtorPerson($tblDebtor->getServiceTblPerson());
                $Entity->setBankReference('');
                $Entity->setOwner('');
                $Entity->setBankName('');
                $Entity->setIBAN('');
                $Entity->setBIC('');
                $Entity->setServiceTblDebtor($tblDebtor);
                $Entity->setServiceTblBankReference(null);
                $Entity->setServiceTblPaymentType($tblPaymentType);

                $Manager->saveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                    $Entity);
            }
        }

        return $Entity;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return TblInvoiceItemValue
     */
    public function createItem(TblBasketVerification $tblBasketVerification)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = null;
        if($tblBasketVerification->getServiceTblItem()) {
            $Entity = $Manager->getEntity('TblItem')->findOneBy(
                array(
                    TblInvoiceItemValue::ATTR_NAME             => $tblBasketVerification->getServiceTblItem()->getName(),
                    TblInvoiceItemValue::ATTR_DESCRIPTION      => $tblBasketVerification->getServiceTblItem()->getDescription(),
                    TblInvoiceItemValue::ATTR_VALUE            => $tblBasketVerification->getValue(),
                    TblInvoiceItemValue::ATTR_QUANTITY         => $tblBasketVerification->getQuantity(),
                    TblInvoiceItemValue::ATTR_SERVICE_TBL_ITEM => $tblBasketVerification->getServiceTblItem()->getId()
                ));
        }

        if($Entity === null) {
            $Entity = new TblInvoiceItemValue();
            $Entity->setName($tblBasketVerification->getServiceTblItem()->getName());
            $Entity->setDescription($tblBasketVerification->getServiceTblItem()->getDescription());
            $Entity->setValue($tblBasketVerification->getValue());
            $Entity->setQuantity($tblBasketVerification->getQuantity());
            $Entity->setServiceTblItem($tblBasketVerification->getServiceTblItem());

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblInvoice          $tblInvoice
     * @param TblInvoiceItemValue $tblItem
     * @param TblPerson           $tblPerson
     * @param TblInvoiceDebtor    $tblDebtor
     *
     * @return TblInvoiceCauser
     */
    public function createInvoiceItem(TblInvoice $tblInvoice, TblInvoiceItemValue $tblItem, TblPerson $tblPerson,
        TblInvoiceDebtor $tblDebtor
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblInvoiceCauser();
        $Entity->setTblInvoice($tblInvoice);
        $Entity->setTblItem($tblItem);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblInvoiceDebtor($tblDebtor);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }
}
