<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Success;

abstract class ServiceYearChange extends ServiceTeacher
{
    /**
     * nur für die Vorschau-Anzeige
     *
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
        $divisionCourseSekTransitionList = array();

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
                        $isSekTransition = ($level == 10 && $tblSchoolType->getShortName() == 'Gy')
                            || ($level == 11 && $tblSchoolType->getShortName() == 'BGy');

                        $hasAddStudentEducationList[$level + 1] = 1;
                        $dataTargetList[$level + 1][$tblPerson->getId()] = new Success(new Plus() . ' ' . $tblPerson->getLastFirstName());
                        if ((!$tblStudentEducationTarget || !$tblStudentEducationTarget->getTblDivision())
                            && ($tblDivision = $tblStudentEducationSource->getTblDivision())
                            && !isset($courseSourceList[$tblDivision->getId()])
                        ) {
                            $courseSourceList[$tblDivision->getId()] = $tblDivision->getName();

                            if ($isSekTransition && !isset($divisionCourseSekTransitionList[$tblDivision->getId()])) {
                                $divisionCourseSekTransitionList[$tblDivision->getId()] = 1;
                            }
                        }
                        if ((!$tblStudentEducationTarget || !$tblStudentEducationTarget->getTblCoreGroup())
                            && ($tblCoreGroup = $tblStudentEducationSource->getTblCoreGroup())
                            && !isset($courseSourceList[$tblCoreGroup->getId()])
                        ) {
                            $courseSourceList[$tblCoreGroup->getId()] = $tblCoreGroup->getName();

                            if ($isSekTransition && !isset($divisionCourseSekTransitionList[$tblCoreGroup->getId()])) {
                                $divisionCourseSekTransitionList[$tblCoreGroup->getId()] = 1;
                            }
                        }
                        if (!$isSekTransition
                            && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseMemberListByPersonAndYearAndMemberType($tblPerson, $tblYearSource, $tblMemberTypeStudent))
                        ) {
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
                    // Lehraufträge bei Übergang ins SekII-Kurssystem Gy 10 oder BGy 11 nicht übernehmen
                    && !isset($divisionCourseSekTransitionList[$divisionCourseId])
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
        $divisionCourseSekTransitionList = array();

        $createStudentEducationList = array();
        $updateStudentEducationList = array();
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
                        // Schüler besitzt bereits eine SchülerBildung mit Klasse oder Stammgruppe fürs neue Schuljahr → keine Übernahme beim Schuljahreswechsel
                        if ($tblStudentEducationTarget->getTblDivision() || $tblStudentEducationTarget->getTblCoreGroup()
                            // oder hat eine andere Schulart
                            || (($tblSchoolTypeTarget = $tblStudentEducationTarget->getServiceTblSchoolType()) && $tblSchoolTypeTarget->getId() != $tblSchoolType->getId())
                            // oder hat nicht die nächste Klassenstufe
                            || ($tblStudentEducationTarget->getLevel() && $tblStudentEducationTarget->getLevel() != $level + 1)
                        ) {
                            continue;
                        }
                    }

                    if ($level < $tblSchoolType->getMaxLevel()) {
                        // Übergang ins SekII-Kurssystem Gy 10 oder BGy 11
                        $isSekTransition = ($level == 10 && $tblSchoolType->getShortName() == 'Gy')
                            || ($level == 11 && $tblSchoolType->getShortName() == 'BGy');
                        // Verknüpfung von pädagogischen Tagebüchern
                        $isLinkDiary = !$isSekTransition;

                        if ($tblStudentEducationTarget) {
                            $tblStudentEducationCreate = $tblStudentEducationTarget;
                        } else {
                            $tblStudentEducationCreate = new TblStudentEducation();
                            $tblStudentEducationCreate->setServiceTblPerson($tblPerson);
                            $tblStudentEducationCreate->setServiceTblYear($tblYearTarget);
                            $tblStudentEducationCreate->setServiceTblCompany($tblStudentEducationSource->getServiceTblCompany() ?: null);
                            $tblStudentEducationCreate->setServiceTblSchoolType($tblStudentEducationSource->getServiceTblSchoolType() ?: null);
                            $tblStudentEducationCreate->setServiceTblCourse($tblStudentEducationSource->getServiceTblCourse() ?: null);
                        }
                        $tblStudentEducationCreate->setLevel($level + 1);

                        // Klasse
                        if (($tblDivision = $tblStudentEducationSource->getTblDivision())) {
                            if (!isset($divisionCourseList[$tblDivision->getId()])) {
                                $divisionCourseList[$tblDivision->getId()] = $this->getFutureDivisionCourse($tblDivision, $tblYearTarget, $createMemberList, $isLinkDiary);

                                if ($isSekTransition && !isset($divisionCourseSekTransitionList[$tblDivision->getId()])) {
                                    $divisionCourseSekTransitionList[$tblDivision->getId()] = 1;
                                }
                            }

                            $tblStudentEducationCreate->setTblDivision($divisionCourseList[$tblDivision->getId()] ?? null);
                        }

                        // Stammgruppe
                        if (($tblCoreGroup = $tblStudentEducationSource->getTblCoreGroup())) {
                            if (!isset($divisionCourseList[$tblCoreGroup->getId()])) {
                                $divisionCourseList[$tblCoreGroup->getId()] = $this->getFutureDivisionCourse($tblCoreGroup, $tblYearTarget, $createMemberList, $isLinkDiary);

                                if ($isSekTransition && !isset($divisionCourseSekTransitionList[$tblCoreGroup->getId()])) {
                                    $divisionCourseSekTransitionList[$tblCoreGroup->getId()] = 1;
                                }
                            }

                            $tblStudentEducationCreate->setTblCoreGroup($divisionCourseList[$tblCoreGroup->getId()] ?? null);
                        }

                        if ($tblStudentEducationTarget) {
                            $updateStudentEducationList[] = $tblStudentEducationCreate;
                        } else {
                            $createStudentEducationList[] = $tblStudentEducationCreate;
                        }

                        // weitere Kurse des Schülers ins neue Schuljahr übernehmen, falls nicht Übergang ins SekII-Kurssystem Gy 10 oder BGy 11
                        if (!$isSekTransition
                            && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseMemberListByPersonAndYearAndMemberType(
                                $tblPerson, $tblYearSource, $tblMemberTypeStudent
                            )
                        )) {
                            foreach ($tblDivisionCourseList as $tblDivisionCourseMember) {
                                if (($temp = $tblDivisionCourseMember->getTblDivisionCourse())
                                    && !$temp->getIsDivisionOrCoreGroup()
                                    && !$tblDivisionCourseMember->getLeaveDate()
                                    // keine Lehrer-Gruppen
                                    && $temp->getTypeIdentifier() != TblDivisionCourseType::TYPE_TEACHER_GROUP
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

                        // Übergang ins SekII-Kurssystem Gy 10 oder BGy 11
                        if ($isSekTransition) {
                            // keine Fächer übernehmen
                        // SekII-Kurssystem Gy 11 oder BGy 12
                        } elseif (DivisionCourse::useService()->getIsCourseSystemBySchoolTypeAndLevel($tblSchoolType, $level)) {
                            // SekII-Kurs ins neue Schuljahr übernehmen
                            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYearSource))) {
                                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                                        && !$tblStudentSubject->getServiceTblSubjectTable()
                                        && ($tblDivisionCourseSekII = $tblStudentSubject->getTblDivisionCourse())
                                    ) {
                                        // SekII-Kurs anlegen
                                        if (!isset($divisionCourseList[$tblDivisionCourseSekII->getId()])) {
                                            $divisionCourseList[$tblDivisionCourseSekII->getId()] = $this->getFutureDivisionCourse(
                                                $tblDivisionCourseSekII, $tblYearTarget, $createMemberList, false
                                            );
                                        }
                                        if (($tblDivisionCourseFuture = $divisionCourseList[$tblDivisionCourseSekII->getId()] ?? null)) {
                                            $periodIdentifier = '';
                                            if (($explode = explode('/', $tblStudentSubject->getPeriodIdentifier()))
                                                && isset($explode[1])
                                            ) {
                                                $periodIdentifier = ($level + 1) . '/' . $explode[1];
                                            }

                                            $createStudentSubjectList[] = TblStudentSubject::withParameter(
                                                $tblPerson, $tblYearTarget, $tblSubject, $tblStudentSubject->getHasGrading(), null,
                                                $tblDivisionCourseFuture, $periodIdentifier
                                            );
                                        }
                                    }
                                }
                            }
                        // SekI
                        } else {
                            // individuelle Fächer ins neue Schuljahr übernehmen
                            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYearSource))) {
                                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                                        && !$tblStudentSubject->getServiceTblSubjectTable()
                                        && !$tblStudentSubject->getTblDivisionCourse()
                                        // prüfen, ob der Schüler das Fach bereits im neuen Schuljahr hat
                                        && !DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYearTarget, $tblSubject, true)
                                        // prüfen, ob Fach bereits in der Stundentafel für das nächste Schuljahr vorhanden ist
                                        && !DivisionCourse::useService()->getSubjectTableBy($tblSchoolType, $level + 1, $tblSubject)
                                    ) {
                                        $createStudentSubjectList[] = TblStudentSubject::withParameter(
                                            $tblPerson, $tblYearTarget, $tblSubject, $tblStudentSubject->getHasGrading()
                                        );
                                    }
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
                        // Lehraufträge bei Übergang ins SekII-Kurssystem Gy 10 oder BGy 11 nicht übernehmen
                        && !isset($divisionCourseSekTransitionList[$tblDivisionCourseSource->getId()])
                        && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearSource, null, $tblDivisionCourseSource))
                    ) {

                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblTeacher = $tblTeacherLectureship->getServiceTblPerson())
                                && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                            ) {
                                // prüfen, ob der Lehrauftrag schon existiert
                                if (!DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearTarget, $tblTeacher, $tblDivisionCourseTarget, $tblSubject)) {
                                    // key setzen, um doppelte Lehraufträge zu verhindern
                                    $createTeacherLectureshipList[$tblDivisionCourseTarget->getId() . '_' . $tblTeacher->getId() . '_' . $tblSubject->getId()]
                                        = TblTeacherLectureship::withParameter($tblTeacher, $tblYearTarget, $tblDivisionCourseTarget, $tblSubject);
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($createStudentEducationList)) {
                (new Data($this->getBinding()))->createEntityListBulk($createStudentEducationList);
            }
            if (!empty($updateStudentEducationList)) {
                (new Data($this->getBinding()))->updateEntityListBulk($updateStudentEducationList);
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
            // Klassensprecher übertragen
            $this->copyMembers($tblDivisionCourse, $tblDivisionCourseFuture, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, $createMemberList);

            // pädagogisches Tagebuch verknüpfen
            if ($isLinkDiary) {
                Diary::useService()->addDiaryDivision($tblDivisionCourseFuture, $tblDivisionCourse);
            }

            // Kurs ist als Sub-Kurs verknüpft -> Ober-Kurs + Verknüpfung zum neuen Kurs anlegen
            if (($tblAboveDivisionCourseList = DivisionCourse::useService()->getAboveDivisionCourseListBySubDivisionCourse($tblDivisionCourse))) {
                foreach ($tblAboveDivisionCourseList as $tblAboveDivisionCourse) {
                    $tblAboveDivisionCourseFuture = $this->getFutureDivisionCourse($tblAboveDivisionCourse, $tblYearTarget, $createMemberList, false);
                    DivisionCourse::useService()->addSubDivisionCourseToDivisionCourse($tblAboveDivisionCourseFuture, $tblDivisionCourseFuture);
                }
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

        // Mandanteneinstellung Klassen, Stammgruppen, Unterrichtsgruppen sollen nicht hochgezählt werden
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Lesson', 'DivisionCourse', 'NotIncrementNumericDivisionCourseName'
            ))
            && $tblSetting->getValue()
            && ($tblDivisionCourse->getIsDivisionOrCoreGroup() || $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHING_GROUP)
        ) {
            return $newName;
        }

        if (preg_match_all('!\d+!', $tblDivisionCourse->getName(), $matches)) {
            $pos = strpos($tblDivisionCourse->getName(), $matches[0][0]);
            if ($pos === 0) {
                $level = intval($matches[0][0]);
                $newName = ($level + 1) . substr($newName, strlen($level));
            }
        }

        return $newName;
    }

    /**
     * @param TblYear $tblYearSource
     * @param TblYear $tblYearTarget
     * @param bool $isSave
     *
     * @return string
     */
    public function getYearChangeForCoreGroupData(TblYear $tblYearSource, TblYear $tblYearTarget, bool $isSave = false): string
    {
        $content = '';
        $divisionCourseNameList = array();
        $divisionCourseTargetList = array();
        $divisionCourseStudentList = array();
        $createMemberList = array();
        $updateStudentEducationList = array();
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYearSource))) {
            $tblStudentEducationList = $this->getSorter($tblStudentEducationList)->sortObjectBy('Sort');
            /** @var TblStudentEducation $tblStudentEducationSource */
            foreach ($tblStudentEducationList as $tblStudentEducationSource) {
                if (($tblPerson = $tblStudentEducationSource->getServiceTblPerson())
                    && !$tblStudentEducationSource->isInActive()
                    && ($tblCoreGroupSource = $tblStudentEducationSource->getTblCoreGroup())
                    && ($tblStudentEducationTarget = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYearTarget))
                    && !$tblStudentEducationTarget->getTblCoreGroup()
                ) {
                    if (!isset($divisionCourseNameList[$tblCoreGroupSource->getId()])) {
                        $divisionCourseNameList[$tblCoreGroupSource->getId()] = DivisionCourse::useService()->getFutureDivisionCourseName($tblCoreGroupSource);

                        if ($isSave) {
                            $divisionCourseTargetList[$tblCoreGroupSource->getId()] = DivisionCourse::useService ()->getFutureDivisionCourse(
                                $tblCoreGroupSource, $tblYearTarget, $createMemberList, false
                            );
                        }
                    }

                    $divisionCourseStudentList[$tblCoreGroupSource->getId()][$tblPerson->getId()] = $tblPerson->getLastFirstName();

                    if ($isSave) {
                        $tblStudentEducationTarget->setTblCoreGroup($divisionCourseTargetList[$tblCoreGroupSource->getId()] ?? null);
                        $updateStudentEducationList[] = $tblStudentEducationTarget;
                    }
                }
            }
        }

        foreach ($divisionCourseNameList as $divisionCourseId => $name) {
            if (($tblDivisionCourseSource = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                $content .= new Panel (
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn($tblDivisionCourseSource->getDisplayName(), 5),
                        new LayoutColumn('->', 2),
                        new LayoutColumn($name, 5)
                    )))),
                    $divisionCourseStudentList[$divisionCourseId]
                );
            }
        }

        if ($isSave) {
            if (!empty($updateStudentEducationList)) {
                (new Data($this->getBinding()))->updateEntityListBulk($updateStudentEducationList);
            }
            if (!empty($createMemberList)) {
                (new Data($this->getBinding()))->createEntityListBulk($createMemberList);
            }
        }

        return $content;
    }
}