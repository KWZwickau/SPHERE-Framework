<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractData;

abstract class DataMigrate extends AbstractData
{
    private $tblSubjectForeignLanguageList;
    private $tblSubjectReligionList;
    private $tblSubjectProfileList;
    private $tblSubjectOrientationList;
    private $tblSubjectElectiveList;

//    protected function migrateAll()
//    {
//        if (!($this->getDivisionCourseAll())) {
//            ini_set('memory_limit', '2G');
//            $this->migrateTblDivisionToTblDivisionCourse();
//            $this->migrateDivisionContent();
//
//            $this->migrateTblGroupToTblDivisionCourse();
//        }
//    }

    /**
     * @return array
     */
    public function migrateTblDivisionToTblDivisionCourse(): array
    {
        $count = 0;
        $start = hrtime(true);
//        if (($tblDivisionList = Division::useService()->getDivisionAll())
//            && ($tblType = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_DIVISION))
//        ) {
//            $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('Id');
//            $Manager = $this->getEntityManager();
//            /** @var TblDivision $tblDivision */
//            foreach ($tblDivisionList as $tblDivision) {
//                if (($tblYear = $tblDivision->getServiceTblYear())) {
//                    $description = '';
//                    if ($tblDivision->getDescription()) {
//                        $description = $tblDivision->getDescription();
//                    } elseif (($tblSchoolType = $tblDivision->getType()) && strtolower($tblSchoolType->getShortName()) != strtolower($tblDivision->getName())) {
//                        $description = $tblSchoolType->getShortName();
//                    }
//                    $tblDivisionCourse = TblDivisionCourse::withParameterAndId($tblType, $tblYear, $tblDivision->getDisplayName(), $description,
//                        $tblDivision->getId(), true, true);
//
//                    // beim Speichern mit vorgegebener Id ist kein bulkSave möglich
//                    $Manager->saveEntityWithSetId($tblDivisionCourse);
//                    $count++;
//                }
//            }
//        }

        $end = hrtime(true);

        return array($count, round(($end - $start) / 1000000000, 2));
    }

