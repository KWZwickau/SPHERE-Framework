<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

abstract class ServiceTeacher extends ServiceSubjectTable
{
    /**
     * @param $Id
     *
     * @return false|TblTeacherLectureship
     */
    public function getTeacherLectureshipById($Id)
    {
        return (new Data($this->getBinding()))->getTeacherLectureshipById($Id);
    }

    /**
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblPerson
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblTeacherLectureship[]
     */
    public function getTeacherLectureshipListBy(TblYear $tblYear = null, TblPerson $tblPerson = null, TblDivisionCourse $tblDivisionCourse = null, TblSubject $tblSubject = null)
    {
        return (new Data($this->getBinding()))->getTeacherLectureshipListBy($tblYear, $tblPerson, $tblDivisionCourse, $tblSubject);
    }

    /**
     * @param $Filter
     * @param $PersonId
     * @param $Data
     *
     * @return string
     */
    public function createTeacherLectureship($Filter, $PersonId, $Data): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Lehrer nicht gefunden', new Exclamation());
        }

        $tblYearList = false;
        if (isset($Filter['Year'])) {
            if ($Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
            } elseif (($tblSelectedYear = Term::useService()->getYearById($Filter['Year']))) {
                $tblYearList[] = $tblSelectedYear;
            }
        }
        if ($tblYearList && ($tblSubject = Subject::useService()->getSubjectById($Data['Subject']))) {
            $divisionCourseList = array();
            // bestehende Lehraufträge des Lehrers
            foreach ($tblYearList as $tblYear) {
                if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))) {
                    foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                        if (($tblDivisionCourseByTeacher = $tblTeacherLectureship->getTblDivisionCourse())) {
                            $divisionCourseList[$tblDivisionCourseByTeacher->getId()] = $tblDivisionCourseByTeacher;
                            // Lehrauftrag löschen
                            if (!isset($Data['Courses'][$tblDivisionCourseByTeacher->getId()])) {
                                (new Data($this->getBinding()))->destroyTeacherLectureship($tblTeacherLectureship);
                            }
                        }
                    }
                }
            }

            if (isset($Data['Courses'])) {
                foreach ($Data['Courses'] as $divisionCourseId => $value) {
                    if (!isset($divisionCourseList[$divisionCourseId])
                        && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))
                        && ($tblYearByDivisionCourse = $tblDivisionCourse->getServiceTblYear())
                    ) {
                        // Lehraufrag anlegen
                        (new Data($this->getBinding()))->createTeacherLectureship($tblPerson, $tblYearByDivisionCourse, $tblDivisionCourse, $tblSubject);
                    }
                }
            }

            return new Success('Die Lehraufträge wurde erfolgreich gespeichert')
                . new Redirect('/Education/Lesson/TeacherLectureship', Redirect::TIMEOUT_SUCCESS, array('Filter' => $Filter));
        } else {
            return new Danger('Schuljahr oder Fach nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblSubject[]
     */
    public function getSubjectListByTeacherAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getSubjectListByTeacherAndYear($tblPerson, $tblYear);
    }
}