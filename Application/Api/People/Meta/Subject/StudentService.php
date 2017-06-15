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
                        $tblStudentSubject->setTblStudent($tblStudent);
                        $tblStudentSubject->setTblStudentSubjectType($tblStudentSubjectType);
                        $tblStudentSubject->setTblStudentSubjectRanking($tblStudentSubjectRanking);
                    }
                    $tblStudentSubject->setServiceTblSubject($tblSubject);

                    $BulkSave[] = $tblStudentSubject;
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave);
            }
            return false;
        }
        return true;
    }
}