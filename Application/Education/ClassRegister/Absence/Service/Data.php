<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.07.2016
 * Time: 08:57
 */

namespace SPHERE\Application\Education\ClassRegister\Absence\Service;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsenceLesson;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * @deprecated
 *
 * Class Data
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewAbsence[]
     */
    public function viewAbsence()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewAbsence'
        );
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision|null $tblDivision
     * @param bool $isForced
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, TblDivision $tblDivision = null, $isForced = false)
    {

        if ($tblDivision) {
            $parameters = array(
                TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblAbsence::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            );
        } else {
            $parameters = array(
                TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            );
        }

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
                $parameters
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
                $parameters
            );
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByDivision(TblDivision $tblDivision)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
            array(
                TblAbsence::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            )
        );
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
     * @param TblDivision $tblDivision
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
        TblDivision $tblDivision,
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

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAbsence')->findOneBy(array(
            TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson,
            TblAbsence::ATTR_SERVICE_TBL_DIVISION => $tblDivision,
            TblAbsence::ATTR_FROM_DATE => $FromDate ? new DateTime($FromDate) : null,
            TblAbsence::ATTR_TO_DATE => $ToDate ? new DateTime($ToDate) : null,
        ));

        if (null === $Entity) {
            $Entity = new TblAbsence();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setFromDate($FromDate ? new DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new DateTime($ToDate) : null);
            $Entity->setStatus($Status);
            $Entity->setRemark($Remark);
            $Entity->setType($Type);
            $Entity->setServiceTblPersonStaff($tblPersonStaff);
            $Entity->setIsCertificateRelevant($IsCertificateRelevant);
            $Entity->setSource($Source);
            $Entity->setServiceTblPersonCreator($tblPersonCreator);

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
    ) {

        $Manager = $this->getConnection()->getEntityManager();

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
        $IsSoftRemove = false
    ){

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
    public function restoreAbsence(TblAbsence $tblAbsence)
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
     * @param DateTime $toDate
     * @param TblDivision $tblDivision
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetweenByDivision(DateTime $fromDate, DateTime $toDate, TblDivision $tblDivision)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $and = $queryBuilder->expr()->andX();
        $and->add($queryBuilder->expr()->eq('t.serviceTblDivision', '?3'));

        $or = $queryBuilder->expr()->orX();
        $or->add($queryBuilder->expr()->between('t.FromDate', '?1', '?2'));
        $or->add($queryBuilder->expr()->between('t.ToDate', '?1', '?2'));
        $or->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->lte('t.FromDate', '?1'),
                $queryBuilder->expr()->gte('t.ToDate', '?2')
            )
        );

        $and->add($or);

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblAbsence', 't')
            ->where($and)
            ->setParameter(1, $fromDate)
            ->setParameter(2, $toDate)
            ->setParameter(3, $tblDivision->getId())
            ->orderBy('t.FromDate', 'ASC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param DateTime $fromDate
     * @param TblPerson $tblPerson
     * @param DateTime|null $toDate
     *
     * @return array|bool
     */
    public function getAbsenceAllBetweenByPerson(DateTime $fromDate, TblPerson $tblPerson, DateTime $toDate = null)
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
                ->setParameter(2, $toDate ? $toDate : $fromDate)
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
        $lesson
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAbsenceLesson')->findOneBy(array(
            TblAbsenceLesson::ATTR_TBL_ABSENCE => $tblAbsence->getId(),
            TblAbsenceLesson::ATTR_LESSON => $lesson
        ));

        if (null === $Entity) {
            $Entity = new TblAbsenceLesson();
            $Entity->setTblAbsence($tblAbsence);
            $Entity->setLesson($lesson);

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
    public function removeAbsenceLesson(TblAbsence $tblAbsence, $lesson)
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
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblAbsenceLesson', array(
            TblAbsenceLesson::ATTR_TBL_ABSENCE => $tblAbsence->getId()
        ), array(TblAbsenceLesson::ATTR_LESSON => 'asc'));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param $Status
     *
     * @return bool
     */
    public function hasPersonAbsenceLessons(TblPerson $tblPerson, TblDivision $tblDivision, $Status)
    {
        $queryBuilder = $this->getEntityManager()->getQueryBuilder();

        $query = $queryBuilder->select('l')
            ->from(__NAMESPACE__ . '\Entity\TblAbsence', 'a')
            ->join(__NAMESPACE__ . '\Entity\TblAbsenceLesson', 'l')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('l.tblAbsence', 'a.Id'),
                    $queryBuilder->expr()->eq('a.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('a.serviceTblDivision', '?2'),
                    $queryBuilder->expr()->eq('a.Status', '?3')
                )
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblDivision->getId())
            ->setParameter(3, $Status)
            ->getQuery();

        $resultList = $query->getResult();

        return !empty($resultList);
    }
}