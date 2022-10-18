<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
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

    protected function migrateAll()
    {
        if (!($this->getDivisionCourseAll())) {
            $this->migrateTblDivisionToTblDivisionCourse();
            $this->migrateDivisionContent();

            $this->migrateTblGroupToTblDivisionCourse();
        }
    }

    private function migrateTblDivisionToTblDivisionCourse()
    {
        if (($tblDivisionList = Division::useService()->getDivisionAll())
            && ($tblType = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_DIVISION))
        ) {
            $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('Id');
            $Manager = $this->getEntityManager();
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                // todo jahrgangsübergreifende klassen
                if (($tblYear = $tblDivision->getServiceTblYear())) {
                    $description = '';
                    if ($tblDivision->getDescription()) {
                        $description = $tblDivision->getDescription();
                    } elseif (($tblSchoolType = $tblDivision->getType()) && strtolower($tblSchoolType->getShortName()) != strtolower($tblDivision->getName())) {
                        $description = $tblSchoolType->getShortName();
                    }
                    $tblDivisionCourse = TblDivisionCourse::withParameterAndId($tblType, $tblYear, $tblDivision->getDisplayName(), $description,
                        $tblDivision->getId(), true, true);

                    // beim Speichern mit vorgegebener Id ist kein bulkSave möglich
                    $Manager->saveEntityWithSetId($tblDivisionCourse);
                }
            }
        }
    }

    private function migrateTblGroupToTblDivisionCourse()
    {
        if (($tblGroupList = Group::useService()->getGroupListByIsCoreGroup())
            && ($tblType = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_CORE_GROUP))
            && ($tblYearList = Term::useService()->getYearByNow())
            && ($tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblGroupTudor = Group::useService()->getGroupByMetaTable('TUDOR'))
            && ($tblTypeMemberTudor = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
        ) {
            // todo prüfen ob es das richtige Schuljahr ist, eventuell über Schüler
            /** @var TblYear $tblYear */
            $tblYear = reset($tblYearList);

            $Manager = $this->getEntityManager();
            foreach ($tblGroupList as $tblGroup) {
                $tblDivisionCourse = TblDivisionCourse::withParameter($tblType, $tblYear, $tblGroup->getName(), $tblGroup->getDescription(), true, true);
                // bulkSave nicht möglich, da ansonsten noch keine Id vorhanden ist
                $Manager->saveEntity($tblDivisionCourse);

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
    }

    private function migrateDivisionContent()
    {
        $this->tblSubjectForeignLanguageList = Subject::useService()->getSubjectForeignLanguageAll();
        $this->tblSubjectReligionList = Subject::useService()->getSubjectReligionAll();
        $this->tblSubjectProfileList = Subject::useService()->getSubjectProfileAll();
        $this->tblSubjectOrientationList = Subject::useService()->getSubjectOrientationAll();
        $this->tblSubjectElectiveList = Subject::useService()->getSubjectElectiveAll();

        if (($tblDivisionList = Division::useService()->getDivisionAll())) {
            $tblTypeAdvancedCourse = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_ADVANCED_COURSE);
            $tblTypeBasicCourse = $this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_BASIC_COURSE);
            $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('Id');
            $Manager = $this->getEntityManager();
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblYear = $tblDivision->getServiceTblYear())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                    && ($tblDivisionCourse = $this->getDivisionCourseById($tblDivision->getId()))
                ) {
                    $isCurrentYear = Term::useService()->getIsCurrentYear($tblYear);

                    /**
                     * Schüler der Klasse - TblDivisionStudent
                     */
                    // Jahrgangsübergreifende Klasse
                    if ($tblLevel->getIsChecked()) {
                        // todo jahrgangsübergreifende klassen
                        continue;
                    } else {
                        // feste Klasse
                        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivision, true))) {
                            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                                if (($tblPerson = $tblDivisionStudent->getServiceTblPerson())) {
                                    // prüfen, ob es schon vorhanden ist, eventuell durch Klassen
                                    /** @var TblStudentEducation $tblStudentEducation */
                                    if (!($tblStudentEducation = $Manager->getEntity('TblStudentEducation')->findOneBy(array(
                                        TblStudentEducation::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
                                        TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                                        TblStudentEducation::ATTR_LEAVE_DATE => $tblDivisionStudent->getLeaveDateTime()
                                    )))) {
                                        $tblStudentEducation = new TblStudentEducation();
                                        $tblStudentEducation->setServiceTblYear($tblYear);
                                        $tblStudentEducation->setServiceTblPerson($tblPerson);
                                        $tblStudentEducation->setLeaveDate($tblDivisionStudent->getLeaveDateTime());
                                    }

                                    $tblStudentEducation->setTblDivision($tblDivisionCourse);
                                    $tblStudentEducation->setServiceTblCompany($tblDivision->getServiceTblCompany() ?: null);
                                    $tblStudentEducation->setLevel(intval($tblLevel->getName()));
                                    $tblStudentEducation->setServiceTblSchoolType($tblSchoolType);
                                    if (($tblStudent = $tblPerson->getStudent()) && ($tblCourse = $tblStudent->getCourse())) {
                                        $tblStudentEducation->setServiceTblCourse($tblCourse);
                                    }

                                    $tblStudentEducation->setDivisionSortOrder($tblDivisionStudent->getSortOrder());
                                    $Manager->bulkSaveEntity($tblStudentEducation);
                                }
                            }
                        }
                    }

                    /**
                     * Klassenlehrer der Klasse - TblDivisionTeacher
                     */
                    if (($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))
                        && ($tblTypeMemberTeacher = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                    ) {
                        foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                            if ($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson()) {
                                $Manager->bulkSaveEntity(TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblTypeMemberTeacher, $tblPersonTeacher,
                                    $tblDivisionTeacher->getDescription()));
                            }
                        }
                    }

                    /**
                     * Eltern Vertreter der Klasse - TblDivisionCustody
                     */
                    if (($tblDivisionCustodyList = Division::useService()->getDivisionCustodyAllByDivision($tblDivision))
                        && ($tblTypeMemberCustody = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_CUSTODY))
                    ) {
                        foreach ($tblDivisionCustodyList as $tblDivisionCustody) {
                            if ($tblPersonCustody = $tblDivisionCustody->getServiceTblPerson()) {
                                $Manager->bulkSaveEntity(TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblTypeMemberCustody, $tblPersonCustody,
                                    $tblDivisionCustody->getDescription()));
                            }
                        }
                    }

                    /**
                     * Klassensprecher der Klasse - TblDivisionRepresentative
                     */
                    if (($tblDivisionRepresentativeList = Division::useService()->getDivisionRepresentativeByDivision($tblDivision))
                        && ($tblTypeMemberRepresentative = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))
                    ) {
                        foreach ($tblDivisionRepresentativeList as $tblDivisionRepresentative) {
                            if ($tblPersonRepresentative = $tblDivisionRepresentative->getServiceTblPerson()) {
                                $Manager->bulkSaveEntity(TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblTypeMemberRepresentative,
                                    $tblPersonRepresentative, $tblDivisionRepresentative->getDescription()));
                            }
                        }
                    }

                    /**
                     * Fächer den Schülern und Lehraufträge den Lehrer zuordnen - TblDivisionSubject, TblSubjectGroup, TblSubjectStudent, TblSubjectTeacher
                     */
                    $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                    $isCourseSystem = Division::useService()->getIsDivisionCourseSystem($tblDivision);
                    if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision, false))) {
                        $level = intval($tblLevel->getName());
                        $variableStudentTableList = array();
                        if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level))) {
                            foreach ($tblSubjectTableList as $tblSubjectTableTemp) {
                                if ($tblSubjectTableTemp->getStudentMetaIdentifier()) {
                                    $variableStudentTableList[$tblSubjectTableTemp->getId()] = $tblSubjectTableTemp;

                                }
                            }
                        }

                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                                // prüfen, ob Fach bereits über die feste Stundentafel kommt
                                // todo erstmal überhaupt nur Fächer von aktuellen Schuljahren mitnehmen -> erstmal doch speichern
                                $addStudentSubject = true;
                                if (!$isCourseSystem) {
                                    if (($tblSubjectTable = DivisionCourse::useService()->getSubjectTableBy($tblSchoolType, $level, $tblSubject))) {
                                        if ($tblSubjectTable->getIsFixed()) {
                                            $addStudentSubject = false;
                                        }
                                    }
                                }

                                if (($groupList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject()
                                ))) {
                                    // Fach-Gruppen
                                    foreach ($groupList as $groupItem) {
                                        if (($tblSubjectStudentList = Division::useService()->getStudentByDivisionSubject($groupItem))
                                            && ($tblSubjectGroup = $groupItem->getTblSubjectGroup())
                                        ) {
                                            // SekII-Kurs als Kurs anlegen
                                            if ($isCourseSystem) {
                                                $tblDivisionCourseSekII = TblDivisionCourse::withParameter(
                                                    $tblSubjectGroup->isAdvancedCourse() ? $tblTypeAdvancedCourse : $tblTypeBasicCourse,
                                                    $tblYear, $tblLevel->getName() . $tblSchoolType->getShortName() . ' ' . $tblSubjectGroup->getName(),
                                                    '',
                                                    $tblSubjectGroup->isAdvancedCourse(),
                                                    $tblSubjectGroup->isAdvancedCourse()
                                                );
                                                // bulkSave nicht möglich, da ansonsten noch keine Id vorhanden ist
                                                $Manager->saveEntity($tblDivisionCourseSekII);
                                            } else {
                                                $tblDivisionCourseSekII = false;
                                            }


                                            if ($isCourseSystem) {
                                                // todo bei SekII-kursen SchülerFächer direkt mit neuen Kurs, prüfen doppelt Speicherung wahrscheinlich am besten wie bei TblStudentEducation
                                            } else {
                                                if ($addStudentSubject) {
                                                    foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                                        $tblSubjectTable = null;
                                                        if ($this->getAddStudentSubject($tblSubjectStudent, $tblSchoolType, $level, $tblSubject,
                                                            $variableStudentTableList, $tblSubjectTable, $isCurrentYear
                                                        )) {
                                                            $Manager->bulkSaveEntity(TblStudentSubject::withParameter(
                                                                $tblSubjectStudent, $tblYear, $tblSubject, $groupItem->getHasGrading(), $tblSubjectTable ?: null
                                                            ));
                                                        }
                                                    }
                                                }
                                            }


                                            // Lehraufträge bei Gruppen
                                            if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($groupItem))) {
                                                foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                                    if (($tblTeacherPerson = $tblSubjectTeacher->getServiceTblPerson())) {
                                                        $Manager->bulkSaveEntity(TblTeacherLectureship::withParameter(
                                                            $tblTeacherPerson,
                                                            $tblYear,
                                                            $tblDivisionCourseSekII ?: $tblDivisionCourse,
                                                            $tblSubject,
                                                            $tblDivisionCourseSekII ? '' : $tblSubjectGroup->getName()
                                                        ));
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    // gesamte Klasse
                                    if (!$isCourseSystem && $addStudentSubject && $tblPersonList) {
                                        $tblSubjectTable = null;
                                        foreach ($tblPersonList as $tblPersonItem) {
                                            if ($this->getAddStudentSubject($tblPersonItem, $tblSchoolType, $level, $tblSubject,
                                                $variableStudentTableList, $tblSubjectTable, $isCurrentYear
                                            )) {
                                                $Manager->bulkSaveEntity(TblStudentSubject::withParameter(
                                                    $tblPersonItem, $tblYear, $tblSubject, $tblDivisionSubject->getHasGrading(), $tblSubjectTable ?: null
                                                ));
                                            }
                                        }
                                    }
                                }

                                // Lehraufträge ohne Gruppen
                                if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))) {
                                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                        if (($tblTeacherPerson = $tblSubjectTeacher->getServiceTblPerson())) {
                                            $Manager->bulkSaveEntity(TblTeacherLectureship::withParameter($tblTeacherPerson, $tblYear, $tblDivisionCourse, $tblSubject));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $Manager->flushCache();
        }
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