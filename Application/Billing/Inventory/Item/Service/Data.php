<?php

namespace SPHERE\Application\Billing\Inventory\Item\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
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
        $this->createItemType('Einzelleistung');
        $this->createItemType('Sammelleistung');
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

        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblCalculation', $Id);
        return ( null === $Entity ? false : $Entity );
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
     * @param TblItem $tblItem
     *
     * @return bool|TblCalculation
     */
    public function getCalculationAllByItem(TblItem $tblItem)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemCalculation',
            array(TblItemCalculation::ATTR_TBL_ITEM => $tblItem->getId()));

        $EntityList = array();
        if ($TempList) {
            /** @var TblItemCalculation $Temp */
            foreach ($TempList as $Temp) {
                $EntityList[] = $Temp->getTblCalculation();
            }
        }

        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblItem $tblItem
     * @param         $SchoolType
     * @param         $SiblingRank
     *
     * @return bool|TblCalculation
     */
    public function existsCalculation(TblItem $tblItem, $SchoolType, $SiblingRank)
    {

        if ($SchoolType === '0') {
            $SchoolType = null;
        }
        if ($SiblingRank === '0') {
            $SiblingRank = null;
        }

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItemCondition')
            ->findOneBy(array(TblItemCalculation::ATTR_TBL_ITEM => $tblItem->getId()));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Name
     *
     * @return bool|TblItem
     */
    public function existsItem($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItem')
            ->findOneBy(array(TblItem::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }


    /**
     * @param int $Value
     *
     * @return string
     */
    public function formatPrice($Value)
    {

        $Value = round($Value, 2);
        $Value = sprintf("%01.2f", $Value);
        return str_replace('.', ',', $Value)." €";
    }

    /**
     * @param string      $Name
     * @param TblItemType $tblItemType
     * @param string      $Description
     *
     * @return TblItem
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
     * @param string $Name
     *
     * @return TblItemType
     */
    public function createItemType($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblItemType();
        $Entity->setName($Name);
        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblItem $tblItem
     * @param         $Value
     * @param null    $Course
     * @param null    $ChildRank
     *
     * @return TblCalculation
     */
    public function createCalculation(TblItem $tblItem, $Value, $Course, $ChildRank)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblCalculation();
        $Entity->setValue(str_replace(',', '.', $Value));
        if (Type::useService()->getTypeById($Course)) {
            $Entity->setServiceSchoolType(Type::useService()->getTypeById($Course));
        }
        if (Relationship::useService()->getSiblingRankById($ChildRank)) {
            $Entity->setServiceStudentSiblingRank(Relationship::useService()->getSiblingRankById($ChildRank));
        }
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        // Verknüpfung speichern
        $EntityKey = new TblItemCalculation();
        $EntityKey->setTblItem($tblItem);
        $EntityKey->setTblCalculation($Entity);
        $Manager->saveEntity($EntityKey);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $EntityKey);

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
     * @param TblCalculation   $tblCalculation
     * @param                  $Value
     *
     * @return bool
     */
    public function updateCalculation(TblCalculation $tblCalculation, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCalculation $Entity */
        $Entity = $Manager->getEntityById('TblCalculation', $tblCalculation->getId());
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
     * @param TblItem $tblItem
     *
     * @return bool
     */
    public function destroyItem(
        TblItem $tblItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

//        $EntityList = $Manager->getEntity( 'TblCommodityItem' )->findBy( array( TblCommodityItem::ATTR_TBL_ITEM => $tblItem->getId() ) ); //todo
        $EntityList = Commodity::useService()->getCommodityItemAllByItem($tblItem);
        if (empty( $EntityList )) {
            $EntityItems = $Manager->getEntity('TblItemAccount')
                ->findBy(array(TblItemAccount::ATTR_TBL_ITEM => $tblItem->getId()));
            if (null !== $EntityItems) {
                foreach ($EntityItems as $Entity) {
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                        $Entity);
                    $Manager->killEntity($Entity);
                }
            }

            $Entity = $Manager->getEntity('TblItem')->findOneBy(array('Id' => $tblItem->getId()));
            if (null !== $Entity) {
                /** @var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
                return true;
            }
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
                TblItemAccount::ATTR_TBL_ITEM                => $tblItem->getId(),
                TblItemAccount::ATTR_SERVICE_BILLING_ACCOUNT => $tblAccount->getId()
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
