<?php

namespace SPHERE\Application\Billing\Accounting\Basket\Service;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorCommodity;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodity;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketCommodityDebtor;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblBasket', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblBasket[]
     */
    public function getBasketAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblBasket')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblCommodity[]
     */
    public function getCommodityAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemAllByBasket = $this->getBasketItemAllByBasket($tblBasket);
        $EntityList = array();
        /** @var TblBasketItem $tblBasketItem */
        foreach ($tblBasketItemAllByBasket as $tblBasketItem) {
            $tblCommodity = $tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity();
            if (!array_key_exists($tblCommodity->getId(), $EntityList)) {
                $EntityList[$tblCommodity->getId()] = $tblCommodity;
            }
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblBasketItem')
            ->findBy(array(TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblCommodityItem $tblCommodityItem
     *
     * @return array|bool
     */
    public function getBasketItemAllByCommodityItem(TblCommodityItem $tblCommodityItem)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblBasketItem')
            ->findBy(array(TblBasketItem::ATTR_SERVICE_BILLING_COMMODITY_ITEM => $tblCommodityItem->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblCommodity $tblCommodity
     *
     * @return TblBasketItem[]|bool
     */
    public function getBasketItemAllByBasketAndCommodity(TblBasket $tblBasket, TblCommodity $tblCommodity)
    {

        $tblBasketItemAllByBasket = $this->getBasketItemAllByBasket($tblBasket);
        $EntityList = array();
        /** @var TblBasketItem $tblBasketItem */
        foreach ($tblBasketItemAllByBasket as $tblBasketItem) {
            if ($tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity()->getId() == $tblCommodity->getId()) {
                $EntityList[$tblBasketItem->getId()] = $tblBasketItem;
            }
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblBasketItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /***
     * @param $Id
     *
     * @return bool|TblBasketPerson
     */
    public function getBasketPersonById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblBasketPerson', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblBasketCommodity $tblBasketCommodity
     *
     * @return TblBasketCommodityDebtor[]|bool
     */
    public function getBasketCommodityDebtorAllByBasketCommodity(TblBasketCommodity $tblBasketCommodity)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblBasketCommodityDebtor')
            ->findBy(array(TblBasketCommodityDebtor::ATTR_TBL_BASKET_COMMODITY => $tblBasketCommodity->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketPerson[]
     */
    public function getBasketPersonAllByBasket(TblBasket $tblBasket)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblBasketPerson')
            ->findBy(array(TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return int
     */
    public function countPersonByBasket(TblBasket $tblBasket)
    {

        return (int)$this->getConnection()->getEntityManager()->getEntity('TblBasketPerson')->countBy(array(
            TblBasketPerson::ATTR_TBL_BASKET => $tblBasket->getId()
        ));
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $Data
     * @param           $IsSave
     *
     * @return bool
     */
    public function checkDebtors(
        TblBasket $tblBasket,
        $Data,
        $IsSave = false
    ) {

        if ($Data !== null) {
            foreach ($Data as $Key => $Value) {
                $tblBasketCommodity = $this->getBasketCommodityById($Key);
                $tblBasketCommodityDebtor = $this->getBasketCommodityDebtorById($Value);
                $tblTempInvoice = Invoice::useService()->createTempInvoice(
                    $tblBasket, $tblBasketCommodity->getServiceManagementPerson(),
                    $tblBasketCommodityDebtor->getServiceBillingDebtor());
                Invoice::useService()->createTempInvoiceCommodity($tblTempInvoice,
                    $tblBasketCommodity->getServiceBillingCommodity());

                // auto add DebtorCommodity
                if ($IsSave) {
                    Banking::useService()->addCommodityToDebtor(
                        $tblBasketCommodityDebtor->getServiceBillingDebtor(),
                        $tblBasketCommodity->getServiceBillingCommodity()
                    );
                }
            }

            return true;
        }

        $tblCommodityAllByBasket = Basket::useService()->getCommodityAllByBasket($tblBasket);
        $tblBasketPersonAllByBasket = Basket::useService()->getBasketPersonAllByBasket($tblBasket);

        if (!empty( $tblBasketPersonAllByBasket )) {
            /** @var TblBasketPerson $tblBasketPerson */
            foreach ($tblBasketPersonAllByBasket as $tblBasketPerson) {
                $tblPerson = $tblBasketPerson->getServiceManagementPerson();
                foreach ($tblCommodityAllByBasket as $tblCommodity) {
                    /** @var TblDebtorCommodity[] $tblDebtorCommodityListByPersonAndCommodity */
                    $tblDebtorCommodityListByPersonAndCommodity = array();
                    /** @var TblDebtor[] $tblDebtorListByPerson */
                    $tblDebtorListByPerson = array();
                    $debtorPersonAll = Banking::useService()->getDebtorAllByPerson($tblPerson);
                    if (!empty( $debtorPersonAll )) {
                        foreach ($debtorPersonAll as $tblDebtor) {
                            $tblDebtorCommodityList = Banking::useService()->getDebtorCommodityAllByDebtorAndCommodity($tblDebtor,
                                $tblCommodity);
                            if (!empty( $tblDebtorCommodityList )) {
                                foreach ($tblDebtorCommodityList as $tblDebtorCommodity) {
                                    $tblDebtorCommodityListByPersonAndCommodity[] = $tblDebtorCommodity;
                                }
                            }
                            $tblDebtorListByPerson[] = $tblDebtor;
                        }
                    }

                    $tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if (!empty( $tblPersonRelationshipList )) {
                        foreach ($tblPersonRelationshipList as $tblPersonRelationship) {
                            if ($tblPerson->getId() === $tblPersonRelationship->getServiceTblPersonFrom()->getId()) {
                                $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPersonRelationship->getServiceTblPersonTo());
                            } else {
                                $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPersonRelationship->getServiceTblPersonFrom());
                            }
                            if (!empty( $tblDebtorList )) {
                                foreach ($tblDebtorList as $tblDebtor) {
                                    if (!in_array($tblDebtor, $tblDebtorListByPerson)) {
                                        $tblDebtorCommodityList = Banking::useService()->getDebtorCommodityAllByDebtorAndCommodity($tblDebtor,
                                            $tblCommodity);
                                        if (!empty( $tblDebtorCommodityList )) {
                                            foreach ($tblDebtorCommodityList as $tblDebtorCommodity) {
                                                $tblDebtorCommodityListByPersonAndCommodity[] = $tblDebtorCommodity;
                                            }
                                        }
                                        $tblDebtorListByPerson[] = $tblDebtor;
                                    }
                                }
                            }
                        }
                    }

                    if (count($tblDebtorListByPerson) == 1) {
                        $tblDebtor = Banking::useService()->getDebtorById($tblDebtorListByPerson[0]->getId());
                        $tblTempInvoice = Invoice::useService()->createTempInvoice($tblBasket, $tblPerson,
                            $tblDebtor);
                        Invoice::useService()->createTempInvoiceCommodity($tblTempInvoice, $tblCommodity);
                    } else {
                        if (empty( $tblDebtorCommodityListByPersonAndCommodity )) {
                            $tblBasketCommodity = $this->createBasketCommodity($tblBasket, $tblPerson,
                                $tblCommodity);
                            foreach ($tblDebtorListByPerson as $tblDebtor) {
                                $this->createBasketCommodityDebtor($tblBasketCommodity, $tblDebtor);
                            }
                        } else {
                            if (count($tblDebtorCommodityListByPersonAndCommodity) == 1) {
                                $tblDebtor = $tblDebtorCommodityListByPersonAndCommodity[0]->getTblDebtor();
                                $tblTempInvoice = Invoice::useService()->createTempInvoice($tblBasket,
                                    $tblPerson, $tblDebtor);
                                Invoice::useService()->createTempInvoiceCommodity($tblTempInvoice,
                                    $tblCommodity);
                            } else {
                                $tblBasketCommodity = $this->createBasketCommodity($tblBasket, $tblPerson,
                                    $tblCommodity);
                                foreach ($tblDebtorCommodityListByPersonAndCommodity as $tblDebtorCommodity) {
                                    $this->createBasketCommodityDebtor($tblBasketCommodity,
                                        $tblDebtorCommodity->getTblDebtor());
                                }
                            }
                        }
                    }
                }
            }
        }

        $tblBasketCommodity = $this->getBasketCommodityAllByBasket($tblBasket);
        return empty( $tblBasketCommodity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketCommodity
     */
    public function getBasketCommodityById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblBasketCommodity', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketCommodityDebtor
     */
    public function getBasketCommodityDebtorById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblBasketCommodityDebtor', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblBasket    $tblBasket
     * @param TblPerson    $tblPerson
     * @param TblCommodity $tblCommodity
     *
     * @return TblBasketCommodity|null
     */
    public function createBasketCommodity(
        TblBasket $tblBasket,
        TblPerson $tblPerson,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketCommodity')->findOneBy(array(
            TblBasketCommodity::ATTR_TBL_BASKET                => $tblBasket->getId(),
            TblBasketCommodity::ATTR_SERVICE_MANAGEMENT_PERSON => $tblPerson->getId(),
            TblBasketCommodity::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblBasketCommodity();
            $Entity->setTblBasket($tblBasket);
            $Entity->setServiceManagementPerson($tblPerson);
            $Entity->setServiceBillingCommodity($tblCommodity);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBasketCommodity $tblBasketCommodity
     * @param TblDebtor          $tblDebtor
     *
     * @return TblBasketCommodityDebtor|null
     */
    public function createBasketCommodityDebtor(
        TblBasketCommodity $tblBasketCommodity,
        TblDebtor $tblDebtor
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketCommodityDebtor')->findOneBy(array(
            TblBasketCommodityDebtor::ATTR_TBL_BASKET_COMMODITY   => $tblBasketCommodity->getId(),
            TblBasketCommodityDebtor::ATTR_SERVICE_BILLING_DEBTOR => $tblDebtor->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblBasketCommodityDebtor();
            $Entity->setTblBasketCommodity($tblBasketCommodity);
            $Entity->setServiceBillingDebtor($tblDebtor);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return TblBasketCommodity[]|bool
     */
    public function getBasketCommodityAllByBasket(TblBasket $tblBasket)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblBasketCommodity')
            ->findBy(array(TblBasketCommodity::ATTR_TBL_BASKET => $tblBasket->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblDebtor[]|bool
     */
    public function checkDebtorExistsByPerson(
        TblPerson $tblPerson
    ) {

        $tblDebtorAllList = array();

        $debtorPersonAll = Banking::useService()->getDebtorAllByPerson($tblPerson);
        if (!empty( $debtorPersonAll )) {
            foreach ($debtorPersonAll as $debtor) {
                array_push($tblDebtorAllList, $debtor);
            }
        }

        $tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        if (!empty( $tblPersonRelationshipList )) {
            foreach ($tblPersonRelationshipList as $tblPersonRelationship) {
                if ($tblPerson->getId() === $tblPersonRelationship->getServiceTblPersonFrom()) {
                    $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPersonRelationship->getServiceTblPersonTo());
                } else {
                    $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPersonRelationship->getServiceTblPersonFrom());
                }

                if (!empty( $tblDebtorList )) {
                    foreach ($tblDebtorList as $tblDebtor) {
                        array_push($tblDebtorAllList, $tblDebtor);
                    }
                }
            }
        }

        if (empty( $tblDebtorAllList )) {
            return false;
        } else {
            return $tblDebtorAllList;
        }
    }

    /**
     * @param $Name
     *
     * @return TblBasket
     */
    public function createBasket(
        $Name
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblBasket();
        date_default_timezone_set('Europe/Berlin');
        $Entity->setCreateDate(new \DateTime('now'));
        $Entity->setName($Name);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $Name
     *
     * @return bool
     */
    public function updateBasket(
        TblBasket $tblBasket,
        $Name
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasket $Entity */
        $Entity = $Manager->getEntityById('TblBasket', $tblBasket->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCommodity $tblCommodity
     * @param TblBasket    $tblBasket
     *
     * @return TblBasket
     */
    public function addBasketItemsByCommodity(
        TblBasket $tblBasket,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $tblCommodityItemList = Commodity::useService()->getCommodityItemAllByCommodity($tblCommodity);

        /** @var TblCommodityItem $tblCommodityItem */
        foreach ($tblCommodityItemList as $tblCommodityItem) {
            $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(array(
                TblBasketItem::ATTR_TBL_BASKET                     => $tblBasket->getId(),
                TblBasketItem::ATTR_SERVICE_BILLING_COMMODITY_ITEM => $tblCommodityItem->getId()
            ));
            if (null === $Entity) {
                $Entity = new TblBasketItem();
                $Entity->setPrice($tblCommodityItem->getTblItem()->getPrice());
                $Entity->setQuantity($tblCommodityItem->getQuantity());
                $Entity->setServiceBillingCommodityItem($tblCommodityItem);
                $Entity->setTblBasket($tblBasket);

                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                    $Entity);
            }
        }
        $Manager->flushCache();

        return $tblBasket;
    }

    /**
     * @param TblCommodity $tblCommodity
     * @param TblBasket    $tblBasket
     *
     * @return TblBasket
     */
    public function removeBasketItemsByCommodity(
        TblBasket $tblBasket,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $tblBasketItemAllByBasket = Basket::useService()->getBasketItemAllByBasket($tblBasket);

        /** @var TblBasketItem $tblBasketItem */
        foreach ($tblBasketItemAllByBasket as $tblBasketItem) {
            if ($tblBasketItem->getServiceBillingCommodityItem()->getTblCommodity()->getId() == $tblCommodity->getId()) {
                $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(array('Id' => $tblBasketItem->getId()));
                /**@var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }
        }
        $Manager->flushCache();

        return $tblBasket;
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return bool
     */
    public function removeBasketItem(
        TblBasketItem $tblBasketItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(
            array(
                'Id' => $tblBasketItem->getId()
            ));
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
     * @param TblBasketItem $tblBasketItem
     * @param               $Price
     * @param               $Quantity
     *
     * @return bool
     */
    public function updateBasketItem(
        TblBasketItem $tblBasketItem,
        $Price,
        $Quantity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasketItem $Entity */
        $Entity = $Manager->getEntityById('TblBasketItem', $tblBasketItem->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setPrice(str_replace(',', '.', $Price));
            $Entity->setQuantity(str_replace(',', '.', $Quantity));

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
     * @param TblPerson $tblPerson
     *
     * @return TblBasketPerson
     */
    public function addBasketPerson(
        TblBasket $tblBasket,
        TblPerson $tblPerson
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBasketPerson')->findOneBy(array(
            TblBasketPerson::ATTR_TBL_BASKET                => $tblBasket->getId(),
            TblBasketPerson::ATTR_SERVICE_MANAGEMENT_PERSON => $tblPerson->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblBasketPerson();
            $Entity->setTblBasket($tblBasket);
            $Entity->setServiceManagementPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblBasketPerson $tblBasketPerson
     *
     * @return bool
     */
    public function removeBasketPerson(
        TblBasketPerson $tblBasketPerson
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketPerson')->findOneBy(
            array(
                'Id' => $tblBasketPerson->getId()
            ));
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
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasket(
        TblBasket $tblBasket
    ) {

        if ($tblBasket !== null) {
            $Manager = $this->getConnection()->getEntityManager();

            $EntityList = $Manager->getEntity('TblBasketPerson')->findBy(array(TblBasketPerson::ATTR_TBL_BASKET => $tblBasket->getId()));
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }

            $EntityList = $Manager->getEntity('TblBasketItem')->findBy(array(TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()));
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }

            /** @var  TblBasketCommodity[] $EntityList */
            $EntityList = $Manager->getEntity('TblBasketCommodity')->findBy(array(TblBasketCommodity::ATTR_TBL_BASKET => $tblBasket->getId()));
            foreach ($EntityList as $Entity) {
                $EntitySubList = $Manager->getEntity('TblBasketCommodityDebtor')->findBy(array(
                    TblBasketCommodityDebtor::ATTR_TBL_BASKET_COMMODITY => $Entity->getId()
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

            $Entity = $Manager->getEntity('TblBasket')->findOneBy(array('Id' => $tblBasket->getId()));
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->bulkKillEntity($Entity);

            $Manager->flushCache();

            return true;
        }

        return false;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasketCommodity(
        TblBasket $tblBasket
    ) {

        if ($tblBasket !== null) {
            $Manager = $this->getConnection()->getEntityManager();

            /** @var  TblBasketCommodity[] $EntityList */
            $EntityList = $Manager->getEntity('TblBasketCommodity')->findBy(array(TblBasketCommodity::ATTR_TBL_BASKET => $tblBasket->getId()));
            foreach ($EntityList as $Entity) {
                $EntitySubList = $Manager->getEntity('TblBasketCommodityDebtor')->findBy(array(
                    TblBasketCommodityDebtor::ATTR_TBL_BASKET_COMMODITY => $Entity->getId()
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
