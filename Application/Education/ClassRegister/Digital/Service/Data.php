<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContentLink;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonWeek;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data  extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Room
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblPerson
     * @param TblSubject|null $tblSubject
     * @param TblSubject|null $tblSubstituteSubject
     * @param bool $IsCanceled
     *
     * @return TblLessonContent
     */
    public function createLessonContent(
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Room,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null,
        TblYear $tblYear = null,
        TblPerson $tblPerson = null,
        TblSubject $tblSubject = null,
        TblSubject $tblSubstituteSubject = null,
        bool $IsCanceled = false
    ): TblLessonContent {

        $Manager = $this->getEntityManager();

        $Entity = new TblLessonContent();
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setLesson($Lesson);
        $Entity->setContent($Content);
        $Entity->setHomework($Homework);
        $Entity->setRoom($Room);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblGroup($tblGroup);
        $Entity->setServiceTblYear($tblYear);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblSubstituteSubject($tblSubstituteSubject);
        $Entity->setIsCanceled($IsCanceled);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Room
     * @param TblPerson|null $tblPerson
     * @param TblSubject|null $tblSubject
     * @param TblSubject|null $tblSubstituteSubject
     * @param bool $IsCanceled
     *
     * @return bool
     */
    public function updateLessonContent(
        TblLessonContent $tblLessonContent,
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Room,
        TblPerson $tblPerson = null,
        TblSubject $tblSubject = null,
        TblSubject $tblSubstituteSubject = null,
        bool $IsCanceled = false
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblLessonContent $Entity */
        $Entity = $Manager->getEntityById('TblLessonContent', $tblLessonContent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setLesson($Lesson);
            $Entity->setContent($Content);
            $Entity->setHomework($Homework);
            $Entity->setRoom($Room);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setServiceTblSubstituteSubject($tblSubstituteSubject);
            $Entity->setIsCanceled($IsCanceled);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return bool
     */
    public function destroyLessonContent(TblLessonContent $tblLessonContent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblLessonContent $Entity */
        $Entity = $Manager->getEntityById('TblLessonContent', $tblLessonContent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }
    
    /**
     * @param $Id
     *
     * @return false|TblLessonContent
     */
    public function getLessonContentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblLessonContent', $Id);
    }

    /**
     * @param DateTime $date
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDate(DateTime $date, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent', array(
            TblLessonContent::ATTR_DATE => $date,
            TblLessonContent::ATTR_SERVICE_TBL_DIVISION => $tblDivision ? $tblDivision->getId() : null,
            TblLessonContent::ATTR_SERVICE_TBL_GROUP => $tblGroup ? $tblGroup->getId() : null
        ), array(TblLessonContent::ATTR_LESSON => self::ORDER_ASC) );
    }

    /**
     * @param DateTime $date
     * @param int|null $lesson
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDateAndLesson(DateTime $date, ?int $lesson, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        if ($lesson !== null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent', array(
                TblLessonContent::ATTR_DATE => $date,
                TblLessonContent::ATTR_LESSON => $lesson,
                TblLessonContent::ATTR_SERVICE_TBL_DIVISION => $tblDivision ? $tblDivision->getId() : null,
                TblLessonContent::ATTR_SERVICE_TBL_GROUP => $tblGroup ? $tblGroup->getId() : null
            ), array(Element::ENTITY_CREATE => self::ORDER_ASC));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent', array(
                TblLessonContent::ATTR_DATE => $date,
                TblLessonContent::ATTR_SERVICE_TBL_DIVISION => $tblDivision ? $tblDivision->getId() : null,
                TblLessonContent::ATTR_SERVICE_TBL_GROUP => $tblGroup ? $tblGroup->getId() : null
            ), array(Element::ENTITY_CREATE => self::ORDER_ASC));
        }
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByBetween(DateTime $fromDate, DateTime $toDate, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        if ($tblDivision) {
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblLessonContent', 't')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->between('t.Date', '?1', '?2'),
                        $queryBuilder->expr()->eq('t.serviceTblDivision', '?3')
                    )
                )
                ->setParameter(1, $fromDate)
                ->setParameter(2, $toDate)
                ->setParameter(3, $tblDivision->getId())
                ->getQuery();
        } else {
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblLessonContent', 't')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->between('t.Date', '?1', '?2'),
                        $queryBuilder->expr()->eq('t.serviceTblGroup', '?3')
                    )
                )
                ->setParameter(1, $fromDate)
                ->setParameter(2, $toDate)
                ->setParameter(3, $tblGroup->getId())
                ->getQuery();
        }

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param DateTime $toDate
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return TblLessonContent[]|false
     */
    public function getLessonContentCanceledAllByToDate(DateTime $toDate, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        if ($tblDivision) {
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblLessonContent', 't')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lte('t.Date', '?1'),
                        $queryBuilder->expr()->eq('t.serviceTblDivision', '?2'),
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->eq('t.IsCanceled', '?3'),
                            $queryBuilder->expr()->isNotNull('t.serviceTblSubstituteSubject'),
                        )
                    )
                )
                ->setParameter(1, $toDate)
                ->setParameter(2, $tblDivision->getId())
                ->setParameter(3, 1)
                ->getQuery();
        } else {
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblLessonContent', 't')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lte('t.Date', '?1'),
                        $queryBuilder->expr()->eq('t.serviceTblGroup', '?2'),
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->eq('t.IsCanceled', '?3'),
                            $queryBuilder->expr()->isNotNull('t.serviceTblSubstituteSubject'),
                        )
                    )
                )
                ->setParameter(1, $toDate)
                ->setParameter(2, $tblGroup->getId())
                ->setParameter(3, 1)
                ->getQuery();
        }

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return TblSubject[]|false
     */
    public function getSubjectListFromLessonContent(TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        if ($tblDivision) {
            $query = $queryBuilder->select('t.serviceTblSubject as SubjectId, t.serviceTblSubstituteSubject as SubstituteSubjectId')
                ->from(__NAMESPACE__ . '\Entity\TblLessonContent', 't')
                ->where(
                    $queryBuilder->expr()->eq('t.serviceTblDivision', '?1')
                )
                ->setParameter(1, $tblDivision->getId())
                ->distinct()
                ->getQuery();
        } else {
            $query = $queryBuilder->select('t.serviceTblSubject as SubjectId, t.serviceTblSubstituteSubject as SubstituteSubjectId')
                ->from(__NAMESPACE__ . '\Entity\TblLessonContent', 't')
                ->where(
                    $queryBuilder->expr()->eq('t.serviceTblGroup', '?1')
                )
                ->setParameter(1, $tblGroup->getId())
                ->distinct()
                ->getQuery();
        }

        $resultList = $query->getResult();

        $tblSubjectList = array();
        if ($resultList) {
            foreach ($resultList as $result) {
                $subjectId = $result["SubjectId"];
                $substituteSubjectId = $result["SubstituteSubjectId"];
                if ($subjectId && !isset($tblSubjectList[$subjectId]) && ($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                    $tblSubjectList[$tblSubject->getAcronym()] = $tblSubject;
                }
                if ($substituteSubjectId && !isset($tblSubjectList[$substituteSubjectId]) && ($tblSubstituteSubject = Subject::useService()->getSubjectById($substituteSubjectId))) {
                    $tblSubjectList[$tblSubstituteSubject->getAcronym()] = $tblSubstituteSubject;
                }
            }
        }

        return empty($tblSubjectList) ? false : $tblSubjectList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Remark
     * @param $Room
     * @param $IsDoubleLesson
     * @param TblPerson|null $tblPerson
     *
     * @return TblCourseContent
     */
    public function createCourseContent(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup,
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Remark,
        $Room,
        $IsDoubleLesson,
        TblPerson $tblPerson = null
    ): TblCourseContent {

        $Manager = $this->getEntityManager();

        $Entity = new TblCourseContent();
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblSubjectGroup($tblSubjectGroup);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setLesson($Lesson);
        $Entity->setContent($Content);
        $Entity->setHomework($Homework);
        $Entity->setRemark($Remark);
        $Entity->setRoom($Room);
        $Entity->setIsDoubleLesson($IsDoubleLesson);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblCourseContent $tblCourseContent
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $Remark
     * @param $Room
     * @param $IsDoubleLesson
     * @param TblPerson|null $tblPerson
     *
     * @return bool
     */
    public function updateCourseContent(
        TblCourseContent $tblCourseContent,
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $Remark,
        $Room,
        $IsDoubleLesson,
        TblPerson $tblPerson = null
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCourseContent $Entity */
        $Entity = $Manager->getEntityById('TblCourseContent', $tblCourseContent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setLesson($Lesson);
            $Entity->setContent($Content);
            $Entity->setHomework($Homework);
            $Entity->setRemark($Remark);
            $Entity->setRoom($Room);
            $Entity->setIsDoubleLesson($IsDoubleLesson);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param array $tblCourseContentList
     * @param string $dateHeadmaster
     * @param TblPerson $tblPersonHeadmaster
     */
    public function updateBulkCourseContent(array $tblCourseContentList, string $dateHeadmaster, TblPerson $tblPersonHeadmaster)
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCourseContent $Entity */
        foreach ($tblCourseContentList as $tblCourseContent) {
            $Entity = $Manager->getEntityById('TblCourseContent', $tblCourseContent->getId());
            $Protocol = clone $Entity;
            if (null !== $Entity) {
                $Entity->setDateHeadmaster($dateHeadmaster ? new DateTime($dateHeadmaster) : null);
                $Entity->setServiceTblPersonHeadmaster($tblPersonHeadmaster);

                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblCourseContent $tblCourseContent
     *
     * @return bool
     */
    public function destroyCourseContent(TblCourseContent $tblCourseContent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCourseContent $Entity */
        $Entity = $Manager->getEntityById('TblCourseContent', $tblCourseContent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblCourseContent
     */
    public function getCourseContentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblCourseContent', $Id);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return false|TblCourseContent[]
     */
    public function getCourseContentListBy(TblDivision $tblDivision, TblSubject $tblSubject,TblSubjectGroup $tblSubjectGroup)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblCourseContent', array(
            TblCourseContent::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
            TblCourseContent::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
            TblCourseContent::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
        ));
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblYear|null $tblYear
     * @param $Date
     * @param $Remark
     * @param $DateDivisionTeacher
     * @param TblPerson|null $serviceTblPersonDivisionTeacher
     * @param $DateHeadmaster
     * @param TblPerson|null $serviceTblPersonHeadmaster
     *
     * @return TblLessonWeek
     */
    public function createLessonWeek(
        ?TblDivision $tblDivision,
        ?TblGroup $tblGroup,
        ?TblYear $tblYear,
        $Date,
        $Remark,
        $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher,
        $DateHeadmaster,
        ?TblPerson $serviceTblPersonHeadmaster
    ): TblLessonWeek {

        $Manager = $this->getEntityManager();

        $Entity = new TblLessonWeek();
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblGroup($tblGroup);
        $Entity->setServiceTblYear($tblYear);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setRemark($Remark);
        $Entity->setDateDivisionTeacher($DateDivisionTeacher ? new DateTime($DateDivisionTeacher) : null);
        $Entity->setServiceTblPersonDivisionTeacher($serviceTblPersonDivisionTeacher);
        $Entity->setDateHeadmaster($DateHeadmaster ? new DateTime($DateHeadmaster) : null);
        $Entity->setServiceTblPersonHeadmaster($serviceTblPersonHeadmaster);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblLessonWeek $tblLessonWeek
     * @param $Remark
     * @param $DateDivisionTeacher
     * @param TblPerson|null $serviceTblPersonDivisionTeacher
     * @param $DateHeadmaster
     * @param TblPerson|null $serviceTblPersonHeadmaster
     *
     * @return bool
     */
    public function updateLessonWeek(
        TblLessonWeek $tblLessonWeek,
        $Remark,
        $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher,
        $DateHeadmaster,
        ?TblPerson $serviceTblPersonHeadmaster
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblLessonWeek $Entity */
        $Entity = $Manager->getEntityById('TblLessonWeek', $tblLessonWeek->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setRemark($Remark);
            $Entity->setDateDivisionTeacher($DateDivisionTeacher ? new DateTime($DateDivisionTeacher) : null);
            $Entity->setServiceTblPersonDivisionTeacher($serviceTblPersonDivisionTeacher);
            $Entity->setDateHeadmaster($DateHeadmaster ? new DateTime($DateHeadmaster) : null);
            $Entity->setServiceTblPersonHeadmaster($serviceTblPersonHeadmaster);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLessonWeek $tblLessonWeek
     * @param $Remark
     *
     * @return bool
     */
    public function updateLessonWeekRemark(
        TblLessonWeek $tblLessonWeek,
        $Remark
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblLessonWeek $Entity */
        $Entity = $Manager->getEntityById('TblLessonWeek', $tblLessonWeek->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setRemark($Remark);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param DateTime $dateTime
     *
     * @return false|TblLessonWeek
     */
    public function getLessonWeekAllByDate(?TblDivision $tblDivision, ?TblGroup $tblGroup, DateTime $dateTime)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLessonWeek', array(
            TblLessonWeek::ATTR_SERVICE_TBL_DIVISION => $tblDivision ? $tblDivision->getId() : null,
            TblLessonWeek::ATTR_SERVICE_TBL_GROUP => $tblGroup ? $tblGroup->getId() : null,
            TblLessonWeek::ATTR_DATE => $dateTime
        ));
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param int $LinkId
     *
     * @return TblLessonContentLink
     */
    public function createLessonContentLink(TblLessonContent $tblLessonContent, int $LinkId): TblLessonContentLink
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblLessonContentLink')
            ->findOneBy(
                array(
                    TblLessonContentLink::ATTR_TBL_LESSON_CONTENT => $tblLessonContent->getId(),
                    TblLessonContentLink::ATTR_TBL_LINK_ID => $LinkId
                )
            );
        if (null === $Entity) {
            $Entity = new TblLessonContentLink();
            $Entity->setTblLessonContent($tblLessonContent);
            $Entity->setLinkId($LinkId);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @return int
     */
    public function getNextLinkId(): int
    {
        $list = $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLessonContentLink');
        $max = 0;
        if ($list) {
            $max = 0;
            /** @var TblLessonContentLink $tblLessonContentLink */
            foreach ($list as $tblLessonContentLink) {
                if ($tblLessonContentLink->getLinkId() !== null
                    && $tblLessonContentLink->getLinkId() > $max
                ) {
                    $max = $tblLessonContentLink->getLinkId();
                }
            }
        }

        return $max + 1;
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return false | TblLessonContent[]
     */
    public function getLessonContentLinkAllByLessonContent(TblLessonContent $tblLessonContent)
    {
        $resultList = array();
        /** @var TblLessonContentLink $tblLessonContentLink */
        $tblLessonContentLink = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLessonContentLink',
            array(
                TblLessonContentLink::ATTR_TBL_LESSON_CONTENT => $tblLessonContent->getId()
            )
        );
        if ($tblLessonContentLink
            && ($LinkId = $tblLessonContentLink->getLinkId())
        ) {
            $tblLessonContentLinkList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblLessonContentLink', array(
                    TblLessonContentLink::ATTR_TBL_LINK_ID => $LinkId
                )
            );
            if ($tblLessonContentLinkList) {
                /** @var TblLessonContentLink $item */
                foreach ($tblLessonContentLinkList as $item) {
                    if ($item->getTblLessonContent()
                        && $item->getTblLessonContent()->getId() != $tblLessonContent->getId()
                    ) {
                        $resultList[] = $item->getTblLessonContent();
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblLessonContent[] $tblLessonContentList
     *
     * @return bool
     */
    public function destroyLessonContentLinkList(
        array $tblLessonContentList
    ): bool {
        $Manager = $this->getEntityManager();

        foreach ($tblLessonContentList as $tblLessonContent) {
            /** @var TblLessonContent $Entity */
            if (($tblLessonContentLinkList = $this->getForceEntityListBy(__METHOD__,
                $Manager, 'TblLessonContentLink', array(TblLessonContentLink::ATTR_TBL_LESSON_CONTENT => $tblLessonContent->getId())))
            ) {
                foreach ($tblLessonContentLinkList as $tblLessonContentLink) {
                    $Entity = $Manager->getEntityById('TblLessonContentLink', $tblLessonContentLink->getId());

                    if (null !== $Entity) {
                        $Manager->bulkKillEntity($Entity);
                        Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                    }
                }
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
}