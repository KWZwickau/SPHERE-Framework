<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
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
     *
     * @return array[]
     */
    public function getYearChangeData(TblType $tblSchoolType, TblYear $tblYearSource, TblYear $tblYearTarget, bool $hasOptionTeacherLectureship): array
    {
        $dataSourceList = array();
        $dataTargetList = array();
        $courseSourceList = array();
        $hasAddStudentEducationList = array();
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

                    // Schüler mit Abgangszeugnis oder Abschlusszeugnis ignorieren
                    if (Prepare::useService()->getIsLeaveOrDiplomaStudent($tblPerson, $tblYearSource)) {
                        continue;
                    }

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
                $newName = $this->getFutureDivisionCourseName($tblDivisionCourse);

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

    /**
     * @param TblType $tblSchoolType
     * @param TblYear $tblYearSource
     * @param TblYear $tblYearTarget
     * @param bool $hasOptionTeacherLectureship
     *
     * @return bool
     */
    public function saveYearChangeData(TblType $tblSchoolType, TblYear $tblYearSource, TblYear $tblYearTarget, bool $hasOptionTeacherLectureship): bool
    {
        $divisionCourseList = array();
        $createStudentEducationList = array();
        $createMemberList = array();
        $createStudentSubjectList = array();
        $createTeacherLectureshipList = array();

        $tblMemberTypeStudent = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT);

        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYearSource, $tblSchoolType))) {
            $tblStudentEducationList = $this->getSorter($tblStudentEducationList)->sortObjectBy('Sort');
            /** @var TblStudentEducation $tblStudentEducationSource */
            foreach ($tblStudentEducationList as $tblStudentEducationSource) {
                if (($tblPerson = $tblStudentEducationSource->getServiceTblPerson())
                    && !$tblStudentEducationSource->isInActive()
                    && ($level = $tblStudentEducationSource->getLevel())
                ) {
                    // Schüler mit Abgangszeugnis oder Abschlusszeugnis ignorieren
                    if (Prepare::useService()->getIsLeaveOrDiplomaStudent($tblPerson, $tblYearSource)) {
                        continue;
                    }

                    if (($tblStudentEducationTarget = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYearTarget))) {

                    } elseif ($level < $tblSchoolType->getMaxLevel()) {
                        $isLinkDiary = !($level == 4 && $tblSchoolType->getShortName() == 'GS')
                            && !($level == 10 && $tblSchoolType->getShortName() == 'Gy')
                            && !($level == 11 && $tblSchoolType->getShortName() == 'BGy');

                        $tblStudentEducationCreate = new TblStudentEducation();
                        $tblStudentEducationCreate->setServiceTblPerson($tblPerson);
                        $tblStudentEducationCreate->setServiceTblYear($tblYearTarget);
                        $tblStudentEducationCreate->setServiceTblCompany($tblStudentEducationSource->getServiceTblCompany() ?: null);
                        $tblStudentEducationCreate->setServiceTblSchoolType($tblStudentEducationSource->getServiceTblSchoolType() ?: null);
                        $tblStudentEducationCreate->setLevel($level + 1);

                        // Klasse
                        if (($tblDivision = $tblStudentEducationSource->getTblDivision())) {
                            if (!isset($divisionCourseList[$tblDivision->getId()])) {
                                $divisionCourseList[$tblDivision->getId()] = $this->getFutureDivisionCourse($tblDivision, $tblYearTarget, $createMemberList, $isLinkDiary);
                            }

                            $tblStudentEducationCreate->setTblDivision($divisionCourseList[$tblDivision->getId()] ?? null);
                        }

                        // Stammgruppe
                        if (($tblCoreGroup = $tblStudentEducationSource->getTblCoreGroup())) {
                            if (!isset($divisionCourseList[$tblCoreGroup->getId()])) {
                                $divisionCourseList[$tblCoreGroup->getId()] = $this->getFutureDivisionCourse($tblCoreGroup, $tblYearTarget, $createMemberList, $isLinkDiary);
                            }

                            $tblStudentEducationCreate->setTblCoreGroup($divisionCourseList[$tblCoreGroup->getId()] ?? null);
                        }

                        $createStudentEducationList[] = $tblStudentEducationCreate;

                        // weitere Kurse des Schülers ins neue Schuljahr übernehmen
                        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseMemberListByPersonAndYearAndMemberType(
                            $tblPerson, $tblYearSource, $tblMemberTypeStudent
                        ))) {
                            foreach ($tblDivisionCourseList as $tblDivisionCourseMember) {
                                if (($temp = $tblDivisionCourseMember->getTblDivisionCourse())
                                    && !$temp->getIsDivisionOrCoreGroup()
                                    && !$tblDivisionCourseMember->getLeaveDate()
                                ) {
                                    if (!isset($divisionCourseList[$temp->getId()])) {
                                        $divisionCourseList[$temp->getId()] = $this->getFutureDivisionCourse($temp, $tblYearTarget, $createMemberList, false);
                                    }

                                    if (($tblDivisionCourseFuture = $divisionCourseList[$temp->getId()] ?? null)
                                        && !DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourseFuture, $tblMemberTypeStudent, $tblPerson)
                                    ) {
                                        $createMemberList[] = TblDivisionCourseMember::withParameter(
                                            $tblDivisionCourseFuture, $tblMemberTypeStudent, $tblPerson, $tblDivisionCourseMember->getDescription()
                                        );
                                    }
                                }
                            }
                        }

                        // individuelle Fächer ins neue Schuljahr übernehmen
                        if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYearSource))) {
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                                    && !$tblStudentSubject->getServiceTblSubjectTable()
                                    && !$tblStudentSubject->getTblDivisionCourse()
                                ) {
                                    $createStudentSubjectList[] = TblStudentSubject::withParameter(
                                        $tblPerson, $tblYearTarget, $tblSubject, $tblStudentSubject->getHasGrading()
                                    );
                                }
                            }
                        }

                        // Schülerakte Fächer fürs alte Schuljahr fest speichern
                        if (($virtualSubjectList = DivisionCourse::useService()->getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndYear($tblPerson, $tblYearSource))) {
                            foreach ($virtualSubjectList as $virtualSubject) {
                                if (($tblSubject = $virtualSubject->getTblSubject())
                                    && !DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYearSource, $tblSubject)
                                ) {
                                    $createStudentSubjectList[] = TblStudentSubject::withParameter(
                                        $tblPerson, $tblYearSource, $tblSubject, $virtualSubject->getHasGrading(), $virtualSubject->getTblSubjectTable()
                                    );
                                }
                            }
                        }
                    }
                }
            }

            // Lehraufträge ins neue Schuljahr übertragen
            if ($hasOptionTeacherLectureship) {
                foreach($divisionCourseList as $divisionCourseIdSource => $tblDivisionCourseTarget) {
                    if (($tblDivisionCourseSource = DivisionCourse::useService()->getDivisionCourseById($divisionCourseIdSource))
                        && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearSource, null, $tblDivisionCourseSource))
                    ) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblTeacher = $tblTeacherLectureship->getServiceTblPerson())
                                && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                            ) {
                                // prüfen, ob der Lehrauftrag schon existiert
                                if (!DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearTarget, $tblTeacher, $tblDivisionCourseTarget, $tblSubject)) {
                                    $createTeacherLectureshipList[] = TblTeacherLectureship::withParameter(
                                        $tblTeacher, $tblYearTarget, $tblDivisionCourseTarget, $tblSubject
                                    );
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($createStudentEducationList)) {
                (new Data($this->getBinding()))->createEntityListBulk($createStudentEducationList);
            }
            if (!empty($createMemberList)) {
                (new Data($this->getBinding()))->createEntityListBulk($createMemberList);
            }
            if (!empty($createStudentSubjectList)) {
                (new Data($this->getBinding()))->createEntityListBulk($createStudentSubjectList);
            }
            if (!empty($createTeacherLectureshipList)) {
                (new Data($this->getBinding()))->createEntityListBulk($createTeacherLectureshipList);
            }
        }

        return true;
    }

    /**
     * DivisionCourse wird angelegt, falls dieser noch nicht existiert
     *
     * Mitglieder außer Schüler werden ebenfalls alle übertragen
     *
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblYear $tblYearTarget
     * @param array $createMemberList
     * @param bool $isLinkDiary
     *
     * @return TblDivisionCourse
     */
    private function getFutureDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblYear $tblYearTarget, array &$createMemberList, bool $isLinkDiary): TblDivisionCourse
    {
        $newName = $this->getFutureDivisionCourseName($tblDivisionCourse);
        // prüfen, ob es den kurs im neuen schuljahr schon gibt
        if (!($tblDivisionCourseFuture = DivisionCourse::useService()->getDivisionCourseByNameAndYear($newName, $tblYearTarget))) {
            $tblDivisionCourseFuture = (new Data($this->getBinding()))->createDivisionCourse(
                $tblDivisionCourse->getType(),
                $tblYearTarget,
                $newName,
                $tblDivisionCourse->getDescription(),
                $tblDivisionCourse->getIsShownInPersonData(),
                $tblDivisionCourse->getIsReporting(),
                $tblDivisionCourse->getServiceTblSubject() ?: null
            );

            // Klassenlehrer/Tutoren übertragen
            $this->copyMembers($tblDivisionCourse, $tblDivisionCourseFuture, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, $createMemberList);
            // Elternsprecher übertragen
            $this->copyMembers($tblDivisionCourse, $tblDivisionCourseFuture, TblDivisionCourseMemberType::TYPE_CUSTODY, $createMemberList);
            // Schülersprecher übertragen
            $this->copyMembers($tblDivisionCourse, $tblDivisionCourseFuture, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, $createMemberList);

            // pädagogisches Tagebuch verknüpfen
            if ($isLinkDiary) {
                Diary::useService()->addDiaryDivision($tblDivisionCourseFuture, $tblDivisionCourse);
            }
        }

        return $tblDivisionCourseFuture;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourseSource
     * @param TblDivisionCourse $tblDivisionCourseTarget
     * @param string $memberTypeIdentifier
     * @param array $createMemberList
     *
     * @return void
     */
    private function copyMembers(TblDivisionCourse $tblDivisionCourseSource, TblDivisionCourse $tblDivisionCourseTarget, string $memberTypeIdentifier, array &$createMemberList)
    {
        if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $tblDivisionCourseSource, $memberTypeIdentifier, false, false
        ))) {
            foreach ($tblMemberList as $tblMember) {
                if (($tblPerson = $tblMember->getServiceTblPerson())) {
                    $createMemberList[] = TblDivisionCourseMember::withParameter(
                        $tblDivisionCourseTarget, $tblMember->getTblMemberType(), $tblPerson, $tblMember->getDescription(), null, $tblMember->getSortOrder()
                    );
                }
            }
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    private function getFutureDivisionCourseName(TblDivisionCourse $tblDivisionCourse): string {
        $newName = $tblDivisionCourse->getName();
        if (preg_match_all('!\d+!', $tblDivisionCourse->getName(), $matches)) {
            $pos = strpos($tblDivisionCourse->getName(), $matches[0][0]);
            if ($pos === 0) {
                $level = intval($matches[0][0]);
                $newName = ($level + 1) . substr($newName, strlen($level));
            }
        }

        return $newName;
    }
}