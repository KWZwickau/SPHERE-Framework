<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacement;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableWeek;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

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
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     *
     * @return null|TblTimetable
     */
    public function getTimetableByNameAndTime(string $Name, DateTime $DateFrom, DateTime $DateTo): ?TblTimetable
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
     * @param DateTime $Date
     *
     * @return TblTimetable[]|false
     */
    public function getTimetableListByDateTime(DateTime $Date)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblTimetable', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->lte('t.DateFrom', '?1'),
                    $queryBuilder->expr()->gte('t.DateTo', '?1')
                )
            )
            ->setParameter(1, $Date)
            ->orderBy('t.DateFrom', 'DESC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return false|TblTimetableNode[]|null
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param Int $Day
     * @param int|null $lesson
     * @param TblPerson|null $tblPerson
     *
     * @return false|TblTimetableNode[]
     */
    public function getTimetableNodeListBy(TblTimetable $tblTimetable, TblDivisionCourse $tblDivisionCourse, int $Day, ?int $lesson, ?TblPerson $tblPerson)
    {
        if ($lesson !== null) {
            if ($tblPerson) {
                return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTimetableNode', array(
                    TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId(),
                    TblTimetableNode::ATTR_SERVICE_TBL_COURSE => $tblDivisionCourse->getId(),
                    TblTimetableNode::ATTR_DAY => $Day,
                    TblTimetableNode::ATTR_HOUR => $lesson,
                    TblTimetableNode::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
            } else {
                return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTimetableNode', array(
                    TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId(),
                    TblTimetableNode::ATTR_SERVICE_TBL_COURSE => $tblDivisionCourse->getId(),
                    TblTimetableNode::ATTR_DAY => $Day,
                    TblTimetableNode::ATTR_HOUR => $lesson
                ));
            }
        } else {
            if ($tblPerson) {
                return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTimetableNode', array(
                    TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId(),
                    TblTimetableNode::ATTR_SERVICE_TBL_COURSE => $tblDivisionCourse->getId(),
                    TblTimetableNode::ATTR_DAY => $Day,
                    TblTimetableNode::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
            } else {
                return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTimetableNode', array(
                    TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId(),
                    TblTimetableNode::ATTR_SERVICE_TBL_COURSE => $tblDivisionCourse->getId(),
                    TblTimetableNode::ATTR_DAY => $Day,
                ));
            }
        }
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param Int $Day
     * @param TblPerson|null $tblPerson
     *
     * @return false|TblTimetableNode[]
     */
    public function getTimetableNodeListByDayAndPerson(TblTimetable $tblTimetable, Int $Day, TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTimetableNode', array(
            TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId(),
            TblTimetableNode::ATTR_DAY => $Day,
            TblTimetableNode::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ), array(
            TblTimetableNode::ATTR_HOUR => self::ORDER_ASC
        ));
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
     * @param TblTimetable $tblTimetable
     * @param string $week
     * @param DateTime $dateTime
     *
     * @return false|TblTimetableWeek
     */
    public function getTimetableWeekByTimeTableAndWeekAndDate(TblTimetable $tblTimetable,string $week , DateTime $dateTime)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblTimetableWeek', array(
            TblTimetableWeek::ATTR_TBL_CLASS_REGISTER_TIMETABLE => $tblTimetable->getId(),
            TblTimetableWeek::ATTR_WEEK => $week,
            TblTimetableWeek::ATTR_DATE => $dateTime,
        ));
    }

    /**
     * @param DateTime $Date
     * @param $tblPerson
     * @param $tblCourse
     * @param $Hour
     * @return TblTimetableReplacement[]|null
     */
    public function getTimetableReplacementByTime(DateTime $Date, $tblPerson = null, $tblCourse = null, $Hour = null)
    {

        $Search = array(TblTimetableReplacement::ATTR_DATE => $Date);
        if($tblCourse){
            /** @var Element $tblCourse */
            $Search[TblTimetableReplacement::ATTR_SERVICE_TBL_COURSE] = $tblCourse->getId();
        }
        if($Hour){
            $Search[TblTimetableReplacement::ATTR_HOUR] = $Hour;
        }
        if($tblPerson){
            /** @var TblPerson $tblPerson */
            $Search[TblTimetableReplacement::ATTR_SERVICE_TBL_PERSON] = $tblPerson->getId();
        }

        /* @var TblTimetableReplacement[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblTimetableReplacement',
            $Search);
        return (false === $EntityList ? null : $EntityList);
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblTimetableReplacement[]|bool
     */
    public function getTimetableReplacementByDate(DateTime $fromDate, DateTime $toDate)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblTimetableReplacement', 't')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->between('t.Date', '?1', '?2'),
            ))
            ->setParameter(1, $fromDate)
            ->setParameter(2, $toDate)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
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
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     * @return TblTimetable|null
     */
    public function createTimetable($Name, $Description, DateTime $DateFrom, DateTime $DateTo): ?TblTimetable
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
                $Entity->setDate(new DateTime($Row['Date']));
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
     * @param array $ImportList
     * required ArrayKeys
     * [Date]
     * [Hour]
     * [Room]
     * [subjectGroup]
     * [IsCanceled]
     * [tblSubject]
     * [tblSubstituteSubject]
     * [tblCourse]
     * [tblPerson]
     *
     * @return bool
     */
    public function createTimetableReplacementBulk($ImportList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Row) {
                $Entity = new TblTimetableReplacement();
                $Entity->setDate($Row['Date']);
                $Entity->setHour($Row['Hour']);
                $Entity->setRoom($Row['Room']);
                if(isset($Row['IsCanceled'])){
                    $Entity->setIsCanceled($Row['IsCanceled']);
                } else {
                    $Entity->setIsCanceled(false);
                }
                $Entity->setSubjectGroup($Row['SubjectGroup']);
                if(!$Row['tblSubject']){
                    $Row['tblSubject'] = null;
                }
                $Entity->setServiceTblSubject($Row['tblSubject']);
                if(!$Row['tblSubstituteSubject']){
                    $Row['tblSubstituteSubject'] = null;
                }
                $Entity->setServiceTblSubstituteSubject($Row['tblSubstituteSubject']);
                $Entity->setServiceTblCourse($Row['tblCourse']);
                $Entity->setServiceTblPerson($Row['tblPerson']);
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
     * @param TblTimetable $tblTimeTable
     * @param string       $Name
     * @param string       $Description
     * @param DateTime     $DateFrom
     * @param DateTime     $DateTo
     *
     * @return TblTimetable|null
     */
    public function updateTimetable(TblTimetable $tblTimeTable, string $Name, string $Description, DateTime $DateFrom, DateTime $DateTo): ?TblTimetable
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTimetable $Entity*/
        $Entity = $Manager->getEntityById('TblTimetable', $tblTimeTable->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setDateFrom($DateFrom);
            $Entity->setDateTo($DateTo);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
        }
        return null;
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

    /**
     * @return bool
     */
    public function destroyTimetableReplacementBulk($EntityList): bool
    {

        $Manager = $this->getConnection()->getEntityManager();
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
