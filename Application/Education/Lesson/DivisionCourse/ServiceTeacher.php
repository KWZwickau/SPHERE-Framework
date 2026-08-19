<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use DateTime;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;

abstract class ServiceTeacher extends ServiceSubjectTable
{
    /**
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblPerson
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblTeacherLectureship[]
     */
    public function getTeacherLectureshipListBy(TblYear $tblYear = null, TblPerson $tblPerson = null, TblDivisionCourse $tblDivisionCourse = null,
        TblSubject $tblSubject = null): false|array
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

        $tblYearList = array();
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
                        // Lehrauftrag anlegen
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
    public function getSubjectListByTeacherAndYear(TblPerson $tblPerson, TblYear $tblYear): false|array
    {
        return (new Data($this->getBinding()))->getSubjectListByTeacherAndYear($tblPerson, $tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $date
     *
     * @return array
     */
    public function getSubjectListByTeacherAndDate(TblPerson $tblPerson, string $date = 'now'): array
    {
        $dateTime = new DateTime($date);
        $tblSubjectList = [];
        if (($tblYearList = Term::useService()->getYearAllByDate($dateTime))) {
            foreach ($tblYearList as $tblYear) {
                if (($tblSubjectListTemp = DivisionCourse::useService()->getSubjectListByTeacherAndYear($tblPerson, $tblYear))) {
                    $tblSubjectList = array_merge($tblSubjectList, $tblSubjectListTemp);
                }
            }
        }

        return $tblSubjectList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param bool $isString
     *
     * @return string|TblPerson[]
     */
    public function getSubjectTeachers(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, bool $isString = true): string|array
    {
        $tblPersonList = array();

        if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(null, null, $tblDivisionCourse, $tblSubject))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())) {
                    $tblPersonList[$tblPerson->getId()] = $isString ? $tblTeacherLectureship->getTeacherName() : $tblPerson;
                }
            }
        }


        return $isString
            ? implode(", ", $tblPersonList)
            : $tblPersonList;
    }

    /**
     * @param $Filter
     * @param bool $isShowGroupName
     *
     * @return array|string
     */
    public function getTeacherLectureshipDataByFilter($Filter, bool $isShowGroupName = true): array|string
    {
        $hasFilter = false;
        $tblYearList = false;
        $tblSubjectFilter = Subject::useService()->getSubjectById($Filter['Subject'] ?? 0);
        $tblTeacherFilter = Person::useService()->getPersonById($Filter['Teacher'] ?? 0);

        $tblTeacherLectureshipList = array();
        // Name like
        if (isset($Filter['CourseName']) && $Filter['CourseName'] != '') {
            $hasFilter = true;
            if (isset($Filter['Year']) && $Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
                $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($Filter['CourseName'], $tblYearList ?: null);
            } elseif (isset($Filter['Year']) && ($tblYear = Term::useService()->getYearById($Filter['Year']))) {
                $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($Filter['CourseName'], array($tblYear));
            } else {
                return (new Warning('Bitte wählen Sie ein Schuljahr aus', new Exclamation()));
            }

            if ($tblDivisionCourseList) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (($tblTeacherLectureshipDivisionCourseList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                        null, $tblTeacherFilter ?: null, $tblDivisionCourse, $tblSubjectFilter ?: null
                    ))) {
                        $tblTeacherLectureshipList = array_merge($tblTeacherLectureshipDivisionCourseList, $tblTeacherLectureshipList);
                    }
                }
            }
        } elseif ($tblSubjectFilter || $tblTeacherFilter) {
            $hasFilter = true;
            if (isset($Filter['Year']) && $Filter['Year'] == -1) {
                if (($tblYearList = Term::useService()->getYearByNow())) {
                    foreach ($tblYearList as $tblYearItem) {
                        if (($tblTeacherLectureshipYearList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                            $tblYearItem, $tblTeacherFilter ?: null, null, $tblSubjectFilter ?: null
                        ))) {
                            $tblTeacherLectureshipList = array_merge($tblTeacherLectureshipYearList, $tblTeacherLectureshipList);
                        }
                    }
                }
                // ausgewähltes Schuljahr
            } elseif (isset($Filter['Year']) && ($tblYearFilter = Term::useService()->getYearById($Filter['Year']))) {
                $tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                    $tblYearFilter, $tblTeacherFilter ?: null, null, $tblSubjectFilter ?: null
                );
            } else {
                return (new Warning('Bitte wählen Sie ein Schuljahr aus', new Exclamation()));
            }
        }

        $personList = array();
        $personListWithoutTeacherGroup = array();
        // bei Filterung, nur Lehrer mit entsprechendem Lehrauftrag anzeigen
        if ($hasFilter) {
            if ($tblTeacherLectureshipList) {
                $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())
                        && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                        && ($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                    ) {
                        $personList[$tblPerson->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName()
                            . ($isShowGroupName && ($groupName = $tblTeacherLectureship->getGroupName()) ? ' (' . $groupName . ')' : '');
                    }
                }
            }

            if ($tblTeacherFilter && !isset($personList[$tblTeacherFilter->getId()])) {
                $personList[$tblTeacherFilter->getId()] = false;
            }
            // kein Filter, dann alle Lehrer anzeigen
        } else {
            if (isset($Filter['Year']) && $Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
            } elseif (isset($Filter['Year']) && ($tblYearFilter = Term::useService()->getYearById($Filter['Year']))) {
                $tblYearList = array($tblYearFilter);
            }

            if (($tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER')))) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
                foreach ($tblPersonList as $tblPerson) {
                    $tblTeacherLectureshipList = array();
                    if ($tblYearList) {
                        foreach ($tblYearList as $tblYear) {
                            if (($tblTeacherLectureshipYearList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson))) {
                                $tblTeacherLectureshipList = array_merge($tblTeacherLectureshipYearList, $tblTeacherLectureshipList);
                            }
                        }
                    }
                    if ($tblTeacherLectureshipList) {
                        $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                                && ($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                            ) {
                                $personList[$tblPerson->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName()
                                    . ($isShowGroupName && ($groupName = $tblTeacherLectureship->getGroupName()) ? ' (' . $groupName . ')' : '');
                            }
                        }
                    } else {
                        $personList[$tblPerson->getId()] = false;
                    }
                }
            }

            // Personen mit einem Lehrauftrag, welche nicht mehr in der festen Gruppe Lehrer sind
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                        $tblYear
                    ))) {
                        $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
                        /** @var TblTeacherLectureship $tblTeacherLectureship */
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())
                                && !isset($personList[$tblPerson->getId()])
                                && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                                && ($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                            ) {
                                $personListWithoutTeacherGroup[$tblPerson->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName()
                                    . ($isShowGroupName && ($groupName = $tblTeacherLectureship->getGroupName()) ? ' (' . $groupName . ')' : '');
                            }
                        }
                    }
                }
            }
        }

        return [
            'personListWithoutTeacherGroup' => $personListWithoutTeacherGroup,
            'personList' => $personList,
            'tblYearList' => $tblYearList,
            'tblSubjectFilter' => $tblSubjectFilter
        ];
    }
}