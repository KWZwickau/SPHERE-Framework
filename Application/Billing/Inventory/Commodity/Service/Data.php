<?php

namespace SPHERE\Application\Billing\Inventory\Commodity\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityType;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
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
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        /**
         * CommodityType
         */
        $this->actionCreateCommodityType('Einzelleistung');
        $this->actionCreateCommodityType('Sammelleistung');
    }

    /**
     * @param $Name
     *
     * @return TblCommodityType
     */
    public function actionCreateCommodityType($Name)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblCommodityType')->findOneBy(array('Name' => $Name,));
        if (null === $Entity) {
            $Entity = new TblCommodityType();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCommodity
     */
    public function entityCommodityById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblCommodity', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCommodity
     */
    public function entityCommodityByName($Name)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblCommodity')->findOneBy(
            array(TblCommodity::ATTR_NAME => $Name)
        );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblCommodity[]
     */
    public function entityCommodityAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblCommodity')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityType
     */
    public function entityCommodityTypeById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblCommodityType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblCommodityType[]
     */
    public function entityCommodityTypeAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblCommodityType')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityItem
     */
    public function entityCommodityItemById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblCommodityItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return TblAccount[]
     */
    public function entityAccountAllByItem(TblItem $tblItem)
    {

        $tblItemAccountAllByItem = Item::useService()->entityItemAccountAllByItem($tblItem);
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
    public function entityCommodityItemAllByItem(TblItem $tblItem)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblCommodityItem')
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

        return (int)$this->Connection->getEntityManager()->getEntity('TblCommodityItem')->countBy(array(
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
        $tblCommodityItemByCommodity = $this->entityCommodityItemAllByCommodity($tblCommodity);
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
    public function entityCommodityItemAllByCommodity(TblCommodity $tblCommodity)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblCommodityItem')
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
    public function actionCreateCommodity(
        $Name,
        $Description,
        TblCommodityType $tblCommodityType
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblCommodity();
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        $Entity->setTblCommodityType($tblCommodityType);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
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
    public function actionEditCommodity(
        TblCommodity $tblCommodity,
        $Name,
        $Description,
        TblCommodityType $tblCommodityType
    ) {

        $Manager = $this->Connection->getEntityManager();

        /** @var TblCommodity $Entity */
        $Entity = $Manager->getEntityById('TblCommodity', $tblCommodity->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblCommodityType($tblCommodityType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function entityItemAllByCommodity(TblCommodity $tblCommodity)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblCommodityItem')
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
    public function actionAddCommodityItem(
        TblCommodity $tblCommodity,
        TblItem $tblItem,
        $Quantity
    ) {

        $Manager = $this->Connection->getEntityManager();
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

            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCommodityItem $tblCommodityItem
     *
     * @return bool
     */
    public function actionRemoveCommodityItem(
        TblCommodityItem $tblCommodityItem
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblCommodityItem')->findOneBy(array('Id' => $tblCommodityItem->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
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
    public function actionRemoveCommodity(
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->Connection->getEntityManager();

        $EntityItems = $Manager->getEntity('TblCommodityItem')
            ->findBy(array(TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        $Entity = $Manager->getEntity('TblCommodity')->findOneBy(array('Id' => $tblCommodity->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
