<?php

namespace SPHERE\Application\Education\Competence\ScoreType\Service;

use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreTypeConversion;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreTypeItem;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{
    /**
     * @return void
     */
    public function setupDatabaseContent(): void
    {

    }

    /**
     * @param $id
     *
     * @return TblScoreType|false
     */
    public function getScoreTypeById($id): TblScoreType|false
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreType', $id);
    }

    /**
     * @return TblScoreType[]
     */
    public function getScoreTypeAll(): array
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblScoreType') ?: [];
    }

    /**
     * @param string $name
     * @param string $description
     * 
     * @return TblScoreType
     */
    public function createScoreType(string $name, string $description): TblScoreType 
    {
        $manager = $this->getEntityManager();

        $entity = new TblScoreType();
        $entity->setName($name);
        $entity->setDescription($description);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param string $name
     * @param string $description
     * 
     * @return bool
     */
    public function updateScoreType(TblScoreType $tblScoreType, string $name, string $description): bool 
    {
        $manager = $this->getEntityManager();
        /** @var TblScoreType $entity */
        $entity = $manager->getEntityById('TblScoreType', $tblScoreType->getId());
        $protocol = clone $entity;
        if (null !== $entity) {
            $entity->setName($name);
            $entity->setDescription($description);

            $manager->saveEntity($entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $protocol, $entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return bool
     */
    public function destroyScoreType(TblScoreType $tblScoreType): bool
    {
        $manager = $this->getConnection()->getEntityManager();
        /** @var Element $entity */
        $entity = $manager->getEntityById('TblScoreType', $tblScoreType->getId());
        if (null !== $entity) {
            $manager->killEntity($entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param string $value
     * @param string $name
     * @param string|null $description
     *
     * @return TblScoreTypeItem
     */
    public function createScoreTypeItem(TblScoreType $tblScoreType, string $value, string $name, ?string $description): TblScoreTypeItem
    {
        $manager = $this->getEntityManager();

        $entity = new TblScoreTypeItem();
        $entity->setTblScoreType($tblScoreType);
        $entity->setValue($value);
        $entity->setName($name);
        $entity->setDescription($description);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }

    /**
     * @param array $tblScoreTypeItemList
     *
     * @return bool
     */
    public function destroyScoreTypeItemBulkList(array $tblScoreTypeItemList): bool
    {
        $manager = $this->getEntityManager();

        foreach ($tblScoreTypeItemList as $tblScoreTypeItem) {
            /** @var Element $entity */
            $entity = $manager->getEntityById('TblScoreTypeItem', $tblScoreTypeItem->getId());
            if (null !== $entity) {
                $manager->bulkKillEntity($entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $entity, true);
            }
        }

        $manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return TblScoreTypeItem[]
     */
    public function getScoreTypeItemListByScoreType(TblScoreType $tblScoreType): array
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreTypeItem',
            [TblScoreTypeItem::TBL_SCORE_TYPE => $tblScoreType->getId()], [TblScoreTypeItem::ATTR_VALUE => self::ORDER_ASC]) ?: [];
    }

    /**
     * @param TblScoreType|null $tblScoreType
     *
     * @return TblScoreTypeConversion[]
     */
    public function getScoreTypeConversionListByScoreType(?TblScoreType $tblScoreType): array
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreTypeConversion', [
            TblScoreTypeConversion::TBL_SCORE_TYPE => $tblScoreType?->getId()
        ]) ?: [];
    }

    /**
     * @param TblScoreType|null $tblScoreType
     * @param string $grade
     * @param string $value
     *
     * @return TblScoreTypeConversion
     */
    public function updateScoreTypeConversion(?TblScoreType $tblScoreType, string $grade, string $value): TblScoreTypeConversion
    {
        $manager = $this->getEntityManager();

        $entity = $manager->getEntity('TblScoreTypeConversion')->findOneBy([
            TblScoreTypeConversion::TBL_SCORE_TYPE => $tblScoreType?->getId(),
            TblScoreTypeConversion::ATTR_GRADE => $grade
        ]);

        if (null === $entity) {
            $entity = new TblScoreTypeConversion();
            $entity->setTblScoreType($tblScoreType);
            $entity->setValue($value);
            $entity->setGrade($grade);

            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);
        } else {
            $protocol = clone $entity;
            $entity->setValue($value);
            $entity->setGrade($grade);

            $manager->saveEntity($entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $protocol, $entity);
        }

        return $entity;
    }
}