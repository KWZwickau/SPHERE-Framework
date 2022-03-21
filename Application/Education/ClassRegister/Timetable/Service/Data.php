<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service;

use DateTime as DateTime;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
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
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     * @return null|TblTimetable
     * @throws \Exception
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
     * [stunde]
     * [tag]
     * [woche]
     * [raum]
     * [gruppe]
     * [stufe]
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
                $Entity->setHour($Row['stunde']);
                $Entity->setDay($Row['tag']);
                $Entity->setWeek($Row['woche']);
                $Entity->setRoom($Row['raum']);
                $Entity->setSubjectGroup($Row['gruppe']);
                $Entity->setLevel($Row['stufe']);
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
