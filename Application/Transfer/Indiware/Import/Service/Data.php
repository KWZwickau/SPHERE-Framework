<?php

namespace SPHERE\Application\Transfer\Indiware\Import\Service;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportLectureship;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportStudent;
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
     * @return false|TblIndiwareImportStudent
     */
    public function getIndiwareImportStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudent', $Id);
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     *
     * @return false|TblIndiwareImportStudentCourse[]
     */
    public function getIndiwareImportStudentCourseByIndiwareImportStudent(
        TblIndiwareImportStudent $tblIndiwareImportStudent
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudentCourse',
            array(
                TblIndiwareImportStudentCourse::ATTR_TBL_INDIWARE_IMPORT_STUDENT => $tblIndiwareImportStudent->getId()
            ));
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param                          $Number
     *
     * @return false|TblIndiwareImportStudentCourse
     */
    public function getIndiwareImportStudentCourseByIndiwareImportStudentAndNumber(
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        $Number
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudentCourse',
            array(
                TblIndiwareImportStudentCourse::ATTR_TBL_INDIWARE_IMPORT_STUDENT => $tblIndiwareImportStudent->getId(),
                TblIndiwareImportStudentCourse::ATTR_COURSE_NUMBER               => $Number,
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
     * @return false|TblIndiwareImportStudent[]
     */
    public function getIndiwareImportStudentAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudent');
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
     * @return false|TblIndiwareImportStudent[]
     */
    public function getIndiwareImportStudentAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblIndiwareImportStudent',
            array(
                TblIndiwareImportStudent::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
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
     * @param string     $LevelString
     *
     * @return bool
     */
    public function createIndiwareImportStudentBulk(
        $ImportList,
        TblYear $tblYear,
        TblAccount $tblAccount,
        $LevelString = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {

            $SubjectList = $this->getSubjectCount(17);
            foreach ($ImportList as $Result) {
                $tblIndiwareImportStudent = $this->createIndiwareImportStudent($tblYear, $tblAccount, $Result,
                    $LevelString);
                foreach ($SubjectList as $Number) {
                    if (isset($Result['FileSubject'.$Number]) && $Result['FileSubject'.$Number]) {

                        if (isset($Result['FileSubject'.$Number]) && $Result['FileSubject'.$Number] != '') {
                            $this->createIndiwareImportStudentCourseBulk($Manager, $Result, $Number,
                                $tblIndiwareImportStudent);
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
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     * @param array      $Result
     * @param string     $LevelString
     *
     * @return TblIndiwareImportStudent
     */
    private function createIndiwareImportStudent(
        TblYear $tblYear,
        TblAccount $tblAccount,
        $Result = array(),
        $LevelString = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblIndiwareImportStudent();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setServiceTblPerson(($Result['tblPerson'] ? $Result['tblPerson'] : null));
        $Entity->setServiceTblDivision(($Result['tblDivision'] ? $Result['tblDivision'] : null));
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setLevel($LevelString);
        $Entity->setIsIgnore(false);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param TblDivision|null         $tblDivision
     *
     * @return bool
     */
    public function updateIndiwareImportStudent(
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        TblDivision $tblDivision = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportStudent $Entity */
        $Entity = $Manager->getEntityById('TblIndiwareImportStudent', $tblIndiwareImportStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblDivision($tblDivision);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }
        return false;
    }

    /**
     * @param Manager                  $Manager
     * @param array                    $Result
     * @param int                      $SubjectNumber
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     */
    private function createIndiwareImportStudentCourseBulk(
        Manager $Manager,
        $Result = array(),
        $SubjectNumber = 1,
        TblIndiwareImportStudent $tblIndiwareImportStudent
    ) {

        $Entity = new TblIndiwareImportStudentCourse();
        $Entity->setSubjectName($Result['FileSubject'.$SubjectNumber]);
        $Entity->setSubjectGroup($Result['AppSubjectGroup'.$SubjectNumber]);
        $Entity->setCourseNumber($SubjectNumber);
        $Entity->setIsIntensiveCourse($Result['IsIntensiveCourse'.$SubjectNumber]);
        $Entity->setIsIgnoreCourse(false);
        $Entity->setServiceTblSubject(($Result['tblSubject'.$SubjectNumber] ? $Result['tblSubject'.$SubjectNumber] : null));
        $Entity->settblIndiwareImportStudent($tblIndiwareImportStudent);
        $Manager->bulkSaveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
    }

    /**
     * @param string                   $SubjectGroup
     * @param string                   $SubjectName
     * @param int                      $Number
     * @param bool                     $IsIntensiveCourse
     * @param bool                     $IsIgnoreCourse
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param TblSubject               $tblSubject
     */
    public function createIndiwareImportStudentCourse(
        $SubjectGroup = '',
        $SubjectName = '',
        $Number,
        $IsIntensiveCourse = false,
        $IsIgnoreCourse = false,
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        TblSubject $tblSubject = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblIndiwareImportStudentCourse();
        $Entity->setSubjectGroup($SubjectGroup);
        $Entity->setSubjectName($SubjectName);
        $Entity->setCourseNumber($Number);
        $Entity->setIsIntensiveCourse($IsIntensiveCourse);
        $Entity->setIsIgnoreCourse($IsIgnoreCourse);
        $Entity->settblIndiwareImportStudent($tblIndiwareImportStudent);
        $Entity->setServiceTblSubject($tblSubject);

        $Manager->SaveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
    }

    /**
     * @param TblIndiwareImportStudentCourse $tblIndiwareImportStudentCourse
     * @param TblSubject                     $tblSubject
     * @param string                         $SubjectGroup
     * @param bool                           $IsIntensive
     * @param bool                           $IgnoreCourse
     *
     * @return bool|TblIndiwareImportStudentCourse
     */
    public function updateIndiwareImportStudentCourse(
        TblIndiwareImportStudentCourse $tblIndiwareImportStudentCourse,
        $tblSubject = null,
        $SubjectGroup = '',
        $IsIntensive = false,
        $IgnoreCourse = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblIndiwareImportStudentCourse', $tblIndiwareImportStudentCourse->getId());
        /** @var TblIndiwareImportStudentCourse $Entity */
        if ($Entity !== null) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setSubjectGroup($SubjectGroup);
            $Entity->setIsIntensiveCourse($IsIntensive);
            $Entity->setIsIgnoreCourse($IgnoreCourse);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblIndiwareImportLectureship $tblIndiwareImportLectureship
     * @param boolean                      $isIgnore
     *
     * @return bool
     */
    public function updateIndiwareImportLectureshipIsIgnore(
        TblIndiwareImportLectureship $tblIndiwareImportLectureship,
        $isIgnore = true
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblIndiwareImportLectureship', $tblIndiwareImportLectureship->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsIgnore($isIgnore);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }

        return false;
    }

    public function updateIndiwareImportStudentDivision(
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        TblDivision $tblDivision
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportStudent $Entity */
        $Entity = $Manager->getEntityById('TblIndiwareImportStudent', $tblIndiwareImportStudent->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblDivision($tblDivision);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param bool                     $isIgnore
     *
     * @return mixed
     */
    public function updateIndiwareImportStudentIsIgnore(
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        $isIgnore = true
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportStudent $Entity */
        $Entity = $Manager->getEntityById('TblIndiwareImportStudent', $tblIndiwareImportStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsIgnore($isIgnore);
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
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     *
     * @return bool
     */
    public function destroyIndiwareImportStudentCourse(TblIndiwareImportStudent $tblIndiwareImportStudent)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblIndiwareImportStudentCourse')
            ->findBy(array(TblIndiwareImportStudentCourse::ATTR_TBL_INDIWARE_IMPORT_STUDENT => $tblIndiwareImportStudent->getId()));
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
    public function destroyIndiwareImportStudentByAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblIndiwareImportStudent')
            ->findBy(array(TblIndiwareImportStudent::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()));
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
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     *
     * @return bool
     */
    public function destroyIndiwareImportStudent(TblIndiwareImportStudent $tblIndiwareImportStudent)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblIndiwareImportStudent $Entity */
        $Entity = $Manager->getEntity('TblIndiwareImportStudent')
            ->find($tblIndiwareImportStudent->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity, true);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

}