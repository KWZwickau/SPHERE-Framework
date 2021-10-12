<?php
namespace SPHERE\Application\Api\People\Meta\MedicalRecord;

use DateTime;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;

class MedicalRecordService
{

    /**
     * @param array       $PersonIdArray
     * @param string|null $Date
     *
     * @return bool
     */
    public function replaceMasernDateByPersonIdList(
        $PersonIdArray = array(),
        $Date = null
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
                    $tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord();
                    if (!$tblMedicalRecord) {
                        $tblMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                            '',
                            '',
                            '',
                            0,
                            '',
                            $Date
                        );
                        Student::useService()->updateStudentField(
                            $tblStudent,
                            $tblMedicalRecord,
                            $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                            $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                            $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                            $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                            $tblStudent->getTblStudentIntegration() ? $tblStudent->getTblStudentIntegration() : null,
                            $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                            $tblStudent->getTblStudentTechnicalSchool() ? $tblStudent->getTblStudentTechnicalSchool() : null
                        );

                    } else {
                        $BulkProtocol[] = clone $tblMedicalRecord;
                        /** @var DateTime|null $Date */
                        $tblMedicalRecord->setMasernDate($Date);

                        $BulkSave[] = $tblMedicalRecord;
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
     * @param array       $PersonIdArray
     * @param string|null $StudentMasernInfoId
     *
     * @return bool
     */
    public function replaceMasernDocumentByPersonIdList(
        $PersonIdArray = array(),
        $StudentMasernInfoId = null
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
                $tblStudentMasernInfo = Student::useService()->getStudentMasernInfoById($StudentMasernInfoId);
                if ($tblStudent && $tblStudentMasernInfo) {
                    $tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord();
                    if (!$tblMedicalRecord) {
                        $tblMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                            '',
                            '',
                            '',
                            0,
                            '',
                            null,
                            $tblStudentMasernInfo
                        );
                        Student::useService()->updateStudentField(
                            $tblStudent,
                            $tblMedicalRecord,
                            $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                            $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                            $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                            $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                            $tblStudent->getTblStudentIntegration() ? $tblStudent->getTblStudentIntegration() : null,
                            $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                            $tblStudent->getTblStudentTechnicalSchool() ? $tblStudent->getTblStudentTechnicalSchool() : null
                        );

                    } else {
                        $BulkProtocol[] = clone $tblMedicalRecord;
                        $tblMedicalRecord->setMasernDocumentType($tblStudentMasernInfo);

                        $BulkSave[] = $tblMedicalRecord;
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
     * @param array       $PersonIdArray
     * @param string|null $StudentMasernInfoId
     *
     * @return bool
     */
    public function replaceMasernCreatorByPersonIdList(
        $PersonIdArray = array(),
        $StudentMasernInfoId = null
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
                $tblStudentMasernInfo = Student::useService()->getStudentMasernInfoById($StudentMasernInfoId);
                if ($tblStudent && $tblStudentMasernInfo) {
                    $tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord();
                    if (!$tblMedicalRecord) {
                        $tblMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                            '',
                            '',
                            '',
                            0,
                            '',
                            null,
                            null,
                            $tblStudentMasernInfo
                        );
                        Student::useService()->updateStudentField(
                            $tblStudent,
                            $tblMedicalRecord,
                            $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                            $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                            $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                            $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                            $tblStudent->getTblStudentIntegration() ? $tblStudent->getTblStudentIntegration() : null,
                            $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                            $tblStudent->getTblStudentTechnicalSchool() ? $tblStudent->getTblStudentTechnicalSchool() : null
                        );

                    } else {
                        $BulkProtocol[] = clone $tblMedicalRecord;
                        $tblMedicalRecord->setMasernCreatorType($tblStudentMasernInfo);

                        $BulkSave[] = $tblMedicalRecord;
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