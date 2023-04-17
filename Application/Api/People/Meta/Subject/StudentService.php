<?php

namespace SPHERE\Application\Api\People\Meta\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;

class StudentService
{

    /**
     * @param array                    $PersonIdArray
     * @param TblSubject|null          $tblSubject
     * @param TblStudentSubjectType    $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     *
     * @return bool
     */
    public function replaceSubjectByPersonIdList(
        $PersonIdArray = array(),
        TblSubject $tblSubject = null,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking
    ) {

        $BulkSave = array();
        $BulkProtocol = array();

        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = Person::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if (!$tblStudent) {
                        $tblStudent = Student::useService()->createStudent($tblPerson);
                    }
                }
                if ($tblStudent) {
                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                    if (!$tblStudentSubject) {
                        $tblStudentSubject = new TblStudentSubject();
                        $BulkProtocol[] = false;
                        $tblStudentSubject->setTblStudent($tblStudent);
                        $tblStudentSubject->setTblStudentSubjectType($tblStudentSubjectType);
                        $tblStudentSubject->setTblStudentSubjectRanking($tblStudentSubjectRanking);
                    } else {
                        $BulkProtocol[] = clone $tblStudentSubject;
                    }
                    $tblStudentSubject->setServiceTblSubject($tblSubject);

                    $BulkSave[] = $tblStudentSubject;
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave, $BulkProtocol);
            }
            return false;
        }
        return true;
    }

    /**
     * @param array $PersonIdArray
     * @param int|null $LevelFrom
     * @param TblStudentSubjectType $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     *
     * @return bool
     */
    public function replaceLevelFromByPersonIdList(
        $PersonIdArray = array(),
        ?int $LevelFrom = null,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking
    ): bool {

        $BulkSave = array();
        $BulkProtocol = array();

        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = Person::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if (!$tblStudent) {
                        $tblStudent = Student::useService()->createStudent($tblPerson);
                    }
                }
                if ($tblStudent) {
                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                    if ($tblStudentSubject && $tblStudentSubject->getServiceTblSubject()) {
                        $BulkProtocol[] = clone $tblStudentSubject;
                        $tblStudentSubject->setLevelFrom($LevelFrom);
                        $BulkSave[] = $tblStudentSubject;
                    }
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave, $BulkProtocol);
            }
            return false;
        }
        return true;
    }

    /**
     * @param array $PersonIdArray
     * @param int|null $LevelTill
     * @param TblStudentSubjectType $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     *
     * @return bool
     */
    public function replaceLevelTillByPersonIdList(
        $PersonIdArray = array(),
        ?int $LevelTill = null,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking
    ): bool {

        $BulkSave = array();
        $BulkProtocol = array();

        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = Person::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if (!$tblStudent) {
                        $tblStudent = Student::useService()->createStudent($tblPerson);
                    }
                }
                if ($tblStudent) {
                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                    if ($tblStudentSubject && $tblStudentSubject->getServiceTblSubject()) {
                        $BulkProtocol[] = clone $tblStudentSubject;
                        $tblStudentSubject->setLevelTill($LevelTill);
                        $BulkSave[] = $tblStudentSubject;
                    }
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave, $BulkProtocol);
            }
            return false;
        }
        return true;
    }
}