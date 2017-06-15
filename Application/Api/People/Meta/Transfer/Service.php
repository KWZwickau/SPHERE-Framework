<?php

namespace SPHERE\Application\Api\People\Meta\Transfer;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service as ServiceAPP;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;

class Service extends ServiceAPP
{

    /**
     * @param array      $PersonIdArray
     * @param string     $StudentTransferTypeIdentifier
     * @param TblCompany $tblCompany
     *
     * @return bool|ServiceAPP\Entity\TblStudentTransfer|AbstractField
     */
    public function createTransferCompany(
        $PersonIdArray = array(),
        $StudentTransferTypeIdentifier,
        $tblCompany = null
    ) {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier($StudentTransferTypeIdentifier);

        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = Person::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    if ($tblPerson && $tblStudentTransferType) {
                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                        if (!$tblStudent) {
                            $tblStudent = Student::useService()->createStudent($tblPerson);
                        }
                    }
                }
                if ($tblStudent) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblStudentTransferType);
                    if (!$tblStudentTransfer) {
                        $tblStudentTransfer = new ServiceAPP\Entity\TblStudentTransfer();
                        $tblStudentTransfer->setTblStudent($tblStudent);
                        $tblStudentTransfer->setTblStudentTransferType($tblStudentTransferType);
                        $tblStudentTransfer->setRemark('');
                    }
                    $tblStudentTransfer->setServiceTblCompany($tblCompany);

                    $BulkSave[] = $tblStudentTransfer;
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave);
            }
            return true;
        }
        return false;
    }

    /**
     * @param array   $PersonIdArray
     * @param string  $StudentTransferTypeIdentifier
     * @param TblType $tblSchoolType
     *
     * @return bool|ServiceAPP\Entity\TblStudentTransfer|AbstractField
     */
    public function createTransferType(
        $PersonIdArray = array(),
        $StudentTransferTypeIdentifier,
        $tblSchoolType = null
    ) {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier($StudentTransferTypeIdentifier);

        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = Person::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    if ($tblPerson && $tblStudentTransferType) {
                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                        if (!$tblStudent) {
                            $tblStudent = Student::useService()->createStudent($tblPerson);
                        }
                    }
                }
                if ($tblStudent) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblStudentTransferType);
                    if (!$tblStudentTransfer) {
                        $tblStudentTransfer = new ServiceAPP\Entity\TblStudentTransfer();
                        $tblStudentTransfer->setTblStudent($tblStudent);
                        $tblStudentTransfer->setTblStudentTransferType($tblStudentTransferType);
                        $tblStudentTransfer->setRemark('');
                    }
                    $tblStudentTransfer->setServiceTblType($tblSchoolType);

                    $BulkSave[] = $tblStudentTransfer;
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave);
            }
            return false;
        }

        return true;
    }

    /**
     * @param array     $PersonIdArray
     * @param string    $StudentTransferTypeIdentifier
     * @param TblCourse $tblCourse
     *
     * @return bool|ServiceAPP\Entity\TblStudentTransfer|AbstractField
     */
    public function createTransferCourse(
        $PersonIdArray = array(),
        $StudentTransferTypeIdentifier,
        $tblCourse = null
    ) {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier($StudentTransferTypeIdentifier);

        if (!empty($PersonIdArray)) {
            foreach ($PersonIdArray as $PersonIdList) {
                $tblStudent = false;
                $tblPerson = Person::useService()->getPersonById($PersonIdList);
                if ($tblPerson) {
                    if ($tblPerson && $tblStudentTransferType) {
                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                        if (!$tblStudent) {
                            $tblStudent = Student::useService()->createStudent($tblPerson);
                        }
                    }
                }
                if ($tblStudent) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblStudentTransferType);
                    if (!$tblStudentTransfer) {
                        $tblStudentTransfer = new ServiceAPP\Entity\TblStudentTransfer();
                        $tblStudentTransfer->setTblStudent($tblStudent);
                        $tblStudentTransfer->setTblStudentTransferType($tblStudentTransferType);
                        $tblStudentTransfer->setRemark('');
                    }
                    $tblStudentTransfer->setServiceTblCourse($tblCourse);

                    $BulkSave[] = $tblStudentTransfer;
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