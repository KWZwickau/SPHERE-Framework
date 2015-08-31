<?php

namespace SPHERE\Application\Billing\Inventory\Item\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
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
//        $this->actionCreateCommodityType( 'Einzelleistung' );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblItem
     */
    public function entityItemById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Name
     *
     * @return bool|null|TblItem
     */
    public function entityItemByName($Name)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblItem')
            ->findOneBy(array(TblItem::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblItem[]
     */
    public function entityItemAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblItem')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemAccount
     */
    public function entityItemAccountById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblItemAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return TblItemAccount[]|bool
     */
    public function entityItemAccountAllByItem(TblItem $tblItem)
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblItemAccount')
            ->findBy(array(TblItemAccount::ATTR_TBL_Item => $tblItem->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $Name
     * @param $Description
     * @param $Price
     * @param $CostUnit
    //     * @param $Course
     * //     * @param $ChildRank
     *
     * @return TblItem
     */
    public function actionCreateItem(
        $Name,
        $Description,
        $Price,
        $CostUnit //,
//        $Course,
//        $ChildRank
    )
    {

        $Manager = $this->Connection->getEntityManager();

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

        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblItem $tblItem
     * @param         $Name
     * @param         $Description
     * @param         $Price
     * @param         $CostUnit
     * @param         $Course
     * @param         $ChildRank
     *
     * @return bool
     */
    public function actionEditItem(
        TblItem $tblItem,
        $Name,
        $Description,
        $Price,
        $CostUnit,
        $Course,
        $ChildRank
    ) {

        $Manager = $this->Connection->getEntityManager();

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
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(),
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
    public function actionDestroyItem(
        TblItem $tblItem
    ) {

        $Manager = $this->Connection->getEntityManager();

//        $EntityList = $Manager->getEntity( 'TblCommodityItem' )->findBy( array( TblCommodityItem::ATTR_TBL_ITEM => $tblItem->getId() ) ); //todo
        $EntityList = Commodity::useService()->entityCommodityItemAllByItem($tblItem);
        if (empty( $EntityList )) {
            $EntityItems = $Manager->getEntity('TblItemAccount')
                ->findBy(array(TblItemAccount::ATTR_TBL_Item => $tblItem->getId()));
            if (null !== $EntityItems) {
                foreach ($EntityItems as $Entity) {
                    Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
                        $Entity);
                    $Manager->killEntity($Entity);
                }
            }

            $Entity = $Manager->getEntity('TblItem')->findOneBy(array('Id' => $tblItem->getId()));
            if (null !== $Entity) {
                /** @var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
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
    public function actionAddItemAccount(
        TblItem $tblItem,
        TblAccount $tblAccount
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblItemAccount')->findOneBy(
            array(
                TblItemAccount::ATTR_TBL_Item                => $tblItem->getId(),
                TblItemAccount::ATTR_SERVICE_BILLING_ACCOUNT => $tblAccount->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblItemAccount();
            $Entity->setTblItem($tblItem);
            $Entity->setTblAccount($tblAccount);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblItemAccount $tblItemAccount
     *
     * @return bool
     */
    public function actionRemoveItemAccount(
        TblItemAccount $tblItemAccount
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblItemAccount')->findOneBy(
            array(
                'Id' => $tblItemAccount->getId()
            ));
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
