<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\Service;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryPredecessorDivisionCourse;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryStudent;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
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
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear): array
    {
        $count = 0;
        $start = hrtime(true);

        $isCurrentYear = false;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYearTemp) {
                if ($tblYearTemp->getId() == $tblYear->getId()) {
                    $isCurrentYear = true;
                    break;
                }
            }
        }

        if (($tblDiaryOldList = Diary::useServiceOld()->getDiaryListByYear($tblYear))) {
            $Manager = $this->getEntityManager();
            $tblDivisionCourseGroupList = array();
            $tblDivisionList = array();
            $tblTypeCoreGroup = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_CORE_GROUP);
            foreach ($tblDiaryOldList as $tblDiaryOld) {
                $tblDivisionCourse = false;
                // Klasse
                if (($tblDivision = $tblDiaryOld->getServiceTblDivision())) {
                    $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($tblDivision->getId());
                    if (!isset($tblDivisionList[$tblDivision->getId()])) {
                        $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                    }
                // Stammgruppe
                } elseif (($tblGroup = $tblDiaryOld->getServiceTblGroup())) {
                    if (isset($tblDivisionCourseGroupList[$tblGroup->getId()])) {
                        $tblDivisionCourse = $tblDivisionCourseGroupList[$tblGroup->getId()];
                    } elseif ($isCurrentYear) {
                        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseByMigrateGroupId($tblGroup->getId());
                    } else {
                        // für ältere Schuljahre muss die Personen-Stammgruppe noch als DivisionCourse angelegt werden
                        if (($tblDivisionCourse = DivisionCourse::useService()->insertDivisionCourse(
                            $tblTypeCoreGroup, $tblYear, $tblGroup->getName(), $tblGroup->getDescription(), true, true, null
                        ))) {
                            $tblDivisionCourseGroupList[$tblGroup->getId()] = $tblDivisionCourse;
                        }
                    }
                }

                if ($tblDivisionCourse) {
                    $tblDiary = new TblDiary();
                    $tblDiary->setServiceTblDivisionCourse($tblDivisionCourse);
                    $tblDiary->setSubject($tblDiaryOld->getSubject());
                    $tblDiary->setContent($tblDiaryOld->getContent());
                    $tblDiary->setDate(($Date = $tblDiaryOld->getDate()) ? new DateTime($Date) : null);
                    $tblDiary->setLocation($tblDiaryOld->getLocation());
                    $tblDiary->setServiceTblPerson($tblDiaryOld->getServiceTblPerson() ?: null);

                    $count++;
                    // bei vorhandenen Schülern muss der Diary-Eintrag sofort gespeichert werden für die Id
                    if (($tblDiaryStudentOldList = Diary::useServiceOld()->getDiaryStudentAllByDiary($tblDiaryOld))) {
                        $Manager->saveEntity($tblDiary);

                        foreach ($tblDiaryStudentOldList as $tblDiaryStudentOld) {
                            if (($tblPersonStudent = $tblDiaryStudentOld->getServiceTblPerson())) {
                                $tblDiaryStudent = new TblDiaryStudent();
                                $tblDiaryStudent->setTblDiary($tblDiary);
                                $tblDiaryStudent->setServiceTblPerson($tblPersonStudent);

                                $Manager->bulkSaveEntity($tblDiaryStudent);
                                $count++;
                            }
                        }
                    } else {
                        $Manager->bulkSaveEntity($tblDiary);
                    }
                }
            }

            if (!empty($tblDivisionList)) {
                foreach ($tblDivisionList as $tblDivisionItem) {
                    if (($tblDiaryDivisionOldList = Diary::useServiceOld()->getDiaryDivisionByDivision($tblDivisionItem))
                        && ($tblDivisionCourseItem = DivisionCourse::useService()->getDivisionCourseById($tblDivisionItem->getId()))
                    ) {
                        foreach($tblDiaryDivisionOldList as $tblDiaryDivisionOld) {
                            if (($tblPredecessorDivision = $tblDiaryDivisionOld->getServiceTblPredecessorDivision())
                                && ($tblPredecessorDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($tblPredecessorDivision->getId()))
                            ) {
                                $tblDiaryPredecessorDivisionCourse = new TblDiaryPredecessorDivisionCourse();
                                $tblDiaryPredecessorDivisionCourse->setServiceTblDivisionCourse($tblDivisionCourseItem);
                                $tblDiaryPredecessorDivisionCourse->setServiceTblPredecessorDivisionCourse($tblPredecessorDivisionCourse);

                                $Manager->bulkSaveEntity($tblDiaryPredecessorDivisionCourse);
                                $count++;
                            }
                        }
                    }
                }
            }

            $Manager->flushCache();
        }

        $end = hrtime(true);

        return array($count, round(($end - $start) / 1000000000, 2));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Subject
     * @param $Content
     * @param $Date
     * @param $Location
     * @param ?TblPerson $tblPerson
     *
     * @return TblDiary
     */
    public function createDiary(
        TblDivisionCourse $tblDivisionCourse,
        $Subject,
        $Content,
        $Date,
        $Location,
        ?TblPerson $tblPerson
    ): TblDiary {
        $Manager = $this->getEntityManager();

        $Entity = new TblDiary();
        $Entity->setServiceTblDivisionCourse($tblDivisionCourse);
        $Entity->setSubject($Subject);
        $Entity->setContent($Content);
        $Entity->setDate($Date ? new DateTime($Date) : null);
        $Entity->setLocation($Location);
        $Entity->setServiceTblPerson($tblPerson);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblDiary $tblDiary
     * @param $Subject
     * @param $Content
     * @param $Date
     * @param $Location
     * @param ?TblPerson $tblPerson
     *
     * @return bool
     */
    public function updateDiary(
        TblDiary $tblDiary,
        $Subject,
        $Content,
        $Date,
        $Location,
        ?TblPerson $tblPerson
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDiary $Entity */
        $Entity = $Manager->getEntityById('TblDiary', $tblDiary->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setSubject($Subject);
            $Entity->setContent($Content);
            $Entity->setDate($Date ? new DateTime($Date) : null);
            $Entity->setLocation($Location);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return bool
     */
    public function destroyDiary(TblDiary $tblDiary): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDiary $Entity */
        $Entity = $Manager->getEntityById('TblDiary', $tblDiary->getId());
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
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDiary', $Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblDiary[]
     */
    public function getDiaryAllByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiary', array(
            TblDiary::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId()
        ));
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByDiary(TblDiary $tblDiary)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryStudent', array(
            TblDiaryStudent::ATTR_TBL_DIARY => $tblDiary->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByStudent(TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryStudent', array(
            TblDiaryStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblDiary $tblDiary
     * @param TblPerson $tblPerson
     *
     * @return TblDiaryStudent
     */
    public function addDiaryStudent(TblDiary $tblDiary, TblPerson $tblPerson): TblDiaryStudent
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblDiaryStudent')
            ->findOneBy(array(
                TblDiaryStudent::ATTR_TBL_DIARY => $tblDiary->getId(),
                TblDiaryStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblDiaryStudent();
            $Entity->setTblDiary($tblDiary);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDiaryStudent $tblDiaryStudent
     *
     * @return bool
     */
    public function removeDiaryStudent(TblDiaryStudent $tblDiaryStudent): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDiaryStudent $Entity */
        $Entity = $Manager->getEntityById('TblDiaryStudent', $tblDiaryStudent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblDiaryPredecessorDivisionCourse[]
     */
    public function getDiaryPredecessorDivisionCourseListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryPredecessorDivisionCourse', array(
            TblDiaryPredecessorDivisionCourse::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblPredecessorDivisionCourse
     *
     * @return TblDiaryPredecessorDivisionCourse
     */
    public function addDiaryDivision(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblPredecessorDivisionCourse): TblDiaryPredecessorDivisionCourse
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblDiaryPredecessorDivisionCourse')
            ->findOneBy(array(
                TblDiaryPredecessorDivisionCourse::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
                TblDiaryPredecessorDivisionCourse::ATTR_SERVICE_TBL_PREDECESSOR_DIVISION_COURSE => $tblPredecessorDivisionCourse->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblDiaryPredecessorDivisionCourse();
            $Entity->setServiceTblDivisionCourse($tblDivisionCourse);
            $Entity->setServiceTblPredecessorDivisionCourse($tblPredecessorDivisionCourse);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDiary $tblDiary
     * @param TblPerson $tblPerson
     *
     * @return false|TblDiaryStudent
     */
    public function existsDiaryStudent(TblDiary $tblDiary, TblPerson $tblPerson)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDiaryStudent', array(
            TblDiaryStudent::ATTR_TBL_DIARY => $tblDiary->getId(),
            TblDiaryStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }
}