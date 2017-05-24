<?php

namespace SPHERE\Application\Api\People\Meta;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service as ServiceAPP;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person as PersonAPP;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;

class Service extends ServiceAPP
{

    /**
     * @param IFormInterface|null $Form
     * @param null|array          $Meta
     * @param integer             $PersonId
     * @param string              $StudentTransferTypeIdentifier
     * @param TblCompany          $tblCompany
     * @param TblType             $tblType
     * @param TblCourse           $tblCourse
     * @param string              $transferDate
     * @param string              $Remark
     *
     * @return bool|ServiceAPP\Entity\TblStudentTransfer|IFormInterface
     */
    public function createTransfer(
        IFormInterface $Form = null,
        $Meta = null,
        $PersonId,
        $StudentTransferTypeIdentifier,
        $tblCompany = null,
        $tblType = null,
        $tblCourse = null,
        $transferDate = '',
        $Remark = ''
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        if (isset($Meta)) {

            foreach ($Meta as $Category) {
                foreach ($Category as $Type) {
                    foreach ($Type as $Field => $Value) {
                        if ($Field == 'School') {
                            $tblCompany = Company::useService()->getCompanyById($Value);
                        }
                        if ($Field == 'Type') {
                            $tblType = Type::useService()->getTypeById($Value);
                        }
                        if ($Field == 'Course') {
                            $tblCourse = Course::useService()->getCourseById($Value);
                        }
                    }
                }
            }
        }

        $tblPerson = PersonAPP::useService()->getPersonById($PersonId);
        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier($StudentTransferTypeIdentifier);
        if ($tblPerson && $tblStudentTransferType) {
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
            } else {
                $tblStudent = $this->createStudent($tblPerson);
            }
        }

        if (isset($tblStudentTransfer) && $tblStudentTransfer) {
            if ($tblCompany === null) {
                $tblCompany = $tblStudentTransfer->getServiceTblCompany();

            }
            if ($tblType === null) {
                $tblType = $tblStudentTransfer->getServiceTblType();
            }
            if ($tblCourse === null) {
                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
            }
            if ($transferDate == '') {
                $transferDate = $tblStudentTransfer->getTransferDate();
            }
            if ($Remark == '') {
                $Remark = $tblStudentTransfer->getRemark();
            }
        }
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }
        if (!$tblType && null !== $tblType) {
            $tblType = null;
        }
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
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