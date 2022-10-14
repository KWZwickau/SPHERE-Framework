<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceStudentSubject extends AbstractService
{
    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool|null $hasGrading
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear, ?bool $hasGrading = null)
    {
        return (new Data($this->getBinding()))->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblStudentSubject
     */
    public function getStudentSubjectByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return TblStudentSubject[]|false
     */
    private function getStudentSubjectListByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        $tblStudentSubjectList = array();
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentList = DivisionCourse::useService()->getStudentListBy($tblDivisionCourse))
        ) {
            foreach ($tblStudentList as $tblPerson) {
                if (($tblStudentSubject = $this->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                    $tblStudentSubjectList[$tblPerson->getId()] = $tblStudentSubject;
                }
            }
        }

        return empty($tblStudentSubjectList) ? false : $tblStudentSubjectList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Data
     *
     * @return bool
     */
    public function createStudentSubjectList(TblDivisionCourse $tblDivisionCourse, $Data): bool
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblSubject = Subject::useService()->getSubjectById($Data['Subject']))
        ) {
            $hasGrading = isset($Data['HasGrading']);

            $createList = array();
            $updateList = array();
            $destroyList = array();

            if (($tblStudentSubjectList = $this->getStudentSubjectListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
                foreach ($tblStudentSubjectList as $personId => $tblStudentSubject) {
                    // löschen
                    if (!isset($Data['StudentList'][$personId])) {
                        $destroyList[] = $tblStudentSubject;
                    // update
                    } elseif ($tblStudentSubject->getHasGrading() != $hasGrading) {
                        $tblStudentSubject->setHasGrading($hasGrading);
                        $updateList[] = $tblStudentSubject;
                    }
                }
            }

            // neu
            if (isset($Data['StudentList'])) {
                foreach ($Data['StudentList'] as $personId => $value) {
                    if (($tblPerson = Person::useService()->getPersonById($personId))) {
                        if (($tblStudentSubjectList && !isset($tblStudentSubjectList[$personId]))
                            || !$tblStudentSubjectList
                        ) {
                            $createList[] = TblStudentSubject::withParameter($tblPerson, $tblYear, $tblSubject, $hasGrading);
                        }
                    }
                }
            }

            if (!empty($createList)) {
                (new Data($this->getBinding()))->createStudentSubjectBulkList($createList);
            }
            if (!empty($updateList)) {
                (new Data($this->getBinding()))->updateStudentSubjectBulkList($updateList);
            }
            if (!empty($destroyList)) {
                (new Data($this->getBinding()))->destroyStudentSubjectBulkList($destroyList);
            }

            return true;
        }

        return false;
    }
}