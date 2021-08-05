<?php

namespace SPHERE\Application\Transfer\Untis\Import\Service;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportStudent;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportStudentCourse;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Manager;

/**
 * Class Data
 * @package SPHERE\Application\Transfer\Untis\Import\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblUntisImportLectureship
     */
    public function getUntisImportLectureshipById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUntisImportLectureship', $Id);
    }

    /**
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUntisImportLectureship');
    }

    /**
     * @param $Id
     *
     * @return false|TblUntisImportStudent
     */
    public function getUntisImportStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUntisImportStudent', $Id);
    }

    /**
     * @param TblUntisImportStudent $tblUntisImportStudent
     *
     * @return false|TblUntisImportStudentCourse[]
     */
    public function getUntisImportStudentCourseByUntisImportStudent(
        TblUntisImportStudent $tblUntisImportStudent
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUntisImportStudentCourse',
            array(
                TblUntisImportStudentCourse::ATTR_TBL_UNTIS_IMPORT_STUDENT => $tblUntisImportStudent->getId()
            ));
    }

    /**
     * @param TblUntisImportStudent $tblUntisImportStudent
     * @param                          $Number
     *
     * @return false|TblUntisImportStudentCourse
     */
    public function getUntisImportStudentCourseByUntisImportStudentAndNumber(
        TblUntisImportStudent $tblUntisImportStudent,
        $Number
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUntisImportStudentCourse',
            array(
                TblUntisImportStudentCourse::ATTR_TBL_UNTIS_IMPORT_STUDENT => $tblUntisImportStudent->getId(),
                TblUntisImportStudentCourse::ATTR_COURSE_NUMBER               => $Number,
            ));
    }

    /**
     * @return false|TblUntisImportStudent[]
     */
    public function getUntisImportStudentAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUntisImportStudent');
    }

    /**
     * @param TblAccount|null $tblAccount
     *
     * @return false|TblUntisImportStudent[]
     */
    public function getUntisImportStudentAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUntisImportStudent',
            array(
                TblUntisImportStudent::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            ));
    }

    /**
     * @param TblAccount|null $tblAccount
     *
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUntisImportLectureship',
            array(
                TblUntisImportLectureship::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            ));
    }

    /**
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function createUntisImportLectureship(
        $ImportList,
        TblYear $tblYear,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $Result) {

                $Entity = new TblUntisImportLectureship();
                $Entity->setServiceTblYear($tblYear);
                $Entity->setSchoolClass($Result['FileDivision']);
                $Entity->setTeacherAcronym($Result['FileTeacher']);
                $Entity->setSubjectName($Result['FileSubject']);
                $Entity->setSubjectGroupName($Result['FileSubjectGroup']);
                $Entity->setServiceTblDivision($Result['tblDivision']);
                $Entity->setServiceTblTeacher($Result['tblTeacher']);
                $Entity->setServiceTblSubject($Result['tblSubject']);
                $Entity->setSubjectGroup($Result['AppSubjectGroup']);
                $Entity->setServiceTblAccount($tblAccount);
                $Entity->setIsIgnore(false);

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
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function createUntisImportStudentBulk(
        $ImportList,
        TblYear $tblYear,
        TblAccount $tblAccount
    ) {
// Prepare if needed
//        // entfernen doppelter Fächer /fachgruppen für eine Person
//        $existSubjectSubjectGroupList = array();
        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($ImportList)) {
            foreach ($ImportList as $StudentData) {

                // SSW-1420 es dürfen nur Schüler der SEK II berücksichtigt werden, also Klassenstufe 11-13
                $isIgnoreStudent = false;
                //Kontrolle, in welcher Klasse die Person gerade sitzt
                $LevelString = '';
                if(is_object($StudentData['EntityPerson'])){
                    $tblPerson = $StudentData['EntityPerson'];
                    if(($tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear))){
                        if(($tblLevel = $tblDivision->getTblLevel())){
                            $LevelString = $tblLevel->getName();

                            if (intval($LevelString) < 11) {
                                $isIgnoreStudent = true;
                            }
                        }
                    }
                }

                if (!$isIgnoreStudent) {
                    $tblUntisImportStudent = $this->createUntisImportStudent($tblYear, $tblAccount, $StudentData,
                        $LevelString);
                    $SubjectCount = 0;
                    if (isset($StudentData['SubjectList'])) {
                        foreach ($StudentData['SubjectList'] as $SubjectData) {
                            // Prepare if needed
//                        // Fächer und Fachgruppen soll es nur einmal pro Person geben
//                        if(!in_array($StudentData['EntityPerson']->getId().'x'.$SubjectData['FileSubject'].'x'.
//                            $SubjectData['SubjectGroup'], $existSubjectSubjectGroupList)){
                            $SubjectCount++;
                            $this->createUntisImportStudentCourseBulk($Manager, $SubjectData, $SubjectCount,
                                $tblUntisImportStudent);
                            $existSubjectSubjectGroupList[] = $StudentData['EntityPerson']->getId() . 'x' .
                                $SubjectData['FileSubject'] . 'x' . $SubjectData['SubjectGroup'];
//                        }
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
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     * @param array      $Result
     * @param string     $LevelString
     *
     * @return TblUntisImportStudent
     */
    private function createUntisImportStudent(
        TblYear $tblYear,
        TblAccount $tblAccount,
        $Result = array(),
        $LevelString = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblUntisImportStudent();
        $Entity->setServiceTblYear($tblYear);
        $Entity->setServiceTblPerson((is_object($Result['EntityPerson']) ? $Result['EntityPerson'] : null));
        $Entity->setServiceTblDivision((is_object($Result['EntityDivision']) ? $Result['EntityDivision'] : null));
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setLevel($LevelString);
        $Entity->setIsIgnore(false);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param Manager               $Manager
     * @param array                 $Result
     * @param int                   $SubjectNumber
     * @param TblUntisImportStudent $tblUntisImportStudent
     */
    private function createUntisImportStudentCourseBulk(
        Manager $Manager,
        $Result = array(),
        $SubjectNumber = 1,
        TblUntisImportStudent $tblUntisImportStudent = null
    ) {

        $Entity = new TblUntisImportStudentCourse();
        $Entity->setSubjectName($Result['FileSubject']);
        $Entity->setSubjectGroup($Result['SubjectGroup']);
        $Entity->setCourseNumber($SubjectNumber);
        if(preg_match('!-[Ll]-!',$Result['SubjectGroup'])){
            $IntensiveCourse = true;
        } else {
            $IntensiveCourse = false;
        }
        $Entity->setIsIntensiveCourse($IntensiveCourse);
        $Entity->setIsIgnoreCourse(false);
        $Entity->setServiceTblSubject((is_object($Result['EntitySubject']) ? $Result['EntitySubject'] : null));
        $Entity->settblUntisImportStudent($tblUntisImportStudent);
        $Manager->bulkSaveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
    }

    /**
     * @param string                $SubjectGroup
     * @param string                $SubjectName
     * @param int                   $Number
     * @param bool                  $IsIntensiveCourse
     * @param bool                  $IsIgnoreCourse
     * @param TblUntisImportStudent $tblUntisImportStudent
     * @param TblSubject            $tblSubject
     */
    public function createUntisImportStudentCourse(
        $SubjectGroup = '',
        $SubjectName = '',
        $Number = 1,
        $IsIntensiveCourse = false,
        $IsIgnoreCourse = false,
        TblUntisImportStudent $tblUntisImportStudent = null,
        TblSubject $tblSubject = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblUntisImportStudentCourse();
        $Entity->setSubjectGroup($SubjectGroup);
        $Entity->setSubjectName($SubjectName);
        $Entity->setCourseNumber($Number);
        $Entity->setIsIntensiveCourse($IsIntensiveCourse);
        $Entity->setIsIgnoreCourse($IsIgnoreCourse);
        $Entity->settblUntisImportStudent($tblUntisImportStudent);
        $Entity->setServiceTblSubject($tblSubject);

        $Manager->SaveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
    }

    /**
     * @param TblUntisImportStudentCourse $tblUntisImportStudentCourse
     * @param TblSubject                     $tblSubject
     * @param string                         $SubjectGroup
     * @param bool                           $IsIntensive
     * @param bool                           $IgnoreCourse
     *
     * @return bool|TblUntisImportStudentCourse
     */
    public function updateUntisImportStudentCourse(
        TblUntisImportStudentCourse $tblUntisImportStudentCourse,
        $tblSubject = null,
        $SubjectGroup = '',
        $IsIntensive = false,
        $IgnoreCourse = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblUntisImportStudentCourse', $tblUntisImportStudentCourse->getId());
        /** @var TblUntisImportStudentCourse $Entity */
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
     * @param TblUntisImportLectureship|null $tblUntisImportLectureship
     * @param TblDivision|null               $tblDivision
     * @param TblTeacher|null                $tblTeacher
     * @param TblSubject|null                $tblSubject
     * @param string                         $SubjectGroup
     * @param bool                           $IsIgnore
     *
     * @return bool
     */
    public function updateUntisImportLectureship(
        TblUntisImportLectureship $tblUntisImportLectureship = null,
        TblDivision $tblDivision = null,
        TblTeacher $tblTeacher = null,
        TblSubject $tblSubject = null,
        $SubjectGroup = '',
        $IsIgnore = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblUntisImportLectureship', $tblUntisImportLectureship->getId());
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
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     * @param boolean                   $IsIgnore
     *
     * @return bool
     */
    public function updateUntisImportLectureshipIsIgnore(TblUntisImportLectureship $tblUntisImportLectureship, $IsIgnore = true)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportLectureship $Entity */
        $Entity = $Manager->getEntityById('TblUntisImportLectureship', $tblUntisImportLectureship->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsIgnore($IsIgnore);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }

        return false;
    }

    public function updateUntisImportStudentDivision(
        TblUntisImportStudent $tblUntisImportStudent,
        TblDivision $tblDivision
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportStudent $Entity */
        $Entity = $Manager->getEntityById('TblUntisImportStudent', $tblUntisImportStudent->getId());
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
     * @param TblUntisImportStudent $tblUntisImportStudent
     * @param bool                     $isIgnore
     *
     * @return mixed
     */
    public function updateUntisImportStudentIsIgnore(
        TblUntisImportStudent $tblUntisImportStudent,
        $isIgnore = true
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportStudent $Entity */
        $Entity = $Manager->getEntityById('TblUntisImportStudent', $tblUntisImportStudent->getId());
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
    public function destroyUntisImportLectureshipByAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblUntisImportLectureship')
            ->findBy(array(TblUntisImportLectureship::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()));
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
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     *
     * @return bool
     */
    public function destroyUntisImportLectureship(TblUntisImportLectureship $tblUntisImportLectureship)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUntisImportLectureship $Entity */
        $Entity = $Manager->getEntity('TblUntisImportLectureship')
            ->find($tblUntisImportLectureship->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity, true);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblUntisImportStudent $tblUntisImportStudent
     *
     * @return bool
     */
    public function destroyUntisImportStudentCourse(TblUntisImportStudent $tblUntisImportStudent)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblUntisImportStudentCourse')
            ->findBy(array(TblUntisImportStudentCourse::ATTR_TBL_UNTIS_IMPORT_STUDENT => $tblUntisImportStudent->getId()));
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
    public function destroyUntisImportStudentByAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblUntisImportStudent')
            ->findBy(array(TblUntisImportStudent::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()));
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

}