<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service;

use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableWeek;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\ClassRegister\Timetable\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     * @return TblTimetable|null
     * @throws \Exception
     */
    public function getTimetableById($Id): ?TblTimetable
    {

        /* @var TblTimetable $Entity */
        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblTimetable', $Id);
        return (false === $Entity ? null : $Entity);
    }

    /**
     * @param string $Name
     * @param \DateTime $DateFrom
     * @param \DateTime $DateTo
     * @return null|TblTimetable
     * @throws \Exception
     */
    public function getTimetableByNameAndTime(string $Name, \DateTime $DateFrom, \DateTime $DateTo): ?TblTimetable
    {
        /* @var TblTimetable $Entity */
        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblTimetable',
            array(
                TblTimetable::ATTR_NAME => $Name,
                TblTimetable::ATTR_DATE_FROM => $DateFrom,
                TblTimetable::ATTR_DATE_TO => $DateTo
            ));
        return (false === $Entity ? null : $Entity);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return mixed
     */
    public function getTimetableNodeListByTimetable(TblTimetable $tblTimetable)
    {

        /* @var TblTimetableNode[]|false $EntityList */
        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblTimetableNode',
            array(
                TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId()
            ));
        return (false === $EntityList ? null : $EntityList);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return TblTimetableWeek[]|null
     */
    public function getTimetableWeekListByTimetable(TblTimetable $tblTimetable)
    {

        /* @var TblTimetableWeek[]|false $EntityList */
        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblTimetableWeek',
            array(
                TblTimetableWeek::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId()
            ));
        return (false === $EntityList ? null : $EntityList);
    }

    /**
     * @return TblTimetable[]|null
     */
    public function getTimetableAll(): ?array
    {

        /* @var TblTimetable[] $EntityList */
        $EntityList = $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblTimetable');
        return (false === $EntityList ? null : $EntityList);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param \DateTime $DateFrom
     * @param \DateTime $DateTo
     * @return TblTimetable|null
     */
    public function createTimetable($Name, $Description, \DateTime $DateFrom, \DateTime $DateTo): ?TblTimetable
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblTimetable')->findOneBy(array(
            TblTimetable::ATTR_NAME => $Name,
//            TblTimetable::ATTR_DESCRIPTION => $Description,
            TblTimetable::ATTR_DATE_FROM => $DateFrom,
            TblTimetable::ATTR_DATE_TO => $DateTo,
        ));
        /** @var TblTimetable $Entity*/
        if (null === $Entity) {
            $Entity = new TblTimetable();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setDateFrom($DateFrom);
            $Entity->setDateTo($DateTo);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblTimetable $tblTimetable,
     * @param array $ImportList
     * required ArrayKeys
     * [Hour]
     * [Day]
     * [Week]
     * [Room]
     * [SubjectGroup]
     * [Level]
     * [tblCourse]
     * [tblSubject]
     * [tblPerson]
     *
     * @return bool
     */
    public function createTimetableNodeBulk(TblTimetable $tblTimetable, array $ImportList): bool
    {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Row) {
                $Entity = new TblTimetableNode();
                $Entity->setHour($Row['Hour']);
                $Entity->setDay($Row['Day']);
                $Entity->setWeek($Row['Week']);
                $Entity->setRoom($Row['Room']);
                $Entity->setSubjectGroup($Row['SubjectGroup']);
                $Entity->setLevel($Row['Level']);
                $Entity->setServiceTblCourse($Row['tblCourse']);
                $Entity->setServiceTblSubject($Row['tblSubject']);
                $Entity->setServiceTblPerson($Row['tblPerson']);
                $Entity->setTblTimetable($tblTimetable);
                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;

    }

    /**
     * @param TblTimetable $tblTimetable
     * @param array $ImportList
     * required ArrayKeys
     * [number]
     * [week]
     * [date]
     *
     * @return bool
     */
    public function createTimetableWeekBulk(TblTimetable $tblTimetable, $ImportList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Row) {
                $Entity = new TblTimetableWeek();
                $Entity->setNumber($Row['Number']);
                $Entity->setWeek($Row['Week']);
                $Entity->setDate(new \DateTime($Row['Date']));
                $Entity->setTblTimetable($tblTimetable);
                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;

    }

    /**
     * @param $tblTimetableNodeList
     * @return bool
     */
    public function removeTimetableNodeList($tblTimetableNodeList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($tblTimetableNodeList)) {
            foreach ($tblTimetableNodeList as $Entity) {
                $Manager->bulkKillEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param $tblTimetableWeekList
     * @return bool
     */
    public function removeTimetableWeekList($tblTimetableWeekList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($tblTimetableWeekList)) {
            foreach ($tblTimetableWeekList as $Entity) {
                $Manager->bulkKillEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblTimetable $tblTimeTable
     * @return bool
     */
    public function removeTimetable(TblTimetable $tblTimeTable)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = clone $tblTimeTable;
        $Manager->killEntity($tblTimeTable);
        Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
        return true;
    }

    /**
     * @return bool
     */
    public function destroyTimetableAllBulk(): bool
    {

        $Manager = $this->getConnection()->getEntityManager();
        // Test - ToDO nur die Einträge löschen, welche nicht beim erneuten Import dabei sind
        $EntityList = $Manager->getEntity('TblTimetable')
            ->findAll();
        if (!empty($EntityList)) {
            foreach ($EntityList as $Entity) {
                $Manager->bulkKillEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }
}
