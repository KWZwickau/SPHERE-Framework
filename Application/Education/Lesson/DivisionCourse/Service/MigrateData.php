<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\System\Database\Binding\AbstractData;

abstract class MigrateData extends AbstractData
{
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
                    } elseif (($tblSchoolType = $tblDivision->getType())) {
                        $description = $tblSchoolType->getShortName();
                    }
                    $tblDivisionCourse = TblDivisionCourse::withParameterAndId($tblType, $tblYear, $tblDivision->getDisplayName(), $description,
                        $tblDivision->getId(), true, true, true);

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
                $tblDivisionCourse = TblDivisionCourse::withParameter($tblType, $tblYear, $tblGroup->getName(), $tblGroup->getDescription(), true, true, true);
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
        if (($tblDivisionList = Division::useService()->getDivisionAll())) {
            $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('Id');
            $Manager = $this->getEntityManager();
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblYear = $tblDivision->getServiceTblYear())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                    && ($tblDivisionCourse = $this->getDivisionCourseById($tblDivision->getId()))
                ) {
                    /**
                     * Schüler der Klasse - TblDivisionStudent
                     */
                    // Jahrgangsübergreifende Klasse
                    if ($tblLevel->getIsChecked()) {
                        // todo jahrgangsübergreifende klassen
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
                    if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision, false))) {
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                                if (($groupList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject()
                                ))) {
                                    // Fach-Gruppen
                                    foreach ($groupList as $groupItem) {
                                        if (($tblSubjectStudentList = Division::useService()->getStudentByDivisionSubject($groupItem))
                                            && ($tblSubjectGroup = $groupItem->getTblSubjectGroup())
                                        ) {
                                            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                                $Manager->bulkSaveEntity(TblStudentSubject::withParameter(
                                                    $tblSubjectStudent, $tblYear, $tblSubject, $groupItem->getHasGrading(), $tblSubjectGroup->isAdvancedCourse()
                                                ));
                                            }

                                            // Lehraufträge bei Gruppen
                                            if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($groupItem))) {
                                                foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                                    if (($tblTeacherPerson = $tblSubjectTeacher->getServiceTblPerson())) {
                                                        $Manager->bulkSaveEntity(TblTeacherLectureship::withParameter(
                                                            $tblTeacherPerson, $tblYear, $tblDivision, $tblSubject
                                                        ));
                                                    }
                                                }
                                           }
                                        }
                                    }
                                } else {
                                    // gesamte Klasse
                                    if ($tblPersonList) {
                                        foreach ($tblPersonList as $tblPersonItem) {
                                            $Manager->bulkSaveEntity(TblStudentSubject::withParameter(
                                                $tblPersonItem, $tblYear, $tblSubject, $tblDivisionSubject->getHasGrading()
                                            ));
                                        }
                                    }
                                }

                                // Lehraufträge ohne Gruppen
                                if (($tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))) {
                                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                        if (($tblTeacherPerson = $tblSubjectTeacher->getServiceTblPerson())) {
                                            $Manager->bulkSaveEntity(TblTeacherLectureship::withParameter($tblTeacherPerson, $tblYear, $tblDivision, $tblSubject));
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
}