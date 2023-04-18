<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Text\Repository\Success;

abstract class ServiceYearChange extends ServiceTeacher
{
    /**
     * @param TblType $tblSchoolType
     * @param TblYear $tblYearSource
     * @param TblYear $tblYearTarget
     * @param bool $hasOptionTeacherLectureship
     * @param bool $isSave
     *
     * @return array[]|true
     */
    public function getYearChangeData(TblType $tblSchoolType, TblYear $tblYearSource, TblYear $tblYearTarget,
        bool $hasOptionTeacherLectureship, bool $isSave)
    {
        $dataSourceList = array();
        $dataTargetList = array();
        $courseSourceList = array();
        $hasAddStudentEducationList = array();
        $createStudentEducationList = array();
        $tblMemberTypeStudent = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT);
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYearSource, $tblSchoolType))) {
            $tblStudentEducationList = $this->getSorter($tblStudentEducationList)->sortObjectBy('Sort');
            /** @var TblStudentEducation $tblStudentEducationSource */
            foreach ($tblStudentEducationList as $tblStudentEducationSource) {
                if (($tblPerson = $tblStudentEducationSource->getServiceTblPerson())
                    && !$tblStudentEducationSource->isInActive()
                    && ($level = $tblStudentEducationSource->getLevel())
                ) {
                    $dataSourceList[$level][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                    if (($tblStudentEducationTarget = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYearTarget))) {
                        $dataTargetList[$tblStudentEducationTarget->getLevel() ?: 'keine'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                    } elseif ($level < $tblSchoolType->getMaxLevel()) {
                        $hasAddStudentEducationList[$level + 1] = 1;
                        $dataTargetList[$level + 1][$tblPerson->getId()] = new Success(new Plus() . ' ' . $tblPerson->getLastFirstName());
                        if ((!$tblStudentEducationTarget || !$tblStudentEducationTarget->getTblDivision())
                            && ($tblDivision = $tblStudentEducationSource->getTblDivision())
                            && !isset($courseSourceList[$tblDivision->getId()])
                        ) {
                            $courseSourceList[$tblDivision->getId()] = $tblDivision->getName();
                        }
                        if ((!$tblStudentEducationTarget || !$tblStudentEducationTarget->getTblCoreGroup())
                            && ($tblCoreGroup = $tblStudentEducationSource->getTblCoreGroup())
                            && !isset($courseSourceList[$tblCoreGroup->getId()])
                        ) {
                            $courseSourceList[$tblCoreGroup->getId()] = $tblCoreGroup->getName();
                        }
                        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseMemberListByPersonAndYearAndMemberType($tblPerson, $tblYearSource, $tblMemberTypeStudent))) {
                            foreach ($tblDivisionCourseList as $tblDivisionCourseMember) {
                                if (($temp = $tblDivisionCourseMember->getTblDivisionCourse())
                                    && !isset($courseSourceList[$temp->getId()])
                                ) {
                                    $courseSourceList[$temp->getId()] = $temp->getName();
                                }
                            }
                        }

                        if ($isSave) {
                            // create TblStudentEducation
                            $tblStudentEducationCreate = new TblStudentEducation();
                            $tblStudentEducationCreate->setServiceTblPerson($tblPerson);
                            $tblStudentEducationCreate->setServiceTblYear($tblYearTarget);
                            $tblStudentEducationCreate->setServiceTblCompany($tblStudentEducationSource->getServiceTblCompany() ?: null);
                            $tblStudentEducationCreate->setServiceTblSchoolType($tblStudentEducationSource->getServiceTblSchoolType() ?: null);
                            $tblStudentEducationCreate->setLevel($level + 1);

                            // todo Klasse und Stammgruppe

                            $createStudentEducationList[] = $tblStudentEducationCreate;
                        }
                    }
                }
            }
        }

        /**
         * Kurse und Lehraufträge aufbereiten
         */
        asort($courseSourceList, SORT_NATURAL);
        $hasAddCoursesList = array();
        $dataCourseLeft = array();
        $dataCourseRight = array();
        $hasAddTeacherLectureshipList = array();
        $dataTeacherLectureshipLeft = array();
        $dataTeacherLectureshipRight = array();
        foreach ($courseSourceList as $divisionCourseId => $name) {
            if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                $dataCourseLeft[$tblDivisionCourse->getTypeIdentifier()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName();
                $newName = $tblDivisionCourse->getName();
                if (preg_match_all('!\d+!', $tblDivisionCourse->getName(), $matches)) {
                    $pos = strpos($tblDivisionCourse->getName(), $matches[0][0]);
                    if ($pos === 0) {
                        $level = intval($matches[0][0]);
                        $newName = ($level + 1) . substr($newName, strlen($level));
                    }
                }

                // prüfen, ob es den kurs im neuen schuljahr schon gibt
                if (($tblDivisionCourseFuture = DivisionCourse::useService()->getDivisionCourseByNameAndYear($newName, $tblYearTarget))) {
                    $newName = $tblDivisionCourseFuture->getName();
                    $isAdd = false;
                } else {
                    $hasAddCoursesList[$tblDivisionCourse->getTypeIdentifier()] = 1;
                    $isAdd = true;
                }
                $dataCourseRight[$tblDivisionCourse->getTypeIdentifier()][] = $isAdd ? new Success(new Plus() . ' ' . $newName) : $newName;

                // Lehraufträge
                if ($hasOptionTeacherLectureship
                    && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearSource, null, $tblDivisionCourse))
                ) {
                    foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                        if (($tblTeacher = $tblTeacherLectureship->getServiceTblPerson())
                            && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                        ) {
                            $dataTeacherLectureshipLeft[$tblTeacher->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName();
                            // prüfen, ob der Lehrauftrag schon existiert
                            if ($tblDivisionCourseFuture
                                && DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearTarget, $tblTeacher, $tblDivisionCourseFuture, $tblSubject)
                            ) {
                                $dataTeacherLectureshipRight[$tblTeacher->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $newName;
                            } else {
                                $hasAddTeacherLectureshipList[$tblTeacher->getId()] = 1;
                                $dataTeacherLectureshipRight[$tblTeacher->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = new Success(new Plus()  . $newName);
                            }
                        }
                    }
                }
            }
        }

        if ($isSave) {
            (new Data($this->getBinding()))->createEntityListBulk($createStudentEducationList);

            return true;
        } else {
            return array(
                $hasAddStudentEducationList,
                $dataSourceList,
                $dataTargetList,
                $hasAddCoursesList,
                $dataCourseLeft,
                $dataCourseRight,
                $hasAddTeacherLectureshipList,
                $dataTeacherLectureshipLeft,
                $dataTeacherLectureshipRight
            );
        }
    }
}