<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblForgotten;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblForgottenStudent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblFullTimeContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContentLink;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonWeek;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
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
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear): array
    {
        $count = 0;
        $start = hrtime(true);

        // Kursbücher migrieren
//        if (($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))) {
//            $Manager = $this->getEntityManager();
//            foreach ($tblDivisionList as $tblDivision) {
//                if (($tblCourseContentList = $this->getCachedEntityListBy(__METHOD__, $Manager, 'TblCourseContent', array(
//                    TblCourseContent::ATTR_SERVICE_TBL_DIVISION_COURSE => null,
//                    TblCourseContent::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
//                )))) {
//                    /** @var TblCourseContent $tblCourseContent */
//                    foreach($tblCourseContentList as $tblCourseContent) {
//                        if (($tblSubject = $tblCourseContent->getServiceTblSubject())
//                            && ($tblSubjectGroup = $tblCourseContent->getServiceTblSubjectGroup())
//                            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseByMigrateSekCourse(
//                                Division::useService()->getMigrateSekCourseString($tblDivision, $tblSubject, $tblSubjectGroup
//                            )))
//                        ) {
//                            $count++;
//                            $tblCourseContent->setServiceTblDivisionCourse($tblDivisionCourse);
//                            $Manager->bulkSaveEntity($tblCourseContent);
//                        }
//                    }
//                }
//            }
//
//            $Manager->flushCache();
//        }


        $end = hrtime(true);

        return array($count, round(($end - $start) / 1000000000, 2));
    }

    /**
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $DueDateHomework
     * @param $Room
     * @param TblDivisionCourse $tblDivisionCourse
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
        $DueDateHomework,
        $Room,
        TblDivisionCourse $tblDivisionCourse,
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
        $Entity->setDueDateHomework($DueDateHomework ? new DateTime($DueDateHomework) : null);
        $Entity->setRoom($Room);
        $Entity->setServiceTblDivisionCourse($tblDivisionCourse);
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
     * @param $DueDateHomework
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
        $DueDateHomework,
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
            $Entity->setDueDateHomework($DueDateHomework ? new DateTime($DueDateHomework) : null);
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDate(DateTime $date, TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent', array(
            TblLessonContent::ATTR_DATE => $date,
            TblLessonContent::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
        ), array(TblLessonContent::ATTR_LESSON => self::ORDER_ASC) );
    }

    /**
     * @param DateTime $date
     * @param int|null $lesson
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDateAndLesson(DateTime $date, ?int $lesson, TblDivisionCourse $tblDivisionCourse)
    {
        if ($lesson !== null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent', array(
                TblLessonContent::ATTR_DATE => $date,
                TblLessonContent::ATTR_LESSON => $lesson,
                TblLessonContent::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId()
            ), array(Element::ENTITY_CREATE => self::ORDER_ASC));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent', array(
                TblLessonContent::ATTR_DATE => $date,
                TblLessonContent::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId()
            ), array(Element::ENTITY_CREATE => self::ORDER_ASC));
        }
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByBetween(DateTime $fromDate, DateTime $toDate, TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

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
            ->setParameter(3, $tblDivisionCourse->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param DateTime $toDate
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblLessonContent[]|false
     */
    public function getLessonContentCanceledAllByToDate(DateTime $toDate, TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

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
            ->setParameter(2, $tblDivisionCourse->getId())
            ->setParameter(3, 1)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblSubject[]|false
     */
    public function getSubjectListFromLessonContent(TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t.serviceTblSubject as SubjectId, t.serviceTblSubstituteSubject as SubstituteSubjectId')
            ->from(__NAMESPACE__ . '\Entity\TblLessonContent', 't')
            ->where(
                $queryBuilder->expr()->eq('t.serviceTblDivision', '?1')
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->distinct()
            ->getQuery();

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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Date
     * @param $Lesson
     * @param $Content
     * @param $Homework
     * @param $DueDateHomework
     * @param $Remark
     * @param $Room
     * @param $countLessons
     * @param TblPerson|null $tblPerson
     *
     * @return TblCourseContent
     */
    public function createCourseContent(
        TblDivisionCourse $tblDivisionCourse,
        $Date,
        $Lesson,
        $Content,
        $Homework,
        $DueDateHomework,
        $Remark,
        $Room,
        $countLessons,
        TblPerson $tblPerson = null
    ): TblCourseContent {
        $Manager = $this->getEntityManager();

        $Entity = new TblCourseContent();
        $Entity->setServiceTblDivisionCourse($tblDivisionCourse);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setLesson($Lesson);
        $Entity->setContent($Content);
        $Entity->setHomework($Homework);
        $Entity->setDueDateHomework($DueDateHomework ? new DateTime($DueDateHomework) : null);
        $Entity->setRemark($Remark);
        $Entity->setRoom($Room);
        $Entity->setCountLessons($countLessons);

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
     * @param $DueDateHomework
     * @param $Remark
     * @param $Room
     * @param $countLessons
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
        $DueDateHomework,
        $Remark,
        $Room,
        $countLessons,
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
            $Entity->setDueDateHomework($DueDateHomework ? new DateTime($DueDateHomework) : null);
            $Entity->setRemark($Remark);
            $Entity->setRoom($Room);
            $Entity->setCountLessons($countLessons);
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblCourseContent[]
     */
    public function getCourseContentListBy(TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblCourseContent', array(
            TblCourseContent::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
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
        TblDivisionCourse $tblDivisionCourse,
        $Date,
        $Remark,
        $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher,
        $DateHeadmaster,
        ?TblPerson $serviceTblPersonHeadmaster
    ): TblLessonWeek {

        $Manager = $this->getEntityManager();

        $Entity = new TblLessonWeek();
        $Entity->setServiceTblDivisionCourse($tblDivisionCourse);
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $dateTime
     *
     * @return false|TblLessonWeek
     */
    public function getLessonWeekAllByDate(TblDivisionCourse $tblDivisionCourse, DateTime $dateTime)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLessonWeek', array(
            TblLessonWeek::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
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
            $tblLessonContentLinkList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLessonContentLink',
                array(
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

    /**
     * @param $Id
     *
     * @return false|TblFullTimeContent
     */
    public function getFullTimeContentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblFullTimeContent', $Id);
    }

    /**
     * @param $FromDate
     * @param $ToDate
     * @param $Content
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson|null $tblPerson
     *
     * @return TblFullTimeContent
     */
    public function createFullTimeContent(
        $FromDate,
        $ToDate,
        $Content,
        TblDivisionCourse $tblDivisionCourse,
        TblPerson $tblPerson = null
    ): TblFullTimeContent {

        $Manager = $this->getEntityManager();

        $Entity = new TblFullTimeContent();
        $Entity->setFromDate(new DateTime($FromDate));
        $Entity->setToDate($ToDate ? new DateTime($ToDate) : null);
        $Entity->setContent($Content);
        $Entity->setServiceTblDivisionCourse($tblDivisionCourse);
        $Entity->setServiceTblPerson($tblPerson);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblFullTimeContent $tblFullTimeContent
     * @param $FromDate
     * @param $ToDate
     * @param $Content
     * @param TblPerson|null $tblPerson
     *
     * @return bool
     */
    public function updateFullTimeContent(
        TblFullTimeContent $tblFullTimeContent,
        $FromDate,
        $ToDate,
        $Content,
        TblPerson $tblPerson = null
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblFullTimeContent $Entity */
        $Entity = $Manager->getEntityById('TblFullTimeContent', $tblFullTimeContent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setFromDate(new DateTime($FromDate));
            $Entity->setToDate($ToDate ? new DateTime($ToDate) : null);
            $Entity->setContent($Content);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblFullTimeContent $tblFullTimeContent
     *
     * @return bool
     */
    public function destroyFullTimeContent(TblFullTimeContent $tblFullTimeContent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblFullTimeContent $Entity */
        $Entity = $Manager->getEntityById('TblFullTimeContent', $tblFullTimeContent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $date
     *
     * @return TblFullTimeContent[]|false
     */
    public function getFullTimeContentListByDivisionCourseAndDate(TblDivisionCourse $tblDivisionCourse, DateTime $date)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblFullTimeContent', 't')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('t.serviceTblDivisionCourse', '?1'),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNull('t.ToDate'),
                        $queryBuilder->expr()->eq('t.FromDate', '?2')
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNotNull('t.ToDate'),
                        $queryBuilder->expr()->lte('t.FromDate', '?2'),
                        $queryBuilder->expr()->gte('t.ToDate', '?2')
                    ),
                )
            ))
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $date)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function getIsSubjectUsedInDigital(TblSubject $tblSubject): bool
    {
        if ($this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLessonContent',
            array(TblLessonContent::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param DateTime|null $dueDate
     * @param int|null $limit
     *
     * @return TblLessonContent[]|false
     */
    public function getDueDateHomeworkListBySubject(
        TblDivisionCourse $tblDivisionCourse,
        TblSubject $tblSubject,
        ?DateTime $dueDate = null,
        ?int $limit = null
    ): bool|array {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder
            ->select('t')
            ->from(TblLessonContent::class, 't')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->isNull('t.EntityRemove'),
                $queryBuilder->expr()->eq('t.serviceTblDivision', '?1'),
                $queryBuilder->expr()->orX(
                    // nur Vertretungsfach leer
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('t.serviceTblSubject', '?2'),
                        $queryBuilder->expr()->isNull('t.serviceTblSubstituteSubject'),
                    ),
                    $queryBuilder->expr()->eq('t.serviceTblSubstituteSubject', '?2'),
                )
            ))
            // Hausaufgaben nicht leer
            ->andWhere('LENGTH(t.Homework) > 0');

        if ($dueDate) {
            $query->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->lte('t.DueDateHomework', '?3'),
                    // Hausaufgaben auch anzeigen, wenn kein Fälligkeit eingetragen ist
                    $queryBuilder->expr()->isNull('t.DueDateHomework'),
                )
            );
        }

        $query
            ->orderBy('CASE WHEN t.DueDateHomework IS NULL THEN t.Date ELSE t.DueDateHomework END', 'DESC')
//            ->addOrderBy('t.Date', 'DESC')
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $tblSubject->getId());

        if ($dueDate) {
            $query->setParameter(3, $dueDate);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $resultList = $query
            ->getQuery()
            ->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param DateTime $dueDate
     *
     * @return TblLessonContent[]|false
     */
    public function getDueDateHomeworkListBySubjectAndExactDueDate(
        TblDivisionCourse $tblDivisionCourse,
        TblSubject $tblSubject,
        DateTime $dueDate,
    ): bool|array {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder
            ->select('t')
            ->from(TblLessonContent::class, 't')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->isNull('t.EntityRemove'),
                $queryBuilder->expr()->eq('t.serviceTblDivision', '?1'),
                $queryBuilder->expr()->orX(
                // nur Vertretungsfach leer
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('t.serviceTblSubject', '?2'),
                        $queryBuilder->expr()->isNull('t.serviceTblSubstituteSubject'),
                    ),
                    $queryBuilder->expr()->eq('t.serviceTblSubstituteSubject', '?2'),
                ),
                $queryBuilder->expr()->eq('t.DueDateHomework', '?3'),
            ))
            // Hausaufgaben nicht leer
            ->andWhere('LENGTH(t.Homework) > 0')
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $tblSubject->getId())
            ->setParameter(3, $dueDate);

        $resultList = $query
            ->getQuery()
            ->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime|null $dueDate
     * @param int|null $limit
     *
     * @return TblCourseContent[]|false
     */
    public function getDueDateHomeworkListByCourseSystem(
        TblDivisionCourse $tblDivisionCourse,
        ?DateTime $dueDate = null,
        ?int $limit = null
    ): bool|array {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder
            ->select('t')
            ->from(TblCourseContent::class, 't')
                ->where($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->isNull('t.EntityRemove'),
                    $queryBuilder->expr()->eq('t.serviceTblDivisionCourse', '?1'),
                    $queryBuilder->expr()->orX(
                ))
            )
            // Hausaufgaben nicht leer
            ->andWhere('LENGTH(t.Homework) > 0');

        if ($dueDate) {
            $query->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->lte('t.DueDateHomework', '?3'),
                    // Hausaufgaben auch anzeigen, wenn kein Fälligkeit eingetragen ist
                    $queryBuilder->expr()->isNull('t.DueDateHomework'),
                )
            );
        }

        $query
            ->orderBy('CASE WHEN t.DueDateHomework IS NULL THEN t.Date ELSE t.DueDateHomework END', 'DESC')