    /**
     * @return array
     */
    public function migrateTblGroupToTblDivisionCourse(): array
    {
        $count = 0;
        $start = hrtime(true);
        if (($tblGroupList = Group::useService()->getGroupListByIsCoreGroup())
            && ($tblType = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_CORE_GROUP))
            && ($tblYearList = Term::useService()->getYearByNow())
            && ($tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblGroupTudor = Group::useService()->getGroupByMetaTable('TUDOR'))
            && ($tblTypeMemberTudor = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
        ) {
            /** @var TblYear $tblYear */
            $tblYear = reset($tblYearList);

            $Manager = $this->getEntityManager();
            foreach ($tblGroupList as $tblGroup) {
                $tblDivisionCourse = TblDivisionCourse::withParameter($tblType, $tblYear, $tblGroup->getName(), $tblGroup->getDescription(), true, true,
                    null, $tblGroup->getId());
                // bulkSave nicht möglich, da ansonsten noch keine Id vorhanden ist
                $Manager->saveEntity($tblDivisionCourse);
                $count++;

                if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                    foreach ($tblPersonList as $tblPerson) {
                        if (Group::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)) {
                            // prüfen, ob es schon vorhanden ist, eventuell durch Klassen
                            /** @var TblStudentEducation $tblStudentEducation */
                            if (!($tblStudentEducation = $Manager->getEntity('TblStudentEducation')->findOneBy(array(
                                TblStudentEducation::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
                                TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                                TblStudentEducation::ATTR_LEAVE_DATE => null
                            )))) {
                                $tblStudentEducation = new TblStudentEducation();
                                $tblStudentEducation->setServiceTblYear($tblYear);
                                $tblStudentEducation->setServiceTblPerson($tblPerson);
                            }

                            $tblStudentEducation->setTblCoreGroup($tblDivisionCourse);
                            $Manager->bulkSaveEntity($tblStudentEducation);

                        } elseif (Group::useService()->existsGroupPerson($tblGroupTudor, $tblPerson)) {
                            $Manager->bulkSaveEntity(TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblTypeMemberTudor, $tblPerson));
                        }
                    }
                }
            }

            $Manager->flushCache();
        }

        $end = hrtime(true);

        return array($count, round(($end - $start) / 1000000000, 2));
    }

    /**
     * @param TblYear $tblYear
     *
     * @return float
     */
    public function migrateDivisionContent(TblYear $tblYear): float
    {
        $start = hrtime(true);
        $this->tblSubjectForeignLanguageList = Subject::useService()->getSubjectForeignLanguageAll();
        $this->tblSubjectReligionList = Subject::useService()->getSubjectReligionAll();
        $this->tblSubjectProfileList = Subject::useService()->getSubjectProfileAll();
        $this->tblSubjectOrientationList = Subject::useService()->getSubjectOrientationAll();
        $this->tblSubjectElectiveList = Subject::useService()->getSubjectElectiveAll();

        $tblScoreTypeList = array();
        if (($tblTemp1List = Grade::useService()->getScoreTypeAll())) {
            foreach ($tblTemp1List as $tblTemp1) {
                $tblScoreTypeList[$tblTemp1->getIdentifier()] = $tblTemp1;
            }
        }
        $scoreTypeSubjectList = array();

        $tblScoreRuleList = array();
        if (($tblTemp2List = Grade::useService()->getScoreRuleAll(true))) {
            foreach ($tblTemp2List as $tblTemp2) {
                $tblScoreRuleList[$tblTemp2->getId()] = $tblTemp2;
            }
        }
        $scoreRuleSubjectList = array();

        $isCurrentYear = Term::useService()->getIsCurrentYear($tblYear);
//        if (($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))) {
//            $tblTypeAdvancedCourse = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_ADVANCED_COURSE);
//            $tblTypeBasicCourse = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_BASIC_COURSE);
//            $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('Id');
//            $Manager = $this->getEntityManager();
//            /** @var TblDivision $tblDivision */
//            foreach ($tblDivisionList as $tblDivision) {
//                if (($tblLevel = $tblDivision->getTblLevel())
//                    && ($tblSchoolType = $tblLevel->getServiceTblType())
//                    && ($tblDivisionCourse = $this->getDivisionCourseById($tblDivision->getId()))
//                ) {
//                    // bei EKPO 2 schulen mit Level 'W' und WVSZ hat Level mit leeren Namen
//                    $level = intval($tblLevel->getName());
//
//                    /**
//                     * Schüler der Klasse - TblDivisionStudent
//                     */
//                    // Jahrgangsübergreifende Klasse
//                    if ($tblLevel->getIsChecked()) {
//                        // WVSZ Förderschule ist nicht als jahrgangübergreifende Klasse angelegt und bei EKPO werden auch keine verwendet
//                        continue;
//                    } else {
//                        // feste Klasse
//                        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivision, true))) {
//                            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
//                                if (($tblPerson = $tblDivisionStudent->getServiceTblPerson())) {
//                                    // prüfen, ob es schon vorhanden ist, eventuell durch Klassen
//                                    /** @var TblStudentEducation $tblStudentEducation */
//                                    if (!($tblStudentEducation = $Manager->getEntity('TblStudentEducation')->findOneBy(array(
//                                        TblStudentEducation::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
//                                        TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
//                                        TblStudentEducation::ATTR_LEAVE_DATE => $tblDivisionStudent->getLeaveDateTime()
//                                    )))) {
//                                        $tblStudentEducation = new TblStudentEducation();
//                                        $tblStudentEducation->setServiceTblYear($tblYear);
//                                        $tblStudentEducation->setServiceTblPerson($tblPerson);
//                                        $tblStudentEducation->setLeaveDate($tblDivisionStudent->getLeaveDateTime());
//                                    }
//
//                                    $tblStudentEducation->setTblDivision($tblDivisionCourse);
//                                    $tblStudentEducation->setServiceTblCompany($tblDivision->getServiceTblCompany() ?: null);
//                                    $tblStudentEducation->setLevel($level ?: null);
//                                    $tblStudentEducation->setServiceTblSchoolType($tblSchoolType);
//                                    if (($tblStudent = $tblPerson->getStudent()) && ($tblCourse = $tblStudent->getCourse())) {
//                                        // Bildungsgang bei OS nicht vor Klasse 7 setzen
//                                        if ($tblSchoolType->getShortName() == 'OS' && $level < 7) {
//                                            $tblCourse = null;
//                                        }
//                                        $tblStudentEducation->setServiceTblCourse($tblCourse);
//                                    }
//
//                                    $tblStudentEducation->setDivisionSortOrder($tblDivisionStudent->getSortOrder());
//                                    $Manager->bulkSaveEntity($tblStudentEducation);
//                                }
//                            }
//                        }
//                    }
//
//                    /**
//                     * Klassenlehrer der Klasse - TblDivisionTeacher
//                     */
//                    if (($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))
//                        && ($tblTypeMemberTeacher = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
//                    ) {
//                        foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
//                            if ($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson()) {
//                                $Manager->bulkSaveEntity(TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblTypeMemberTeacher, $tblPersonTeacher,
//                                    $tblDivisionTeacher->getDescription()));
//                            }
//                        }
//                    }
//
//                    /**
//                     * Eltern Vertreter der Klasse - TblDivisionCustody
//                     */
//                    if (($tblDivisionCustodyList = Division::useService()->getDivisionCustodyAllByDivision($tblDivision))
//                        && ($tblTypeMemberCustody = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_CUSTODY))
//                    ) {
//                        foreach ($tblDivisionCustodyList as $tblDivisionCustody) {
//                            if ($tblPersonCustody = $tblDivisionCustody->getServiceTblPerson()) {
//                                $Manager->bulkSaveEntity(TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblTypeMemberCustody, $tblPersonCustody,
//                                    $tblDivisionCustody->getDescription()));
//                            }
//                        }
//                    }
//
//                    /**
//                     * Klassensprecher der Klasse - TblDivisionRepresentative
//                     */
//                    if (($tblDivisionRepresentativeList = Division::useService()->getDivisionRepresentativeByDivision($tblDivision))
//                        && ($tblTypeMemberRepresentative = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))
//                    ) {
//                        foreach ($tblDivisionRepresentativeList as $tblDivisionRepresentative) {
//                            if ($tblPersonRepresentative = $tblDivisionRepresentative->getServiceTblPerson()) {
//                                $Manager->bulkSaveEntity(TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblTypeMemberRepresentative,
//                                    $tblPersonRepresentative, $tblDivisionRepresentative->getDescription()));
//                            }
//                        }
//                    }
//
//                    /**
//                     * Fächer den Schülern und Lehraufträge den Lehrer zuordnen - TblDivisionSubject, TblSubjectGroup, TblSubjectStudent, TblSubjectTeacher
//                     */
//                    $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
//                    $isCourseSystem = Division::useService()->getIsDivisionCourseSystem($tblDivision);
//                    if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision, false))) {
//                        $variableStudentTableList = array();
//                        if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level))) {
//                            foreach ($tblSubjectTableList as $tblSubjectTableTemp) {
//                                if ($tblSubjectTableTemp->getStudentMetaIdentifier()) {
//                                    $variableStudentTableList[$tblSubjectTableTemp->getId()] = $tblSubjectTableTemp;
//
//                                }
//                            }
//                        }
//
//                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
//                            if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
//                                // prüfen, ob Fach bereits über die feste Stundentafel kommt
//                                $addStudentSubject = true;
//                                if (!$isCourseSystem) {
//                                    if (($tblSubjectTable = DivisionCourse::useService()->getSubjectTableBy($tblSchoolType, $level, $tblSubject))) {
//                                        if ($tblSubjectTable->getIsFixed()) {
//                                            $addStudentSubject = false;
//                                        }
//                                    }
//                                }
//
//                                if (($groupList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
//                                    $tblDivisionSubject->getTblDivision(),
//                                    $tblDivisionSubject->getServiceTblSubject()
//                                ))) {
//                                    // Fach-Gruppen
//                                    foreach ($groupList as $groupItem) {
//                                        if (($tblSubjectStudentList = Division::useService()->getStudentByDivisionSubject($groupItem))
//                                            && ($tblSubjectGroup = $groupItem->getTblSubjectGroup())
//                                        ) {
//                                            // SekII-Kurs als Kurs anlegen
//                                            if ($isCourseSystem) {
//                                                // Erkennung Untis: 11Gy EN-L-1
//                                                if (strpos($tblSubjectGroup->getName(), '-L-') !== false
//                                                    || strpos($tblSubjectGroup->getName(), '-G-') !== false
//                                                ) {
//                                                    $newCourseName = $tblLevel->getName() . $tblSchoolType->getShortName() . ' ' . $tblSubjectGroup->getName();
//                                                // Inidware: 11Gy L-BIO1
//                                                } else {
//                                                    $newCourseName = $tblLevel->getName() . $tblSchoolType->getShortName() . ' '
//                                                       . ($tblSubjectGroup->isAdvancedCourse() ? 'L-' : 'G-') . $tblSubjectGroup->getName();
//                                                }
//
//                                                $tblDivisionCourseSekII = TblDivisionCourse::withParameter(
//                                                    $tblSubjectGroup->isAdvancedCourse() ? $tblTypeAdvancedCourse : $tblTypeBasicCourse,
//                                                    $tblYear,
//                                                    $newCourseName,
//                                                    '',
//                                                    $tblSubjectGroup->isAdvancedCourse(),
//                                                    $tblSubjectGroup->isAdvancedCourse(),
//                                                    $tblSubject,
//                                                    null,
//                                                    Division::useService()->getMigrateSekCourseString($tblDivision, $tblSubject, $tblSubjectGroup)
//                                                );
//                                                // bulkSave nicht möglich, da ansonsten noch keine Id vorhanden ist
//                                                $Manager->saveEntity($tblDivisionCourseSekII);
//                                            } else {
//                                                $tblDivisionCourseSekII = false;
//                                            }
//
//                                            // SEKII-Kurse
//                                            if ($isCourseSystem) {
//                                                // bei SekII-kursen SchülerFächer direkt mit neuem Kurs verknüpfen
//                                                for ($i = 1; $i <= 2; $i++) {
//                                                    foreach ($tblSubjectStudentList as $tblSubjectStudent) {
//                                                        $Manager->bulkSaveEntity(TblStudentSubject::withParameter(
//                                                            $tblSubjectStudent, $tblYear, null, $groupItem->getHasGrading(), null,
//                                                            $tblDivisionCourseSekII ?: null, $level . '/' . $i
//                                                        ));
//                                                    }
//                                                }
//                                            // normale Fächer, keine SEKII-Kurse
//                                            } else {
//                                                if ($addStudentSubject) {
//                                                    foreach ($tblSubjectStudentList as $tblSubjectStudent) {
//                                                        $tblSubjectTable = null;
//                                                        if ($this->getAddStudentSubject($tblSubjectStudent, $tblSchoolType, $level, $tblSubject,
//                                                            $variableStudentTableList, $tblSubjectTable, $isCurrentYear
//                                                        )) {
//                                                            $Manager->bulkSaveEntity(TblStudentSubject::withParameter(
//                                                                $tblSubjectStudent, $tblYear, $tblSubject, $groupItem->getHasGrading(), $tblSubjectTable ?: null
//                                                            ));
//                                                        }
//                                                    }
//                                                }
//                                            }
//
//                                            // Lehraufträge bei Gruppen
//                                            if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($groupItem))) {
//                                                foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
//                                                    if (($tblTeacherPerson = $tblSubjectTeacher->getServiceTblPerson())) {
//                                                        $Manager->bulkSaveEntity(TblTeacherLectureship::withParameter(
//                                                            $tblTeacherPerson,
//                                                            $tblYear,
//                                                            $tblDivisionCourseSekII ?: $tblDivisionCourse,
//                                                            $tblSubject,
//                                                            $tblDivisionCourseSekII ? '' : $tblSubjectGroup->getName()
//                                                        ));
//                                                    }
//                                                }
//                                            }
//
////                                            // Berechnungsvorschrift an SekII-Kursen
////                                            if ($tblDivisionCourseSekII
////                                                && ($tblScoreRuleSubjectGroup = Gradebook::useService()->getScoreRuleSubjectGroupByDivisionAndSubjectAndGroup(
////                                                    $tblDivision, $tblSubject, $tblSubjectGroup
////                                                ))
////                                                && ($tblScoreRuleOld = $tblScoreRuleSubjectGroup->getTblScoreRule())
////                                            ) {
////                                                $tblScoreRule = $tblScoreRuleList[$tblScoreRuleOld->getId()];
////                                                $Manager->bulkSaveEntity(new TblScoreRuleSubjectDivisionCourse($tblDivisionCourseSekII, $tblSubject, $tblScoreRule));
////                                            }
//                                        }
//                                    }
//                                } else {
//                                    // gesamte Klasse
//                                    if (!$isCourseSystem && $addStudentSubject && $tblPersonList) {
//                                        $tblSubjectTable = null;
//                                        foreach ($tblPersonList as $tblPersonItem) {
//                                            if ($this->getAddStudentSubject($tblPersonItem, $tblSchoolType, $level, $tblSubject,
//                                                $variableStudentTableList, $tblSubjectTable, $isCurrentYear
//                                            )) {
//                                                $Manager->bulkSaveEntity(TblStudentSubject::withParameter(
//                                                    $tblPersonItem, $tblYear, $tblSubject, $tblDivisionSubject->getHasGrading(), $tblSubjectTable ?: null
//                                                ));
//                                            }
//                                        }
//                                    }
//                                }
//
//                                // Lehraufträge ohne Gruppen
//                                if (!$isCourseSystem && ($tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))) {
//                                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
//                                        if (($tblTeacherPerson = $tblSubjectTeacher->getServiceTblPerson())) {
//                                            $Manager->bulkSaveEntity(TblTeacherLectureship::withParameter($tblTeacherPerson, $tblYear, $tblDivisionCourse, $tblSubject));
//                                        }
//                                    }
//                                }
//
//                                // Bewertungssystem und Berechnungsvorschrift
////                                if (($tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject($tblDivision, $tblSubject))) {
////                                    // Bewertungssystem nur bei aktuellem Schuljahr, es gibt beim Bewertungssystem keine Schuljahre mehr
////                                    if ($isCurrentYear && ($tblScoreTypeOld = $tblScoreRuleDivisionSubject->getTblScoreType())) {
////                                        if (!isset($scoreTypeSubjectList[$tblSchoolType->getId()][$level][$tblSubject->getId()])) {
////                                            $tblScoreType = $tblScoreTypeList[$tblScoreTypeOld->getIdentifier()];
////                                            $scoreTypeSubjectList[$tblSchoolType->getId()][$level][$tblSubject->getId()] = $tblScoreType;
////                                            $Manager->bulkSaveEntity(new TblScoreTypeSubject($tblSchoolType, $level, $tblSubject, $tblScoreType));
////                                        }
////                                    }
////
////                                    // Berechnungsvorschrift
////                                    if (($tblScoreRuleOld = $tblScoreRuleDivisionSubject->getTblScoreRule())) {
////                                        if (!isset($scoreRuleSubjectList[$tblSchoolType->getId()][$level][$tblSubject->getId()])) {
////                                            $tblScoreRule = $tblScoreRuleList[$tblScoreRuleOld->getId()];
////                                            $scoreRuleSubjectList[$tblSchoolType->getId()][$level][$tblSubject->getId()] = $tblScoreRule;
////                                            $Manager->bulkSaveEntity(new TblScoreRuleSubject($tblYear, $tblSchoolType, $level, $tblSubject, $tblScoreRule));
////                                        }
////                                    }
////                                }
//                            }
//                        }
//                    }
//                }
//            }
//
//            $Manager->flushCache();
//        }

        $end = hrtime(true);

        return round(($end - $start) / 1000000000, 2);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     * @param $variableStudentTableList
     * @param TblSubjectTable|null $tblSubjectTable
     * @param bool $isCurrentYear
     *
     * @return bool
     */
    private function getAddStudentSubject(TblPerson $tblPerson, TblType $tblSchoolType, int $level, TblSubject $tblSubject, $variableStudentTableList,
        ?TblSubjectTable &$tblSubjectTable, bool $isCurrentYear): bool
    {
        // Fach ist in der Schülerakte gepflegt, nur bei aktuellem Schuljahr
        if ($isCurrentYear
            && ($virtualSubjectList = DivisionCourse::useService()->getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndSchoolTypeAndLevel(
                $tblPerson, $tblSchoolType, $level))
            && (isset($virtualSubjectList[$tblSubject->getId()]))
        ) {
            // nicht speichern, wenn es sich aus der Schülerakte ergibt
            return false;
        // Fach ist nicht in der Schülerakte hinterlegt, sondern nur in Bildung
        } elseif ($variableStudentTableList) {
            $isForeignLanguage = false;
            /** @var TblSubjectTable $tblSubjectTableTemp */
            foreach ($variableStudentTableList as $tblSubjectTableTemp) {
                switch ($tblSubjectTableTemp->getStudentMetaIdentifier()) {
                    case 'FOREIGN_LANGUAGE_1':
                    case 'FOREIGN_LANGUAGE_2':
                    case 'FOREIGN_LANGUAGE_3':
                    case 'FOREIGN_LANGUAGE_4': $tblSubjectMetaList = $this->tblSubjectForeignLanguageList; $isForeignLanguage = true; break;
                    case 'RELIGION': $tblSubjectMetaList = $this->tblSubjectReligionList; break;
                    case 'PROFILE': $tblSubjectMetaList = $this->tblSubjectProfileList; break;
                    case 'ORIENTATION': $tblSubjectMetaList = $this->tblSubjectOrientationList; break;
                    case 'ELECTIVE': $tblSubjectMetaList = $this->tblSubjectElectiveList; break;
                    default: $tblSubjectMetaList = false;
                }

                if ($tblSubjectMetaList && isset($tblSubjectMetaList[$tblSubject->getId()])
                    && (!$tblSubjectTableTemp->getServiceTblSubject() || $tblSubjectTableTemp->getServiceTblSubject()->getId() == $tblSubject->getId())
                ) {
                    // bei einer Fremdsprache muss es zusätzlich mit der Schülerakte übereinstimmen
                    if ($isForeignLanguage) {
                        if (($tblSubjectForeignLanguage = DivisionCourse::useService()->getSubjectFromStudentMetaIdentifier($tblSubjectTableTemp, $tblPerson))
                            && $tblSubjectForeignLanguage->getId() == $tblSubject->getId()
                        ) {
                            $tblSubjectTable = $tblSubjectTableTemp;
                            break;
                        }
                    } else {
                        $tblSubjectTable = $tblSubjectTableTemp;
                        break;
                    }
                }
            }
        }

        return true;
    }
}