<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor as TblDebtorAccounting;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
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
     * @return false|TblItem
     */
    public function getItemById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblItem', $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor', $Id);
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $InvoiceNumber
     *
     * @return TblInvoice|bool
     */
    public function getInvoiceByNumber($InvoiceNumber)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findOneBy(array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return TblInvoiceItem[]|bool
     */
    public function getInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblInvoiceItem')
            ->findBy(array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblItem[]
     */
    public function getItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        if ($EntityList) {
            /** @var TblInvoiceItem $Entity */
            foreach ($EntityList as &$Entity) {
                $Entity = $Entity->getTblItem();
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblDebtor $tblDebtor
     * @param $InvoiceNumber
     * @param TblAddress|null $tblAddress
     * @param TblMail|null $tblMail
     * @param TblPhone|null $tblPhone
     *
     * @return null|object|TblInvoice
     */
    public function createInvoice(
        TblDebtor $tblDebtor,
        $InvoiceNumber,
        TblAddress $tblAddress = null,
        TblMail $tblMail = null,
        TblPhone $tblPhone = null
    ) // Todo Tabelle erweitern
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = null;
        $Entity = $Manager->getEntity('TblInvoice')->findOneBy(
            array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));

        if ($Entity === null) {
            $tblPerson = $tblDebtor->getServiceTblDebtor()->getServiceTblPerson();

            $Entity = new TblInvoice();
            $Entity->setInvoiceNumber($InvoiceNumber);
            $Entity->setDebtorFirstName($tblPerson->getFirstName());
            $Entity->setDebtorSecondName($tblPerson->getSecondName());
            $Entity->setDebtorLastName($tblPerson->getLastName());
            $Entity->setDebtorSalutation($tblPerson->getSalutation());
            $Entity->setDebtorLastName($tblPerson->getLastName());
            $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
            if (null !== $tblAddress) {
                $Entity->setServiceTblAddress($tblAddress);
            }
            if (null !== $tblMail) {
                $Entity->setServiceTblMail($tblMail);
            }
            if (null !== $tblPhone) {
                $Entity->setServiceTblPhone($tblPhone);
            }
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblDebtor($tblDebtor);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDebtorAccounting $tblDebtor
     * @param TblBankReference $tblBankReference
     *
     * @return TblDebtor
     */
    public function createDebtor(
        TblDebtorAccounting $tblDebtor,
        TblBankReference $tblBankReference
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = null;
        $Entity = $Manager->getEntity('TblDebtor')->findOneBy(
            array(TblDebtor::ATTR_DEBTOR_NUMBER => $tblDebtor->getDebtorNumber(),
                  TblDebtor::ATTR_IBAN          => $tblBankReference->getIBAN()));

        if ($Entity === null) {
            $Entity = new TblDebtor();
            $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
            $Entity->setDebtorPerson($tblDebtor->getServiceTblPerson());
            $Entity->setBankReference($tblBankReference->getReference());
            $Entity->setOwner($tblBankReference->getOwner());
            $Entity->setBankName($tblBankReference->getBankName());
            $Entity->setIBAN($tblBankReference->getIBAN());
            $Entity->setBIC($tblBankReference->getBIC());
            $Entity->setCashSign($tblBankReference->getCashSign());
            $Entity->setCreditorId($tblBankReference->getCreditorId());
            $Entity->setServiceTblDebtor($tblDebtor);
            $Entity->setServiceTblBankReference($tblBankReference);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return TblItem
     */
    public function createItem(TblBasketVerification $tblBasketVerification)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = null;
        if ($tblBasketVerification->getServiceTblItem()) {
            $Entity = $Manager->getEntity('TblItem')->findOneBy(
                array(TblItem::ATTR_NAME             => $tblBasketVerification->getServiceTblItem()->getName(),
                      TblItem::ATTR_DESCRIPTION      => $tblBasketVerification->getServiceTblItem()->getDescription(),
                      TblItem::ATTR_VALUE            => $tblBasketVerification->getValue(),
                      TblItem::ATTR_QUANTITY         => $tblBasketVerification->getQuantity(),
                      TblItem::ATTR_SERVICE_TBL_ITEM => $tblBasketVerification->getServiceTblItem()->getId()));
        }

        if ($Entity === null) {
            $Entity = new TblItem();
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

    public function createInvoiceItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblInvoiceItem();
        $Entity->setTblInvoice($tblInvoice);
        $Entity->setTblItem($tblItem);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);


        return $Entity;
    }
}