//            ->addOrderBy('t.Date', 'DESC')
            ->setParameter(1, $tblDivisionCourse->getId());

        if ($dueDate) {
            $query->setParameter(3, $dueDate);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $resultList = $query
            ->getQuery()
            ->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param $Id
     *
     * @return false|TblForgotten
     */
    public function getForgottenById($Id): bool|TblForgotten
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblForgotten', $Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Date
     * @param TblSubject $tblSubject
     * @param string $Remark
     * @param TblLessonContent|null $tblLessonContent
     * @param TblCourseContent|null $tblCourseContent
     * 
     * @return TblForgotten
     */
    public function createForgotten(
        TblDivisionCourse $tblDivisionCourse,
        $Date,
        TblSubject $tblSubject,
        string $Remark,
        ?TblLessonContent $tblLessonContent,
        ?TblCourseContent $tblCourseContent
    ): TblForgotten {

        $Manager = $this->getEntityManager();

        $Entity = new TblForgotten();
        $Entity->setServiceTblDivisionCourse($tblDivisionCourse);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setRemark($Remark);
        $Entity->setTblLessonContent($tblLessonContent);
        $Entity->setTblCourseContent($tblCourseContent);
        $Entity->setIsHomework($tblLessonContent || $tblCourseContent);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblForgotten $tblForgotten
     * @param $Date
     * @param TblSubject $tblSubject
     * @param string $Remark
     * @param TblLessonContent|null $tblLessonContent
     * @param TblCourseContent|null $tblCourseContent
     * 
     * @return bool
     */
    public function updateForgotten(
        TblForgotten $tblForgotten,
        $Date,
        TblSubject $tblSubject,
        string $Remark,
        ?TblLessonContent $tblLessonContent,
        ?TblCourseContent $tblCourseContent
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblForgotten $Entity */
        $Entity = $Manager->getEntityById('TblForgotten', $tblForgotten->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setRemark($Remark);
            $Entity->setTblLessonContent($tblLessonContent);
            $Entity->setTblCourseContent($tblCourseContent);
            $Entity->setIsHomework($tblLessonContent || $tblCourseContent);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblForgotten $tblForgotten
     *
     * @return bool
     */
    public function destroyForgotten(TblForgotten $tblForgotten): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblForgotten $Entity */
        $Entity = $Manager->getEntityById('TblForgotten', $tblForgotten->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblForgotten $tblForgotten
     * @param TblPerson $tblPerson
     *
     * @return TblForgottenStudent
     */
    public function addForgottenStudent(TblForgotten $tblForgotten, TblPerson $tblPerson): TblForgottenStudent
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblForgottenStudent')
            ->findOneBy(array(
                TblForgottenStudent::ATTR_TBL_FORGOTTEN => $tblForgotten->getId(),
                TblForgottenStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblForgottenStudent();
            $Entity->setTblForgotten($tblForgotten);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblForgottenStudent $tblForgottenStudent
     *
     * @return bool
     */
    public function removeForgottenStudent(TblForgottenStudent $tblForgottenStudent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblForgottenStudent $Entity */
        $Entity = $Manager->getEntityById('TblForgottenStudent', $tblForgottenStudent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblForgotten $tblForgotten
     *
     * @return false|TblForgottenStudent[]
     */
    public function getStudentsByForgotten(TblForgotten $tblForgotten): bool|array
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblForgottenStudent', array(
            TblForgottenStudent::ATTR_TBL_FORGOTTEN => $tblForgotten->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     * @param TblPerson|null $tblPerson
     *
     * @return false|TblForgotten[]
     */
    public function getForgottenListBy(TblDivisionCourse $tblDivisionCourse, ?TblSubject $tblSubject = null, ?TblPerson $tblPerson = null): array|bool
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder
            ->select('t')
            ->from(TblForgotten::class, 't')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->isNull('t.EntityRemove'),
                $queryBuilder->expr()->eq('t.serviceTblDivisionCourse', '?1')
            ))
            ->orderBy('t.Date', 'DESC')
            ->setParameter(1, $tblDivisionCourse->getId());

        if ($tblSubject) {
            $query
                ->andWhere($queryBuilder->expr()->eq('t.serviceTblSubject', '?2'))
                ->setParameter(2, $tblSubject->getId());
        }

        if ($tblPerson) {
            $query
                ->join(TblForgottenStudent::class, 'fs')
                ->andWhere($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.Id', 'fs.tblClassRegisterForgotten'),
                    $queryBuilder->expr()->eq('fs.serviceTblPerson', '?3')
                ))
                ->setParameter(3, $tblPerson->getId());
        }

        $resultList = $query
            ->getQuery()
            ->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool|null $isHomework
     * @param TblSubject|null $tblSubject
     *
     * @return int
     */
    public function getForgottenSumByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear, ?bool $isHomework, ?TblSubject $tblSubject = null): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder
            ->select('f')
            ->from(TblForgotten::class, 'f')
            ->join(TblDivisionCourse::class, 'c')
            ->join(TblForgottenStudent::class, 'fs')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->isNull('f.EntityRemove'),
                $queryBuilder->expr()->eq('f.serviceTblDivisionCourse', 'c.Id'),
                $queryBuilder->expr()->eq('f.Id', 'fs.tblClassRegisterForgotten'),
                $queryBuilder->expr()->eq('fs.serviceTblPerson', '?1'),
                $queryBuilder->expr()->eq('c.serviceTblYear', '?2'),
            ))
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblYear->getId());

        if ($isHomework !== null) {
            $query
                ->andWhere($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('f.IsHomework', '?3')
                ))
                ->setParameter(3, $isHomework);
        }

        if ($tblSubject !== null) {
            $query
                ->andWhere($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('f.serviceTblSubject', '?4')
                ))
                ->setParameter(4, $tblSubject->getId());
        }

        $resultList = $query
            ->getQuery()
            ->getResult();

        return count($resultList);
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param DateTime $date
     *
     * @return bool|TblForgotten
     */
    public function getForgottenByHomeworkAndDate(
        TblLessonContent $tblLessonContent,
        DateTime $date,
    ): bool|TblForgotten {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder
            ->select('t')
            ->from(TblForgotten::class, 't')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->isNull('t.EntityRemove'),
                $queryBuilder->expr()->eq('t.tblClassRegisterLessonContent', '?1'),
                $queryBuilder->expr()->eq('t.Date', '?2')
            ))
            // Hausaufgaben nicht leer
            ->setParameter(1, $tblLessonContent->getId())
            ->setParameter(2, $date);

        $resultList = $query
            ->getQuery()
            ->getResult();

        return empty($resultList) ? false : current($resultList);
    }
}