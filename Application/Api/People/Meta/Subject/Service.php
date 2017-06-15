<?php

namespace SPHERE\Application\Api\People\Meta\Subject;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Service as ServiceAPP;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person as PersonAPP;
use SPHERE\Application\People\Person\Person;

class Service extends ServiceAPP
{

    /**
     * @param array                                      $PersonIdArray
     * @param TblSubject|null                            $tblSubject
     * @param ServiceAPP\Entity\TblStudentSubjectType    $tblStudentSubjectType
     * @param ServiceAPP\Entity\TblStudentSubjectRanking $tblStudentSubjectRanking
     * @param TblLevel|null                              $tblLevelFrom
     * @param TblLevel|null                              $tblLevelTill
     *
     * @return bool
     */
    public function replaceSubjectByPersonIdList(
        $PersonIdArray = array(),
        TblSubject $tblSubject = null,
        ServiceAPP\Entity\TblStudentSubjectType $tblStudentSubjectType,
        ServiceAPP\Entity\TblStudentSubjectRanking $tblStudentSubjectRanking,
        TblLevel $tblLevelFrom = null,
        TblLevel $tblLevelTill = null
    ) {

        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = PersonAPP::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if (!$tblStudent) {
                        $tblStudent = Student::useService()->createStudent($tblPerson);
                    }
                }
                if ($tblStudent) {
                    (new Data($this->getBinding()))->createStudentSubject(
                        $tblStudent,
                        $tblSubject,
                        $tblStudentSubjectType,
                        $tblStudentSubjectRanking,
                        $tblLevelFrom,
                        $tblLevelTill);
                }
            }
        }

        return true;
    }
}