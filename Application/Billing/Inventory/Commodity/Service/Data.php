<?php

namespace SPHERE\Application\Billing\Inventory\Commodity\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityType;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        /**
         * CommodityType
         */
        $this->createCommodityType('Einzelleistung');
        $this->createCommodityType('Sammelleistung');
    }

    /**
     * @param $Name
     *
     * @return TblCommodityType
     */
    public function createCommodityType($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCommodityType')->findOneBy(array('Name' => $Name,));
        if (null === $Entity) {
            $Entity = new TblCommodityType();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCommodity
     */
    public function getCommodityById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCommodity', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCommodity
     */
    public function getCommodityByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblCommodity')->findOneBy(
            array(TblCommodity::ATTR_NAME => $Name)
        );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblCommodity[]
     */
    public function getCommodityAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblCommodity')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityType
     */
    public function getCommodityTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCommodityType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblCommodityType[]
     */
    public function getCommodityTypeAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblCommodityType')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityItem
     */
    public function getCommodityItemById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCommodityItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return TblAccount[]
     */
    public function getAccountAllByItem(TblItem $tblItem)
    {

        $tblItemAccountAllByItem = Item::useService()->getItemAccountAllByItem($tblItem);
        $tblAccount = array();
        foreach ($tblItemAccountAllByItem as $tblItemAccount) {
            array_push($tblAccount, $tblItemAccount->getServiceBilling_Account());
        }

        return $tblAccount;
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItem[]
     */
    public function getCommodityItemAllByItem(TblItem $tblItem)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblCommodityItem')
            ->findBy(array(TblCommodityItem::ATTR_TBL_ITEM => $tblItem->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return int
     */
    public function countItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (int)$this->getConnection()->getEntityManager()->getEntity('TblCommodityItem')->countBy(array(
            TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()
        ));
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function sumPriceItemAllByCommodity(TblCommodity $tblCommodity)
    {

        $sum = 0.00;
        $tblCommodityItemByCommodity = $this->getCommodityItemAllByCommodity($tblCommodity);
        /** @var TblCommodityItem $tblCommodityItem */
        foreach ($tblCommodityItemByCommodity as $tblCommodityItem) {
            $sum += $tblCommodityItem->getTblItem()->getPrice() * $tblCommodityItem->getQuantity();
        }

        $sum = round($sum, 2);
        $sum = sprintf("%01.2f", $sum);
        return str_replace('.', ',', $sum)." €";
    }

    /**
     * @param Entity\TblCommodity $tblCommodity
     *
     * @return bool|TblItem[]
     */
    public function getCommodityItemAllByCommodity(TblCommodity $tblCommodity)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblCommodityItem')
            ->findBy(array(TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param                  $Name
     * @param                  $Description
     * @param TblCommodityType $tblCommodityType
     *
     * @return TblCommodity
     */
    public function createCommodity(
        $Name,
        $Description,
        TblCommodityType $tblCommodityType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblCommodity();
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        $Entity->setTblCommodityType($tblCommodityType);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblCommodity            $tblCommodity
     * @param                         $Name
     * @param                         $Description
     * @param Entity\TblCommodityType $tblCommodityType
     *
     * @return bool
     */
    public function updateCommodity(
        TblCommodity $tblCommodity,
        $Name,
        $Description,
        TblCommodityType $tblCommodityType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCommodity $Entity */
        $Entity = $Manager->getEntityById('TblCommodity', $tblCommodity->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblCommodityType($tblCommodityType);

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
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByCommodity(TblCommodity $tblCommodity)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblCommodityItem')
            ->findBy(array(TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()));
        if (!empty( $EntityList )) {
            array_walk($EntityList, function (TblCommodityItem &$tblCommodityItem) {

                $tblCommodityItem = $tblCommodityItem->getTblItem();
            });
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param Entity\TblCommodity $tblCommodity
     * @param TblItem             $tblItem
     * @param                     $Quantity
     *
     * @return bool
     */
    public function addItemToCommodity(
        TblCommodity $tblCommodity,
        TblItem $tblItem,
        $Quantity
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCommodityItem')->findOneBy(array(
            TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId(),
            TblCommodityItem::ATTR_TBL_ITEM      => $tblItem->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblCommodityItem();
            $Entity->setTblCommodity($tblCommodity);
            $Entity->setTblItem($tblItem);
            $Entity->setQuantity(str_replace(',', '.', $Quantity));

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCommodityItem $tblCommodityItem
     *
     * @return bool
     */
    public function removeItemToCommodity(
        TblCommodityItem $tblCommodityItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCommodityItem')->findOneBy(array('Id' => $tblCommodityItem->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param Entity\TblCommodity $tblCommodity
     *
     * @return bool
     */
    public function destroyCommodity(
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityItems = $Manager->getEntity('TblCommodityItem')
            ->findBy(array(TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        $Entity = $Manager->getEntity('TblCommodity')->findOneBy(array('Id' => $tblCommodity->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
