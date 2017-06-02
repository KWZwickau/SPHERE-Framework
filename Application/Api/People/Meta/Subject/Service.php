<?php

namespace SPHERE\Application\Api\People\Meta\Subject;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Service as ServiceAPP;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person as PersonAPP;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class Service extends ServiceAPP
{

    /**
     * @param                                            $PersonId
     * @param TblSubject|null                            $tblSubject
     * @param ServiceAPP\Entity\TblStudentSubjectType    $tblStudentSubjectType
     * @param ServiceAPP\Entity\TblStudentSubjectRanking $tblStudentSubjectRanking
     * @param TblLevel|null                              $tblLevelFrom
     * @param TblLevel|null                              $tblLevelTill
     * @param array                                      $PersonIdArray
     *
     * @return bool
     */
    public function replaceSubject(
        $PersonId,
        TblSubject $tblSubject = null,
        ServiceAPP\Entity\TblStudentSubjectType $tblStudentSubjectType,
        ServiceAPP\Entity\TblStudentSubjectRanking $tblStudentSubjectRanking,
        TblLevel $tblLevelFrom = null,
        TblLevel $tblLevelTill = null,
        $PersonIdArray = array()
    ) {

        $tblPerson = PersonAPP::useService()->getPersonById($PersonId);
        $tblStudent = false;
        if ($tblPerson) {
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if (!$tblStudent) {
                $tblStudent = $this->createStudent($tblPerson);
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
        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = PersonAPP::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if (!$tblStudent) {
                        $tblStudent = $this->createStudent($tblPerson);
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

    /**
     * @param TblPerson                                      $tblPerson
     * @param string                                         $Identifier
     * @param ServiceAPP\Entity\TblStudentMedicalRecord|null $tblStudentMedicalRecord
     * @param ServiceAPP\Entity\TblStudentTransport|null     $tblStudentTransport
     * @param ServiceAPP\Entity\TblStudentBilling|null       $tblStudentBilling
     * @param ServiceAPP\Entity\TblStudentLocker|null        $tblStudentLocker
     * @param ServiceAPP\Entity\TblStudentBaptism|null       $tblStudentBaptism
     * @param ServiceAPP\Entity\TblStudentIntegration|null   $tblStudentIntegration
     * @param string                                         $SchoolAttendanceStartDate
     *
     * @return ServiceAPP\Entity\TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        $Identifier = '',
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentIntegration = null,
        $SchoolAttendanceStartDate = ''
    ) {

        return (new Data($this->getBinding()))->createStudent(
            $tblPerson,
            $Identifier,
            $tblStudentMedicalRecord,
            $tblStudentTransport,
            $tblStudentBilling,
            $tblStudentLocker,
            $tblStudentBaptism,
            $tblStudentIntegration,
            $SchoolAttendanceStartDate
        );
    }
}