<?php

namespace SPHERE\Application\Education\Absence\Service;

use DateTime;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsenceLesson;
use SPHERE\Application\Education\ClassRegister\Absence\Absence as AbsenceOld;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblYear $tblYear
     * @param array $tblDivisionList
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear, array $tblDivisionList): float
    {
        $start = hrtime(true);
        if ($tblDivisionList) {
            $Manager = $this->getEntityManager();
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblAbsenceOldList = AbsenceOld::useService()->getAbsenceAllByDivision($tblDivision))) {
                    $createEntityList = array();
                    // trennung nach mit Stunden und ohne Stunden
                    foreach ($tblAbsenceOldList as $tblAbsenceOld) {
                        if (($tblPerson = $tblAbsenceOld->getServiceTblPerson())) {
                            $tblAbsence = new TblAbsence(
                                $tblPerson, $tblAbsenceOld->getFromDateTime(), $tblAbsenceOld->getToDateTime(),
                                $tblAbsenceOld->getStatus(), $tblAbsenceOld->getType(), $tblAbsenceOld->getRemark(), $tblAbsenceOld->getIsCertificateRelevant(),
                                $tblAbsenceOld->getServiceTblPersonStaff() ?: null, $tblAbsenceOld->getServiceTblPersonCreator() ?: null, $tblAbsenceOld->getSource()
                            );

                            // bei vorhandenen Stunden kann die Fehlzeit nicht per Bulk gespeichert werden
                            if (($tblAbsenceLessonOldList = AbsenceOld::useService()->getAbsenceLessonAllByAbsence($tblAbsenceOld))) {
                                $Manager->saveEntity($tblAbsence);
                                foreach ($tblAbsenceLessonOldList as $tblAbsenceLessonOld) {
                                    $createEntityList[] = new TblAbsenceLesson($tblAbsence, $tblAbsenceLessonOld->getLesson());
                                }
                            } else {
                                $createEntityList[] = $tblAbsence;
                            }
                        }
                    }

                    foreach ($createEntityList as $tblAbsenceCreate) {
                        $Manager->bulkSaveEntity($tblAbsenceCreate);
                    }
                }
            }

            $Manager->flushCache();
        }

        $end = hrtime(true);

        return round(($end - $start) / 1000000000, 2);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, bool $isForced = false)
    {
        $parameters = array(TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId());

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence', $parameters);
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence', $parameters);
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblAbsence
     */
    public function getAbsenceById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence', $Id);
    }

    /**
     * @return false|TblAbsence[]
     */
    public function getAbsenceAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblAbsence');
    }

    /**
     * @param TblPerson $tblPerson
     * @param $FromDate
     * @param $ToDate
     * @param $Status
     * @param string $Remark
     * @param int $Type
     * @param TblPerson|null $tblPersonStaff
     * @param bool $IsCertificateRelevant
     * @param TblPerson|null $tblPersonCreator
     * @param int $Source
     *
     * @return TblAbsence
     */
    public function createAbsence(
        TblPerson $tblPerson,
        $FromDate,
        $ToDate,
        $Status,
        string $Remark = '',
        int $Type = TblAbsence::VALUE_TYPE_NULL,
        bool $IsCertificateRelevant = true,
        TblPerson $tblPersonCreator = null,
        TblPerson $tblPersonStaff = null,
        int $Source = TblAbsence::VALUE_SOURCE_STAFF
    ): TblAbsence {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblAbsence')->findOneBy(array(
            TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson,
            TblAbsence::ATTR_FROM_DATE => $FromDate ? new DateTime($FromDate) : null,
            TblAbsence::ATTR_TO_DATE => $ToDate ? new DateTime($ToDate) : null,
        ));
        if (null === $Entity) {
            $Entity = new TblAbsence(
                $tblPerson,
                $FromDate ? new DateTime($FromDate) : null,
                $ToDate ? new DateTime($ToDate) : null,
                $Status,
                $Type,
                $Remark,
                $IsCertificateRelevant,
                $tblPersonStaff,
                $tblPersonCreator,
                $Source
            );

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param $FromDate
     * @param $ToDate
     * @param $Status
     * @param $Remark
     * @param $Type
     * @param TblPerson|null $tblPersonStaff
     * @param bool $IsCertificateRelevant
     *
     * @return bool
     */
    public function updateAbsence(
        TblAbsence $tblAbsence,
        $FromDate,
        $ToDate,
        $Status,
        $Remark,
        $Type,
        TblPerson $tblPersonStaff = null,
        bool $IsCertificateRelevant = true
    ): bool {
        $Manager = $this->getEntityManager();
        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setFromDate($FromDate ? new DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new DateTime($ToDate) : null);
            $Entity->setStatus($Status);
            $Entity->setRemark($Remark);
            $Entity->setType($Type);
            $Entity->setServiceTblPersonStaff($tblPersonStaff);
            $Entity->setIsCertificateRelevant($IsCertificateRelevant);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyAbsence(
        TblAbsence $tblAbsence,
        bool $IsSoftRemove = false
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        if (null !== $Entity) {
            if (!$IsSoftRemove && ($tblAbsenceLessonList = $this->getAbsenceLessonAllByAbsence($Entity))) {
                foreach ($tblAbsenceLessonList as $tblAbsenceLesson) {
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblAbsenceLesson);
                    $Manager->killEntity($tblAbsenceLesson);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return bool
     */
    public function restoreAbsence(TblAbsence $tblAbsence): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetween(DateTime $fromDate, DateTime $toDate)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblAbsence', 't')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->between('t.FromDate', '?1', '?2'),
                $queryBuilder->expr()->between('t.ToDate', '?1', '?2'),
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->lte('t.FromDate', '?1'),
                    $queryBuilder->expr()->gte('t.ToDate', '?2')
                )
            ))
            ->setParameter(1, $fromDate)
            ->setParameter(2, $toDate)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param DateTime $fromDate
     * @param TblPerson $tblPerson
     * @param DateTime|null $toDate
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetweenByPerson(TblPerson $tblPerson, DateTime $fromDate, DateTime $toDate = null)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        if ($toDate) {
            $and = $queryBuilder->expr()->andX();
            $and->add($queryBuilder->expr()->eq('t.serviceTblPerson', '?3'));

            // Zeitraum überschneidet einen existierenden Zeitraum
            $or = $queryBuilder->expr()->orX();
            $or->add($queryBuilder->expr()->between('t.FromDate', '?1', '?2'));
            $or->add($queryBuilder->expr()->between('t.ToDate', '?1', '?2'));

            // Zeitraum liegt innerhalb eines existierenden Zeitraums
            $subAnd = $queryBuilder->expr()->andX();
            $subAnd->add($queryBuilder->expr()->lte('t.FromDate', '?1'));
            $subAnd->add($queryBuilder->expr()->gte('t.ToDate', '?2'));
            $or->add($subAnd);

            $and->add($or);

            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblAbsence', 't')
                ->where($and)
                ->setParameter(1, $fromDate)
                ->setParameter(2, $toDate ?: $fromDate)
                ->setParameter(3, $tblPerson->getId())
                ->getQuery();

            $resultList = $query->getResult();
        } else {
            // Tag liegt innerhalb eines existierenden Zeitraums
            $andFirst = $queryBuilder->expr()->andX();
            $andFirst->add($queryBuilder->expr()->eq('t.serviceTblPerson', '?3'));
            $andFirst->add($queryBuilder->expr()->lte('t.FromDate', '?1'));
            $andFirst->add($queryBuilder->expr()->gte('t.ToDate', '?1'));

            // Tag liegt auf existierenden Tag
            $andSecond = $queryBuilder->expr()->andX();
            $andSecond->add($queryBuilder->expr()->eq('t.serviceTblPerson', '?3'));
            $andSecond->add($queryBuilder->expr()->eq('t.FromDate', '?1'));

            $or = $queryBuilder->expr()->orX();
            $or->add($andFirst);
            $or->add($andSecond);

            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblAbsence', 't')
                ->where($or)
                ->setParameter(1, $fromDate)
                ->setParameter(3, $tblPerson->getId())
                ->getQuery();

            $resultList = $query->getResult();
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param integer $lesson
     *
     * @return TblAbsenceLesson
     */
    public function addAbsenceLesson(
        TblAbsence $tblAbsence,
        int $lesson
    ): TblAbsenceLesson {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAbsenceLesson')->findOneBy(array(
            TblAbsenceLesson::ATTR_TBL_ABSENCE => $tblAbsence->getId(),
            TblAbsenceLesson::ATTR_LESSON => $lesson
        ));

        if (null === $Entity) {
            $Entity = new TblAbsenceLesson($tblAbsence, $lesson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param integer $lesson
     *
     * @return bool
     */
    public function removeAbsenceLesson(TblAbsence $tblAbsence, int $lesson): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAbsenceLesson $Entity */
        $Entity = $Manager->getEntity('TblAbsenceLesson')
            ->findOneBy(array(
                TblAbsenceLesson::ATTR_TBL_ABSENCE => $tblAbsence->getId(),
                TblAbsenceLesson::ATTR_LESSON => $lesson
            ));

        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return false|TblAbsenceLesson[]
     */
    public function getAbsenceLessonAllByAbsence(TblAbsence $tblAbsence)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblAbsenceLesson',
            array(TblAbsenceLesson::ATTR_TBL_ABSENCE => $tblAbsence->getId()),
            array(TblAbsenceLesson::ATTR_LESSON => 'asc')
        );
    }


    /**
     * @param TblPerson $tblPerson
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param $Status
     *
     * @return bool
     */
    public function getHasPersonAbsenceLessons(TblPerson $tblPerson, DateTime $fromDate, DateTime $toDate, $Status): bool
    {
        $queryBuilder = $this->getEntityManager()->getQueryBuilder();

        // Zeitraum überschneidet einen existierenden Zeitraum
        $or = $queryBuilder->expr()->orX();
        $or->add($queryBuilder->expr()->between('a.FromDate', '?1', '?2'));
        $or->add($queryBuilder->expr()->between('a.ToDate', '?1', '?2'));

        // Zeitraum liegt innerhalb eines existierenden Zeitraums
        $subAnd = $queryBuilder->expr()->andX();
        $subAnd->add($queryBuilder->expr()->lte('a.FromDate', '?1'));
        $subAnd->add($queryBuilder->expr()->gte('a.ToDate', '?2'));
        $or->add($subAnd);

        $query = $queryBuilder->select('l')
            ->from(__NAMESPACE__ . '\Entity\TblAbsence', 'a')
            ->join(__NAMESPACE__ . '\Entity\TblAbsenceLesson', 'l')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('l.tblAbsence', 'a.Id'),
                    $queryBuilder->expr()->eq('a.serviceTblPerson', '?3'),
                    $queryBuilder->expr()->eq('a.Status', '?4'),
                    $queryBuilder->expr()->eq('a.IsCertificateRelevant', 1),
                    $or
                )
            )
            ->setParameter(1, $fromDate)
            ->setParameter(2, $toDate)
            ->setParameter(3, $tblPerson->getId())
            ->setParameter(4, $Status)
            ->getQuery();

        $resultList = $query->getResult();

        return !empty($resultList);
    }
}