<?php

namespace SPHERE\Application\Billing\Inventory\Item\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblItem
     */
    public function getItemById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Name
     *
     * @return bool|null|TblItem
     */
    public function getItemByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItem')
            ->findOneBy(array(TblItem::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblItem[]
     */
    public function getItemAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItem')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemAccount
     */
    public function getItemAccountById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItemAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return TblItemAccount[]|bool
     */
    public function getItemAccountAllByItem(TblItem $tblItem)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblItemAccount')
            ->findBy(array(TblItemAccount::ATTR_TBL_ITEM => $tblItem->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $Name
     * @param $Description
     * @param $Price
     * @param $CostUnit
     *
     * @return TblItem
     */                         //ToDO $Course, $ChildRank
    public function createItem(
        $Name,
        $Description,
        $Price,
        $CostUnit //,
//        $Course,
//        $ChildRank
    )
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblItem();
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        $Entity->setPrice(str_replace(',', '.', $Price));
        $Entity->setCostUnit($CostUnit);
//        if (Management::serviceCourse()->entityCourseById($Course))
//        {
//            $Entity->setServiceManagementCourse(Management::serviceCourse()->entityCourseById($Course));
//        }
//        if (Management::serviceStudent()->entityChildRankById($ChildRank))
//        {
//            $Entity->setServiceManagementStudentChildRank(Management::serviceStudent()->entityChildRankById($ChildRank));
//        }
        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblItem $tblItem
     * @param         $Name
     * @param         $Description
     * @param         $Price
     * @param         $CostUnit
     *
     * @return bool
     */                         //ToDO $Course, $ChildRank
    public function updateItem(
        TblItem $tblItem,
        $Name,
        $Description,
        $Price,
        $CostUnit //,
//        $Course,
//        $ChildRank
    )
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblItem $Entity */
        $Entity = $Manager->getEntityById('TblItem', $tblItem->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setPrice(str_replace(',', '.', $Price));
            $Entity->setCostUnit($CostUnit);
//            if (Management::serviceCourse()->entityCourseById($Course))
//            {
//                $Entity->setServiceManagementCourse(Management::serviceCourse()->entityCourseById($Course));
//            }
//            else
//            {
//                $Entity->setServiceManagementCourse(null);
//            }
//            if (Management::serviceStudent()->entityChildRankById($ChildRank))
//            {
//                $Entity->setServiceManagementStudentChildRank(Management::serviceStudent()->entityChildRankById($ChildRank));
//            }
//            else
//            {
//                $Entity->setServiceManagementStudentChildRank(null);
//            }

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
