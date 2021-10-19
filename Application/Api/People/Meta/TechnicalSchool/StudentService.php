<?php

namespace SPHERE\Application\Api\People\Meta\TechnicalSchool;

use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalSubjectArea;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Student\Service as ServiceAPP;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;

/**
 * Class StudentService
 *
 * @package SPHERE\Application\Api\People\Meta\TechnicalSchool
 */
class StudentService
{
    /**
     * @param array      $PersonIdArray
     * @param TblTechnicalCourse $tblTechnicalCourse
     *
     * @return bool|ServiceAPP\Entity\TblStudentTechnicalSchool|AbstractField
     */
    public function createTechnicalCourse(
        $PersonIdArray = array(),
        $tblTechnicalCourse = null
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
                    $tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool();
                    if (!$tblStudentTechnicalSchool) {
                        $tblStudentTechnicalSchool = new ServiceAPP\Entity\TblStudentTechnicalSchool();
                        $tblStudentTechnicalSchool->setServiceTblTechnicalCourse($tblTechnicalCourse);
                        $tblStudentTechnicalSchool->setPraxisLessons('');
                        $tblStudentTechnicalSchool->setDurationOfTraining('');
                        $tblStudentTechnicalSchool->setRemark('');
                        $tblStudentTechnicalSchool->setYearOfSchoolDiploma('');
                        $tblStudentTechnicalSchool->setYearOfTechnicalDiploma('');
                        $tblStudentTechnicalSchool->setHasFinancialAid(false);
                        $tblStudentTechnicalSchool->setFinancialAidApplicationYear('');
                        $tblStudentTechnicalSchool->setFinancialAidBureau('');

                        $tblStudentTechnicalSchool = Student::useService()->insertStudentTechnicalSchool(
                            '',
                            '',
                            '',
                            $tblTechnicalCourse
                        );
                        Student::useService()->updateStudentField(
                            $tblStudent,
                            $tblStudent->getTblStudentMedicalRecord() ? $tblStudent->getTblStudentMedicalRecord() : null,
                            $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                            $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                            $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                            $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                            $tblStudent->getTblStudentIntegration() ? $tblStudent->getTblStudentIntegration() : null,
                            $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                            $tblStudentTechnicalSchool
                        );
                    } else {
                        $BulkProtocol[] = clone $tblStudentTechnicalSchool;
                        $tblStudentTechnicalSchool->setServiceTblTechnicalCourse($tblTechnicalCourse);

                        $BulkSave[] = $tblStudentTechnicalSchool;
                    }
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave, $BulkProtocol);
            }

            return true;
        }

        return false;
    }

    /**
     * @param array      $PersonIdArray
     * @param TblTechnicalSubjectArea $tblTechnicalSubjectArea
     *
     * @return bool|ServiceAPP\Entity\TblStudentTechnicalSchool|AbstractField
     */
    public function createTechnicalSubjectArea(
        $PersonIdArray = array(),
        $tblTechnicalSubjectArea = null
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
                    $tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool();
                    if (!$tblStudentTechnicalSchool) {
                        $tblStudentTechnicalSchool = new ServiceAPP\Entity\TblStudentTechnicalSchool();
                        $tblStudentTechnicalSchool->setServiceTblTechnicalSubjectArea($tblTechnicalSubjectArea);
                        $tblStudentTechnicalSchool->setPraxisLessons('');
                        $tblStudentTechnicalSchool->setDurationOfTraining('');
                        $tblStudentTechnicalSchool->setRemark('');
                        $tblStudentTechnicalSchool->setYearOfSchoolDiploma('');
                        $tblStudentTechnicalSchool->setYearOfTechnicalDiploma('');
                        $tblStudentTechnicalSchool->setHasFinancialAid(false);
                        $tblStudentTechnicalSchool->setFinancialAidApplicationYear('');
                        $tblStudentTechnicalSchool->setFinancialAidBureau('');

                        $tblStudentTechnicalSchool = Student::useService()->insertStudentTechnicalSchool(
                            '',
                            '',
                            '',
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            '',
                            '',
                            $tblTechnicalSubjectArea
                        );
                        Student::useService()->updateStudentField(
                            $tblStudent,
                            $tblStudent->getTblStudentMedicalRecord() ? $tblStudent->getTblStudentMedicalRecord() : null,
                            $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                            $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                            $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                            $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                            $tblStudent->getTblStudentIntegration() ? $tblStudent->getTblStudentIntegration() : null,
                            $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                            $tblStudentTechnicalSchool
                        );
                    } else {
                        $BulkProtocol[] = clone $tblStudentTechnicalSchool;
                        $tblStudentTechnicalSchool->setServiceTblTechnicalSubjectArea($tblTechnicalSubjectArea);

                        $BulkSave[] = $tblStudentTechnicalSchool;
                    }
                }
            }
            if (!empty($BulkSave)) {
                return Student::useService()->bulkSaveEntityList($BulkSave, $BulkProtocol);
            }

            return true;
        }

        return false;
    }
}