<?php

namespace SPHERE\Application\Api\People\Meta\Transfer;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service as ServiceAPP;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person as PersonAPP;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;

class Service extends ServiceAPP
{

    /**
     * @param integer    $PersonId
     * @param string     $StudentTransferTypeIdentifier
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     * @param TblCourse  $tblCourse
     * @param string     $transferDate
     * @param string     $Remark
     *
     * @return bool|ServiceAPP\Entity\TblStudentTransfer|AbstractField
     */
    public function createTransfer(
        $PersonId,
        $StudentTransferTypeIdentifier,
        $tblCompany = null,
        $tblType = null,
        $tblCourse = null,
        $transferDate = '',
        $Remark = ''
    ) {

        $tblPerson = PersonAPP::useService()->getPersonById($PersonId);
        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier($StudentTransferTypeIdentifier);
        if ($tblPerson && $tblStudentTransferType) {
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if (!$tblStudent) {
                $tblStudent = $this->createStudent($tblPerson);
            }
        }

        if (isset($tblStudent) && $tblStudent) {
            return (new Data($this->getBinding()))->createStudentTransfer(
                $tblStudent,
                $tblStudentTransferType,
                $tblCompany,
                $tblType,
                $tblCourse,
                $transferDate,
                $Remark);
        } else {
            return false;
        }
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