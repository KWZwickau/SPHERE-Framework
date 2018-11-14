<?php

namespace SPHERE\Application\Billing\Inventory\Item\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemGroup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemType;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemVariant;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Inventory\Item\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        /**
         * ItemType
         */
        $tblItemType = $this->createItemType(TblItemType::TYPE_SINGLE);
        $this->createItemType(TblItemType::TYPE_CROWD);

        $tblItem = $this->createItem($tblItemType, 'Schulgeld');
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        $this->createItemGroup($tblItem, $tblGroup);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblItem
     */
    public function getItemById($Id)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItem', $Id);
        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblItemGroup
     */
    public function getItemGroupById($Id)
    {

        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItemGroup', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemGroup[]
     */
    public function getItemGroupByItem(TblItem $tblItem)
    {

        $Entity = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemGroup',
            array(
                TblItemGroup::ATTR_TBL_ITEM => $tblItem->getId()
            ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemVariant[]
     */
    public function getItemVariantByItem(TblItem $tblItem)
    {

        $Entity = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemVariant',
            array(
                TblItemVariant::ATTR_TBL_ITEM => $tblItem->getId()
            ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblItemGroup[]
     */
    public function getItemGroupByGroup(TblGroup $tblGroup)
    {

        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemGroup',
            array(
                TblItemGroup::ATTR_SERVICE_TBL_GROUP => $tblGroup->getId()
            ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblItemGroup[]
     */
    public function getItemGroupAll()
    {
        return $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblItemGroup');
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemType
     */
    public function getItemTypeById($Id)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItemType', $Id);
        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItemType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Name
     *
     * @return bool|Element
     */
    public function getItemTypeByName($Name)
    {

        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemType',
            array(TblItemType::ATTR_NAME => $Name));
    }

    /**
     * @return bool|TblItemType[]
     */
    public function getItemTypeAll()
    {

        return $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblItemType');
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemCalculation
     */
    public function getCalculationById($Id)
    {

        return $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItemCalculation', $Id);
    }

    /**
     * @param $Name
     *
     * @return bool|null|TblItem
     */
    public function getItemByName($Name)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItem')
//            ->findOneBy(array(TblItem::ATTR_NAME => $Name));
        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItem',
            array(TblItem::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblItem[]
     */
    public function getItemAll()
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItem')->findAll();
        return $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblItem');

    }

    /**
     * @param $Id
     *
     * @return bool|TblItemAccount
     */
    public function getItemAccountById($Id)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItemAccount', $Id);
        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItemAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemAccount[]
     */
    public function getItemAccountAllByItem(TblItem $tblItem)
    {

//        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblItemAccount')
//            ->findBy(array(TblItemAccount::ATTR_TBL_ITEM => $tblItem->getId()));
        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemAccount',
            array(TblItemAccount::ATTR_TBL_ITEM => $tblItem->getId()));

        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $Name
     *
     * @return bool|TblItem
     */
    public function existsItem($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblItem',
            array(TblItem::ATTR_NAME => $Name));
    }

    /**
     * @param TblItemType             $tblItemType
     * @param                         $Name
     * @param string                  $Description
     *
     * @return null|object|TblItem
     */
    public function createItem(
        TblItemType $tblItemType,
        $Name,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItem')->findOneBy(array(
            TblItem::ATTR_NAME => $Name,
        ));

        if ($Entity === null) {
            $Entity = new TblItem();
            $Entity->setName($Name);
            $Entity->setTblItemType($tblItemType);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblItem  $tblItem
     * @param TblGroup $tblGroup
     *
     * @return null|object|TblItemGroup
     */
    public function createItemGroup(
        TblItem $tblItem,
        TblGroup $tblGroup
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemGroup')->findOneBy(array(
            TblItemGroup::ATTR_TBL_ITEM => $tblItem->getId(),
            TblItemGroup::ATTR_SERVICE_TBL_GROUP => $tblGroup->getId(),
        ));

        if ($Entity === null) {
            $Entity = new TblItemGroup();
            $Entity->setTblItem($tblItem);
            $Entity->setServiceTblGroup($tblGroup);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return TblItemType
     */
    public function createItemType($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemType')->findOneBy(array(
            TblItemType::ATTR_NAME => $Name,
        ));

        if (null === $Entity) {
            $Entity = new TblItemType();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param                     $Value
     * @param TblType|null        $Course
     * @param TblSiblingRank|null $ChildRank
     *
     * @return TblItemCalculation
     */
    public function createCalculation($Value, TblType $Course = null, TblSiblingRank $ChildRank = null)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblItemCalculation();
        $Entity->setValue(str_replace(',', '.', $Value));
        if (null !== $Course) {
            $Entity->setServiceTblType($Course);
        }
        if (null !== $ChildRank) {
            $Entity->setServiceTblSiblingRank($ChildRank);
        }
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);
        return $Entity;
    }

    /**
     * @param TblItem     $tblItem
     * @param             $Name
     * @param             $Description
     *
     * @return bool
     */
    public function updateItem(
        TblItem $tblItem,
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblItem $Entity */
        $Entity = $Manager->getEntityById('TblItem', $tblItem->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblItemCalculation   $tblCalculation
     * @param                  $Value
     *
     * @return bool
     */
    public function updateCalculation(TblItemCalculation $tblCalculation, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblItemCalculation $Entity */
        $Entity = $Manager->getEntityById('TblItemCalculation', $tblCalculation->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblItemCalculation $tblCalculation
     * @param TblItem        $tblItem
     *
     * @return bool
     */
    public function destroyCalculation(
        TblItemCalculation $tblCalculation,
        TblItem $tblItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemCalculation')->findOneBy(array('Id' => $tblCalculation->getId()));
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
     * @param TblItem    $tblItem
     * @param TblAccount $tblAccount
     *
     * @return TblItemAccount|null
     */
    public function addItemAccount(
        TblItem $tblItem,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemAccount')->findOneBy(
            array(
                TblItemAccount::ATTR_TBL_ITEM            => $tblItem->getId(),
                TblItemAccount::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblItemAccount();
            $Entity->setTblItem($tblItem);
            $Entity->setTblAccount($tblAccount);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool
     */
    public function removeItem(
        TblItem $tblItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblItem', $tblItem->getId());
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
     * @param TblItemGroup $tblItemGroup
     *
     * @return string
     */
    public function removeItemGroup(TblItemGroup $tblItemGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblItemGroup', $tblItemGroup->getId());
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
     * @param TblItemVariant $tblItemVariant
     *
     * @return string
     */
    public function removeItemVariant(TblItemVariant $tblItemVariant)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblItemVariant', $tblItemVariant->getId());
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
     * @param TblItemAccount $tblItemAccount
     *
     * @return bool
     */
    public function removeItemAccount(
        TblItemAccount $tblItemAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemAccount')->findOneBy(
            array(
                'Id' => $tblItemAccount->getId()
            ));
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
