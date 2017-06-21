<?php

namespace SPHERE\Application\Transfer\Indiware\Import\Service;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportLectureship;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportStudentCourse;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Manager;

/**
 * Class Data
 * @package SPHERE\Application\Transfer\Indiware\Import\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblIndiwareImportLectureship
     */
    public function getIndiwareImportLectureshipById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportLectureship', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblIndiwareImportStudentCourse
     */
    public function getIndiwareImportStudentCourseById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudentCourse', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblIndiwareImportStudentCourse[]
     *
     */
    public function getIndiwareImportStudentCourseByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudentCourse',
            array(
                TblIndiwareImportStudentCourse::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @return false|TblIndiwareImportLectureship[]
     */
    public function getIndiwareImportLectureshipAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportLectureship');
    }

    /**
     * @return false|TblIndiwareImportStudentCourse[]
     */
    public function getIndiwareImportStudentCourseAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudentCourse');
    }

    /**
     * @param TblAccount|null $tblAccount
     *
     * @return false|TblIndiwareImportLectureship[]
     */
    public function getIndiwareImportLectureshipAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportLectureship',
            array(
                TblIndiwareImportLectureship::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            ));
    }

    /**
     * @param TblAccount|null $tblAccount
     *
     * @return false|TblIndiwareImportStudentCourse[]
     */
    public function getIndiwareImportStudentCourseAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudentCourse',
            array(
                TblIndiwareImportStudentCourse::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            ));
    }

    /**
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function createIndiwareImportLectureshipBulk(
        $ImportList,
        TblYear $tblYear,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {

            $DivisionList = $this->getDivisionClassCount(20);
            foreach ($ImportList as $Result) {

//                $this->createIndiwareImportLectureship($Manager, $tblYear, $tblAccount, $Result, 1, 1);
//
//                if (isset($Result['FileTeacher2']) && $Result['FileTeacher2'] != '') {
//                    $this->createIndiwareImportLectureship($Manager, $tblYear, $tblAccount, $Result, 2, 1);
//                }
//                if (isset($Result['FileTeacher3']) && $Result['FileTeacher3'] != '') {
//                    $this->createIndiwareImportLectureship($Manager, $tblYear, $tblAccount, $Result, 3, 1);
//                }
                foreach ($DivisionList as $Number) {
                    if (isset($Result['tblDivision'.$Number]) && $Result['tblDivision'.$Number]) {

                        if (isset($Result['FileTeacher1']) && $Result['FileTeacher1'] != '') {
                            $this->createIndiwareImportLectureship($Manager, $tblYear, $tblAccount, $Result, 1,
                                $Number);
                        }
                        if (isset($Result['FileTeacher2']) && $Result['FileTeacher2'] != '') {
                            $this->createIndiwareImportLectureship($Manager, $tblYear, $tblAccount, $Result, 2,
                                $Number);
                        }
                        if (isset($Result['FileTeacher3']) && $Result['FileTeacher3'] != '') {
                            $this->createIndiwareImportLectureship($Manager, $tblYear, $tblAccount, $Result, 3,
                                $Number);
                        }
                    }
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param int $Count *wie viele Klassenspalten sollen durchgegangen werden*
     *
     * @return array
     */
    private function getDivisionClassCount($Count = 20)
    {

        $result = array();
        for ($i = 1; $i <= $Count; $i++) {
            $result[] = $i;
        }

        return $result;
    }

    /**
     * @param Manager    $Manager
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     * @param array      $Result
     * @param int        $TeacherCount
     * @param int        $DivisionCount
     */
    private function createIndiwareImportLectureship(
        Manager $Manager,
        TblYear $tblYear,
        TblAccount $tblAccount,
        $Result = array(),
        $TeacherCount = 1,
        $DivisionCount = 1
    ) {

        $Entity = new TblIndiwareImportLectureship();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setSchoolClass($Result['FileDivision'.$DivisionCount]);
        $Entity->setTeacherAcronym($Result['FileTeacher'.$TeacherCount]);
        $Entity->setSubjectName($Result['FileSubject']);
        $Entity->setSubjectGroupName($Result['FileSubjectGroup']);
        $Entity->setServiceTblDivision($Result['tblDivision'.$DivisionCount]);
        $Entity->setServiceTblTeacher($Result['tblTeacher'.$TeacherCount]);
        $Entity->setServiceTblSubject($Result['tblSubject']);
        $Entity->setSubjectGroup($Result['AppSubjectGroup']);
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setIsIgnore(false);
        $Manager->bulkSaveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
    }

    /**
     * @param TblIndiwareImportLectureship|null $tblIndiwareImportLectureship
     * @param TblDivision|null                  $tblDivision
     * @param TblTeacher|null                   $tblTeacher
     * @param TblSubject|null                   $tblSubject
     * @param string                            $SubjectGroup
     * @param bool                              $IsIgnore
     *
     * @return bool
     */
    public function updateIndiwareImportLectureship(
        TblIndiwareImportLectureship $tblIndiwareImportLectureship = null,
        TblDivision $tblDivision = null,
        TblTeacher $tblTeacher = null,
        TblSubject $tblSubject = null,
        $SubjectGroup = '',
        $IsIgnore = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblIndiwareImportLectureship', $tblIndiwareImportLectureship->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblTeacher($tblTeacher);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setSubjectGroup($SubjectGroup);
            $Entity->setIsIgnore($IsIgnore);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }
        return false;
    }

    /**
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function createIndiwareImportStudentCourseBulk(
        $ImportList,
        TblYear $tblYear,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {

            $SubjectList = $this->getSubjectCount(17);
            foreach ($ImportList as $Result) {

                foreach ($SubjectList as $Number) {
                    if (isset($Result['FileSubject'.$Number]) && $Result['FileSubject'.$Number]) {

                        if (isset($Result['FileSubject'.$Number]) && $Result['FileSubject'.$Number] != '') {
                            $this->createIndiwareImportStudentCourse($Manager, $tblYear, $tblAccount, $Result, $Number);
                        }
                    }
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param int $Count *wie viele Fächer/Gruppen sollen durchgegangen werden*
     *
     * @return array
     */
    private function getSubjectCount($Count = 17)
    {

        $result = array();
        for ($i = 1; $i <= $Count; $i++) {
            $result[] = $i;
        }

        return $result;
    }

    /**
     * @param Manager    $Manager
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     * @param array      $Result
     * @param int        $SubjectNumber
     */
    private function createIndiwareImportStudentCourse(
        Manager $Manager,
        TblYear $tblYear,
        TblAccount $tblAccount,
        $Result = array(),
        $SubjectNumber = 1
    ) {

        $Entity = new TblIndiwareImportStudentCourse();
        $Entity->setServiceTblYear($tblYear);
//        $Entity->setFirstName($Result['FirstName']);
//        $Entity->setLastName($Result['LastName']);
//        $Entity->setBirthday($Result['Birthday']);
        $Entity->setSubjectName($Result['FileSubject'.$SubjectNumber]);
        $Entity->setSubjectGroup($Result['AppSubjectGroup'.$SubjectNumber]);
        $Entity->setCourseNumber($SubjectNumber);
        $Entity->setIsIntensiveCourse($Result['IsIntensiveCourse'.$SubjectNumber]);
        $Entity->setServiceTblPerson(($Result['tblPerson'] ? $Result['tblPerson'] : null));
        $Entity->setServiceTblDivision(($Result['tblDivision'] ? $Result['tblDivision'] : null));
        $Entity->setServiceTblSubject(($Result['tblSubject'.$SubjectNumber] ? $Result['tblSubject'.$SubjectNumber] : null));
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setIsIgnore(false);
        $Manager->bulkSaveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
    }

    /**
     * @param TblIndiwareImportLectureship $tblIndiwareImportLectureship
     * @param boolean                      $IsIgnore
     *
     * @return bool
     */
    public function updateIndiwareImportLectureshipIsIgnore(
        TblIndiwareImportLectureship $tblIndiwareImportLectureship,
        $IsIgnore = true
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblIndiwareImportLectureship', $tblIndiwareImportLectureship->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsIgnore($IsIgnore);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyIndiwareImportLectureshipByAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblIndiwareImportLectureship')
            ->findBy(array(TblIndiwareImportLectureship::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()));
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyIndiwareImportStudentCourseByAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblIndiwareImportStudentCourse')
            ->findBy(array(TblIndiwareImportStudentCourse::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()));
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblIndiwareImportLectureship $tblIndiwareImportLectureship
     *
     * @return bool
     */
    public function destroyIndiwareImportLectureship(TblIndiwareImportLectureship $tblIndiwareImportLectureship)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportLectureship $Entity */
        $Entity = $Manager->getEntity('TblIndiwareImportLectureship')
            ->find($tblIndiwareImportLectureship->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity, true);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblIndiwareImportStudentCourse $tblIndiwareImportStudentCourse
     *
     * @return bool
     */
    public function destroyIndiwareImportStudentCourse(TblIndiwareImportStudentCourse $tblIndiwareImportStudentCourse)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportStudentCourse $Entity */
        $Entity = $Manager->getEntity('TblIndiwareImportStudentCourse')
            ->find($tblIndiwareImportStudentCourse->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity, true);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

}