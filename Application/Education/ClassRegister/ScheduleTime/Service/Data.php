<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime\Service;

use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity\TblScheduleTime;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity\TblScheduleTimeSchoolType;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity\TblScheduleTimeSlot;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $id
     *
     * @return TblScheduleTime|bool
     */
    public function getScheduleTimeById($id): TblScheduleTime|bool
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScheduleTime', $id);
    }

    /**
     * @return TblScheduleTime[]
     */
    public function getScheduleTimeAll(): array
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblScheduleTime') ?: [];
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return TblScheduleTimeSchoolType[]
     */
    public function getScheduleTimeSchoolTypesByScheduleTime(TblScheduleTime $tblScheduleTime): array
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScheduleTimeSchoolType', [
            TblScheduleTimeSchoolType::ATTR_TABLE_SCHEDULE_TIME => $tblScheduleTime->getId()
        ]) ?: [];
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return TblScheduleTimeSlot[]
     */
    public function getScheduleTimeSlotsByScheduleTime(TblScheduleTime $tblScheduleTime): array
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScheduleTimeSlot', [
            TblScheduleTimeSlot::ATTR_TABLE_SCHEDULE_TIME => $tblScheduleTime->getId()
        ], [TblScheduleTimeSlot::ATTR_LESSON => self::ORDER_ASC]) ?: [];
    }

    /**
     * @param string $name
     * @param int $secondaryLevel
     *
     * @return TblScheduleTime
     */
    public function createScheduleTime(string $name, int $secondaryLevel): TblScheduleTime
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = (new TblScheduleTime())
            ->setName($name)
            ->setSecondaryLevel($secondaryLevel);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     * @param string $name
     * @param int $secondaryLevel
     *
     * @return TblScheduleTime|null
     */
    public function updateScheduleTime(TblScheduleTime $tblScheduleTime, string $name, int $secondaryLevel): ?TblScheduleTime
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScheduleTime $Entity*/
        $Entity = $Manager->getEntityById('TblScheduleTime', $tblScheduleTime->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity
                ->setName($name)
                ->setSecondaryLevel($secondaryLevel);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return $Entity;
        }

        return null;
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return bool
     */
    public function deleteScheduleTime(TblScheduleTime $tblScheduleTime): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = clone $tblScheduleTime;
        $Manager->killEntity($tblScheduleTime);
        Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

        return true;
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     * @param TblType $tblSchoolType
     *
     * @return TblScheduleTimeSchoolType|null
     */
    public function createScheduleTimeSchoolType(TblScheduleTime $tblScheduleTime, TblType $tblSchoolType): ?TblScheduleTimeSchoolType
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblScheduleTimeSchoolType')->findOneBy(array(
            TblScheduleTimeSchoolType::ATTR_TABLE_SCHEDULE_TIME => $tblScheduleTime->getId(),
            TblScheduleTimeSchoolType::SERVICE_TABLE_SCHOOL_TYPE => $tblSchoolType->getId(),
        ));
        /** @var TblScheduleTimeSchoolType $Entity*/
        if (null === $Entity) {
            $Entity = (new TblScheduleTimeSchoolType())
                ->setTblScheduleTime($tblScheduleTime)
                ->setServiceTblSchoolType($tblSchoolType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScheduleTime $tblScheduleTime
     * @param int $lesson
     * @param string $startTime
     * @param string $endTime
     *
     * @return TblScheduleTimeSlot|null
     */
    public function createScheduleTimeSlot(TblScheduleTime $tblScheduleTime, int $lesson, string $startTime, string $endTime): ?TblScheduleTimeSlot
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblScheduleTimeSlot')->findOneBy(array(
            TblScheduleTimeSlot::ATTR_TABLE_SCHEDULE_TIME => $tblScheduleTime->getId(),
            TblScheduleTimeSlot::ATTR_LESSON => $lesson,
        ));
        /** @var TblScheduleTimeSlot $Entity*/
        if (null === $Entity) {
            $Entity = (new TblScheduleTimeSlot())
                ->setTblScheduleTime($tblScheduleTime)
                ->setLesson($lesson)
                ->setStartTime($startTime)
                ->setEndTime($endTime);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScheduleTimeSlot $tblScheduleTimeSlot
     * @param string $startTime
     * @param string $endTime
     *
     * @return TblScheduleTimeSlot|null
     */
    public function updateScheduleTimeSlot(TblScheduleTimeSlot $tblScheduleTimeSlot, string $startTime, string $endTime): ?TblScheduleTimeSlot
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScheduleTimeSlot $Entity*/
        $Entity = $Manager->getEntityById('TblScheduleTimeSlot', $tblScheduleTimeSlot->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity
                ->setStartTime($startTime)
                ->setEndTime($endTime);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return $Entity;
        }

        return null;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {

            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());

            $Manager->bulkKillEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
}