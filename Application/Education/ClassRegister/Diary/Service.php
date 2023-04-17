<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use DateInterval;
use DateTime;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Data;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryPredecessorDivisionCourse;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryStudent;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Setup;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear): array
    {
        return (new Data($this->getBinding()))->migrateYear($tblYear);
    }

    /**
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return (new Data($this->getBinding()))->getDiaryById($Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $withPredecessorDivision
     *
     * @return false|TblDiary[]
     */
    public function getDiaryAllByDivisionCourse(TblDivisionCourse $tblDivisionCourse, bool $withPredecessorDivision = false)
    {
        if ($withPredecessorDivision) {
            $divisionList = array();
            $resultList = array();
            $this->getPredecessorDivisionCourseList($tblDivisionCourse, $divisionList);
            /** @var TblDivisionCourse $tblDivisionCourseItem */
            foreach ($divisionList as $tblDivisionCourseItem) {
                if (($list = $this->getDiaryAllByDivisionCourse($tblDivisionCourseItem))) {
                    $resultList = array_merge($resultList, $list);
                }
            }

            return $resultList;
        } else {
            return (new Data($this->getBinding()))->getDiaryAllByDivisionCourse($tblDivisionCourse);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $resultList
     */
    private function getPredecessorDivisionCourseList(TblDivisionCourse $tblDivisionCourse, &$resultList) {
        $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
        if (($tblDiaryDivisionCourseList = $this->getDiaryPredecessorDivisionCourseListByDivisionCourse($tblDivisionCourse))) {
            foreach ($tblDiaryDivisionCourseList as $tblDiaryDivisionCourse) {
                if (($tblPredecessorDivision = $tblDiaryDivisionCourse->getServiceTblPredecessorDivisionCourse())) {
                    $this->getPredecessorDivisionCourseList($tblPredecessorDivision, $resultList);
                }
            }
        }
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByDiary(TblDiary $tblDiary)
    {
        return (new Data($this->getBinding()))->getDiaryStudentAllByDiary($tblDiary);
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDiary|null $tblDiary
     *
     * @return bool|Form
     */
    public function checkFormDiary(
        $Data,
        TblDivisionCourse $tblDivisionCourse,
        TblDiary $tblDiary = null
    ) {
        $error = false;

        $form = Diary::useFrontend()->formDiary($tblDivisionCourse, $tblDiary ? $tblDiary->getId() : null);

        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // SSW-1156 Einträge die älter als 3 Monate sind dürfen nicht mehr angelegt werden
            $now = new DateTime('now');
            $date = new DateTime($Data['Date']);
            $date->add(new DateInterval('P3M'));
            if ($date >= $now) {
                $form->setSuccess('Data[Date]');
            } else {
                $error = true;
                $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an, welches nicht älter als 3 Monate ist');
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function createDiary($Data, TblDivisionCourse $tblDivisionCourse): bool
    {
        $tblPersonTeacher = Account::useService()->getPersonByLogin();

        $tblDiary = (new Data($this->getBinding()))->createDiary(
            $tblDivisionCourse,
            $Data['Subject'],
            $Data['Content'],
            $Data['Date'],
            $Data['Place'],
            $tblPersonTeacher ?: null
        );

        if (isset($Data['Students'])) {
            foreach($Data['Students'] as $personId => $value) {
                if (($tblPersonItem = Person::useService()->getPersonById($personId))) {
                    (new Data($this->getBinding()))->addDiaryStudent($tblDiary, $tblPersonItem);
                }
            }
        }

        return true;
    }

    /**
     * @param TblDiary $tblDiary
     * @param $Data
     *
     * @return bool
     */
    public function updateDiary(TblDiary $tblDiary, $Data): bool
    {
        $tblPersonTeacher = Account::useService()->getPersonByLogin();

        (new Data($this->getBinding()))->updateDiary(
            $tblDiary,
            $Data['Subject'],
            $Data['Content'],
            $Data['Date'],
            $Data['Place'],
            $tblPersonTeacher ?: null
        );

        if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
            foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                if (($tblPersonRemove = $tblDiaryStudent->getServiceTblPerson())
                    && !isset($Data['Students'][$tblPersonRemove->getId()])
                ) {
                    (new Data($this->getBinding()))->removeDiaryStudent($tblDiaryStudent);
                }
            }
        }

        if (isset($Data['Students'])) {
            foreach($Data['Students'] as $personId => $value) {
                if (($tblPersonAdd = Person::useService()->getPersonById($personId))) {
                    (new Data($this->getBinding()))->addDiaryStudent($tblDiary, $tblPersonAdd);
                }
            }
        }

        return true;
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return bool
     */
    public function destroyDiary(TblDiary $tblDiary): bool
    {
        if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
            foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                (new Data($this->getBinding()))->removeDiaryStudent($tblDiaryStudent);
            }
        }

        return (new Data($this->getBinding()))->destroyDiary($tblDiary);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblPredecessorDivision
     *
     * @return TblDiaryPredecessorDivisionCourse
     */
    public function addDiaryDivision(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblPredecessorDivision): TblDiaryPredecessorDivisionCourse
    {
        return (new Data($this->getBinding()))->addDiaryDivision($tblDivisionCourse, $tblPredecessorDivision);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblDiaryPredecessorDivisionCourse[]
     */
    private function getDiaryPredecessorDivisionCourseListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getDiaryPredecessorDivisionCourseListByDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblDiary[]|bool
     */
    public function getDiaryAllByStudent(TblPerson $tblPerson)
    {
        $resultList = array();
        if (($tblDiaryStudentList = (new Data($this->getBinding()))->getDiaryStudentAllByStudent($tblPerson))) {
            foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                if (($tblDiary = $tblDiaryStudent->getTblDiary())) {
                    $resultList[$tblDiary->getId()] = $tblDiary;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDiary $tblDiary
     * @param TblPerson $tblPerson
     *
     * @return false|TblDiaryStudent
     */
    public function existsDiaryStudent(TblDiary $tblDiary, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->existsDiaryStudent($tblDiary, $tblPerson);
    }
}