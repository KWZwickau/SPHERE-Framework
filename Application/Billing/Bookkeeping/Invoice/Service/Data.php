<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoiceCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
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
     * @param integer $Id
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblInvoice', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblTempInvoice
     */
    public function getTempInvoiceById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTempInvoice', $Id);
        return ( null === $Entity ? false : $Entity );
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
     * @param $IsPaid
     *
     * @return TblInvoice[]|bool
     */
    public function getInvoiceAllByIsPaidState($IsPaid)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findBy(array(TblInvoice::ATTR_IS_PAID => $IsPaid));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $IsVoid
     *
     * @return TblInvoice[]|bool
     */
    public function getInvoiceAllByIsVoidState($IsVoid)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findBy(array(TblInvoice::ATTR_IS_VOID => $IsVoid));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblInvoiceItem
     */
    public function getInvoiceItemById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblInvoiceItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Number
     *
     * @return TblInvoice|bool
     */
    public function getInvoiceByNumber($Number)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findOneBy(array(TblInvoice::ATTR_NUMBER => $Number));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return string
     */
    public function sumPriceItemAllStringByInvoice(TblInvoice $tblInvoice)
    {

        $result = sprintf("%01.2f", $this->sumPriceItemAllByInvoice($tblInvoice));
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return float
     */
    public function sumPriceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $sum = 0.00;
        $tblInvoiceItemByInvoice = $this->getInvoiceItemAllByInvoice($tblInvoice);
        /** @var TblInvoiceItem $tblInvoiceItem */
        foreach ($tblInvoiceItemByInvoice as $tblInvoiceItem) {
            $sum += $tblInvoiceItem->getItemPrice() * $tblInvoiceItem->getItemQuantity();
        }

        return $sum;
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
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function checkInvoiceFromDebtorIsPaidByDebtor(TblDebtor $tblDebtor)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')->findOneBy(array(
            TblInvoice::ATTR_IS_PAID       => true,
            TblInvoice::ATTR_DEBTOR_NUMBER => $tblDebtor->getDebtorNumber()
        ));
        return ( null === $Entity ? false : true );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return TblTempInvoice[]|bool
     */
    public function getTempInvoiceAllByBasket(TblBasket $tblBasket)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblTempInvoice')
            ->findBy(array(TblTempInvoice::ATTR_SERVICE_BILLING_BASKET => $tblBasket->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     *
     * @return TblTempInvoiceCommodity[]|bool
     */
    public function getTempInvoiceCommodityAllByTempInvoice(TblTempInvoice $tblTempInvoice)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblTempInvoiceCommodity')
            ->findBy(array(TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE => $tblTempInvoice->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblInvoiceItem $tblInvoiceItem
     * @param TblInvoice     $tblInvoiceCopy
     *
     * @return TblInvoiceItem
     */
    public function createInvoiceItemCopy(TblInvoiceItem $tblInvoiceItem, TblInvoice $tblInvoiceCopy)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblInvoiceItem();
        $Entity->setCommodityName($tblInvoiceItem->getCommodityName());
        $Entity->setCommodityDescription($tblInvoiceItem->getCommodityDescription());
        $Entity->setItemName($tblInvoiceItem->getItemName());
        $Entity->setItemPrice($tblInvoiceItem->getItemPrice() * -1);
        $Entity->setItemQuantity($tblInvoiceItem->getItemQuantity());
        $Entity->setItemDescription($tblInvoiceItem->getItemDescription());
        $Entity->setTblInvoice($tblInvoiceCopy);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return TblInvoice
     */
    public function copyInvoice(TblInvoice $tblInvoice)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblInvoice();
        $Entity->setBasketName($tblInvoice->getBasketName());
        $Entity->setDebtorFirstName($tblInvoice->getDebtorFirstName());
        $Entity->setDebtorLastName($tblInvoice->getDebtorLastName());
        $Entity->setDebtorNumber($tblInvoice->getDebtorNumber());
        $Entity->setDebtorSalutation($tblInvoice->getDebtorSalutation());
        $Entity->setDiscount($tblInvoice->getDiscount());
        $Entity->setInvoiceDate(new \DateTime($tblInvoice->getInvoiceDate()));
        $Entity->setNumber($tblInvoice->getNumber());
        $Entity->setPaid(false);
        $Entity->setVoid(true);
        $Entity->setPaymentDate(new \DateTime($tblInvoice->getPaymentDate()));
        $Entity->setServiceTblPaymentType($tblInvoice->getServiceTblPaymentType());
        $Entity->setServiceTblPerson($tblInvoice->getServiceTblPerson());
        $Entity->setServiceTblAddress($tblInvoice->getServiceTblAddress());

        $Entity->setInvoiceDate(new \DateTime($tblInvoice->getInvoiceDate()));
        $Entity->setPaymentDate(new \DateTime($tblInvoice->getPaymentDate()));
        $Entity->setPaymentDateModified($tblInvoice->getPaymentDateModified());

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool
     */
    public function cancelInvoice(
        TblInvoice $tblInvoice
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setVoid(true);
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
     *
     * @return bool
     */
    public function createPayInvoice(
        TblInvoice $tblInvoice
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setPaid(true);
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
     *
     * @return bool
     */
    public function destroyInvoice(TblInvoice $tblInvoice)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblInvoice')->findOneBy(
            array(
                'Id' => $tblInvoice->getId()
            ));
        /** @var TblInvoice $Entity */
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     * @param TblDebtor $tblDebtor
     *
     * @return TblTempInvoice|null
     */
    public function createTempInvoice(
        TblBasket $tblBasket,
        TblPerson $tblPerson,
        TblDebtor $tblDebtor
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTempInvoice')->findOneBy(array(
            TblTempInvoice::ATTR_SERVICE_BILLING_BASKET    => $tblBasket->getId(),
            TblTempInvoice::ATTR_SERVICE_MANAGEMENT_PERSON => $tblPerson->getId(),
            TblTempInvoice::ATTR_SERVICE_BILLING_DEBTOR    => $tblDebtor->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblTempInvoice();
            $Entity->setServiceBillingBasket($tblBasket);
            $Entity->setServiceManagementPerson($tblPerson);
            $Entity->setServiceBillingDebtor($tblDebtor);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     * @param TblCommodity   $tblCommodity
     *
     * @return TblTempInvoiceCommodity|null
     */
    public function createTempInvoiceCommodity(
        TblTempInvoice $tblTempInvoice,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTempInvoiceCommodity')->findOneBy(array(
            TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE          => $tblTempInvoice->getId(),
            TblTempInvoiceCommodity::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblTempInvoiceCommodity();
            $Entity->setTblTempInvoice($tblTempInvoice);
            $Entity->setServiceBillingCommodity($tblCommodity);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblInvoice     $tblInvoice
     * @param TblPaymentType $tblPaymentType
     *
     * @return bool
     */
    public function changeInvoicePaymentType(
        TblInvoice $tblInvoice,
        TblPaymentType $tblPaymentType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblPaymentType($tblPaymentType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyTempInvoice(
        TblBasket $tblBasket
    ) {

        if ($tblBasket !== null) {
            $Manager = $this->getConnection()->getEntityManager();

            /** @var  TblTempInvoice[] $EntityList */
            $EntityList = $Manager->getEntity('TblTempInvoice')->findBy(array(
                TblTempInvoice::ATTR_SERVICE_BILLING_BASKET => $tblBasket->getId()
            ));
            foreach ($EntityList as $Entity) {
                $EntitySubList = $Manager->getEntity('TblTempInvoiceCommodity')->findBy(array(
                    TblTempInvoiceCommodity::ATTR_TBL_TEMP_INVOICE => $Entity->getId()
                ));
                foreach ($EntitySubList as $SubEntity) {
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                        $SubEntity);
                    $Manager->bulkKillEntity($SubEntity);
                }
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }

            $Manager->flushCache();

            return true;
        }

        return false;
    }
}
