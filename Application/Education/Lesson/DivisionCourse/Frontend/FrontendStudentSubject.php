<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

class FrontendStudentSubject extends FrontendStudent
{
    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadStudentSubjectContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);
            $hasSubDivisionCourse = count($tblDivisionCourseList) > 1;

            $studentList = array();
            $subjectList = array();
            $subjectTableList = array();
            foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourseItem,
                    TblDivisionCourseMemberType::TYPE_STUDENT, true, false))
                ) {
                    $count = 0;
                    foreach ($tblStudentMemberList as $tblStudentMember) {
                        if (($tblPerson = $tblStudentMember->getServiceTblPerson()) && !$tblStudentMember->isInActive()) {
                            $fullName = $tblPerson->getLastFirstName();

                            $item['Number'] = ++$count;
                            $item['FullName'] = $fullName;
                            if ($hasSubDivisionCourse) {
                                $item['DivisionCourse'] = $tblDivisionCourseItem->getName();
                            }

                            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                                && ($level = $tblStudentEducation->getLevel())
                                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            ) {
                                /*
                                 * Stundentafel
                                 */
                                $addHeader = false;
                                if (!isset($subjectTableList[$tblSchoolType->getId()][$level])) {
                                    $addHeader = true;
                                    $subjectTableList[$tblSchoolType->getId()][$level] = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $level);
                                }
                                if ($subjectTableList[$tblSchoolType->getId()][$level]) {
                                    /** @var TblSubjectTable $tblSubjectTable */
                                    foreach ($subjectTableList[$tblSchoolType->getId()][$level] as $tblSubjectTable) {
                                        if (($tblSubject = $tblSubjectTable->getServiceTblSubject())
                                            && !$tblSubjectTable->getStudentMetaIdentifier()
                                            && !(DivisionCourse::useService()->getSubjectTableLinkBySubjectTable($tblSubjectTable))
                                        ) {
                                            if ($addHeader) {
                                                $subjectList[$tblSubject->getId()] = $tblSubject->getAcronym();
                                            }
                                            $item[$tblSubject->getId()] = new ToolTip(new Bold('X ') . new Small(new Muted('ST')), 'Stundentafel');
                                        }
                                        // todo else -> überprüfen ob erfüllt oder Fallback auf die Schülerakte
                                    }
                                }

                                /*
                                 * gespeicherte Fächer am Schüler
                                 */
                                if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear))) {
                                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                        if (($tblSubjectItem = $tblStudentSubject->getServiceTblSubject())) {
                                            if (!isset($item[$tblSubjectItem->getId()])) {
                                                $item[$tblSubjectItem->getId()] = new Bold('X');
                                                // todo edit button hier nicht nur bei individuellen
                                                $subjectList[$tblSubjectItem->getId()] = $tblSubjectItem->getAcronym() . ' ' . new Pen();
                                            }
                                        }
                                    }
                                }
                            }

//                            $item['Option'] = $isInActive ? ''
//                                : (new Link('Bearbeiten', ApiDivisionCourseStudent::getEndpoint(), new Pen()))
//                                    ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineEditDivisionCourseStudentContent(
//                                        $tblStudentEducation->getId(), $tblPerson->getId(), $DivisionCourseId));

                            $studentList[] = $item;
                        }
                    }
                }
            }

            $backgroundColor = '#E0F0FF';
            $headerColumnList[] = $this->getTableHeaderColumn('#', $backgroundColor);
            $headerColumnList[] = $this->getTableHeaderColumn('Schüler', $backgroundColor);
            if ($hasSubDivisionCourse) {
                $headerColumnList[] = $this->getTableHeaderColumn('Kurs', $backgroundColor);
            }
            // todo reihenfolge inhalt
            foreach ($subjectList as $acronym)
            {
                $headerColumnList[] = $this->getTableHeaderColumn($acronym, $backgroundColor);
            }

            return empty($studentList)
                ? new Warning('Keine Schüler dem Kurs zugewiesen')
                : $this->getTableCustom($headerColumnList, $studentList);
        }

        return '';
    }
}