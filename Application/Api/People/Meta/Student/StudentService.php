<?php
namespace SPHERE\Application\Api\People\Meta\Student;

use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;

class StudentService
{

    /**
     * @param array  $PersonIdArray
     * @param string $Prefix
     *
     * @return bool
     */
    public function replacePrefixByPersonIdList(
        $PersonIdArray = array(),
        $Prefix = ''
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
                    $BulkProtocol[] = clone $tblStudent;
                    $tblStudent->setPrefix($Prefix);
                    $BulkSave[] = $tblStudent;
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
     * @param array  $PersonIdArray
     * @param string $Date
     *
     * @return bool
     */
    public function replaceStartDateByPersonIdList(
        $PersonIdArray = array(),
        $Date = ''
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
                    $BulkProtocol[] = clone $tblStudent;
                    $tblStudent->setSchoolAttendanceStartDate($Date);
                    $BulkSave[] = $tblStudent;
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