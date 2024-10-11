<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success as SuccessLink;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
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
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);
            $hasSubDivisionCourse = count($tblDivisionCourseList) > 1;

            // SEKI
            $studentSekIList = array();
            $subjectList = array();
            $subjectTableList = array();
            $hasMissingSubjects = false;

            // SEKII
            $studentSekIIList = array();
            $divisionCourseSekIIList = array();

            foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourseItem,
                    TblDivisionCourseMemberType::TYPE_STUDENT, true, false))
                ) {
                    $countSekI = 0;
                    $countSekII = 0;
                    foreach ($tblStudentMemberList as $tblStudentMember) {
                        if (($tblPerson = $tblStudentMember->getServiceTblPerson()) && !$tblStudentMember->isInActive()) {
                            if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                                $studentSekIIList[] = $this->getStudentSekIIData($DivisionCourseId, $tblDivisionCourseItem, $tblYear, $tblPerson, $countSekII,
                                    $divisionCourseSekIIList, $hasMissingSubjects);
                            } else {
                                $studentSekIList[] = $this->getStudentSekIData($DivisionCourseId, $tblDivisionCourseItem, $tblYear, $tblPerson, $countSekI,
                                    $subjectList, $subjectTableList, $hasSubDivisionCourse, $hasMissingSubjects);
                            }
                        }
                    }
                }
            }

            $content = '';
            if (!empty($studentSekIList)) {
                list($headerColumnList, $contentList) = $this->setSekIHeader($studentSekIList, $subjectList, $hasSubDivisionCourse, $hasMissingSubjects);

                $content .= new Title(new Education() . ' Fächer der Schüler in der ' . $text
                    . (new Link('Bearbeiten', ApiStudentSubject::getEndpoint(), new Pen()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectContent($DivisionCourseId))
                );
                $content .= $this->getTableCustom($headerColumnList, $contentList);
            }

            if (!empty($studentSekIIList)) {
                list($headerColumnList, $contentList) = $this->setSekIIHeader($studentSekIIList, $divisionCourseSekIIList, $hasSubDivisionCourse);
                for ($i = 1; $i <= 2; $i++) {
                    $content .= new Title(new Education() . ' SekII-Kurse der Schüler im ' . new Bold($i . '. HJ') . ' in der ' . $text
                        . (new Link('Bearbeiten', ApiStudentSubject::getEndpoint(), new Pen()))
                            ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectDivisionCourseContent($DivisionCourseId, $i))
                        . ($i == 2
                            ? ' | ' . (new Link('Kurse des 1.HJ kopieren', ApiStudentSubject::getEndpoint(), new MoreItems()))
                                ->ajaxPipelineOnClick(ApiStudentSubject::pipelineOpenCopySubjectDivisionCourseModal($DivisionCourseId))
                            : ''
                        )
                    );
                    $content .= $this->getTableCustom($headerColumnList[$i], $contentList[$i]);
                }

            }

            return $content;
        }

        return '';
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param null $Data
     *
     * @return string
     */
    public function editStudentSubjectContent($DivisionCourseId, $SubjectId, $Data = null): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblDivisionCourse->getServiceTblYear())
        ) {
            $tblSubjectList = Subject::useService()->getSubjectAll();

            if ($SubjectId) {
                $global = $this->getGlobal();
                $global->POST['Data']['Subject'] = $SubjectId;
                $Data['Subject'] = $SubjectId;
                $global->savePost();

                // deaktiviertes Fach hinzufügen
                if (($tblSubject = Subject::useService()->getSubjectById($SubjectId)) && !$tblSubject->getIsActive()) {
                    $tblSubjectList[] = $tblSubject;
                }
            }

            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());

            return
                new Title(new Education() . ' Fächer der Schüler in der ' . $text
                    . (new Link('Anzeigen', ApiStudentSubject::getEndpoint(), new EyeOpen()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineLoadStudentSubjectContent($DivisionCourseId))
                )
                . new Well((new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(new Panel(
                                'Fach',
                                (new SelectBox('Data[Subject]', '', array('{{ Acronym }}-{{ Name }}' => $tblSubjectList)))
                                    ->setRequired()
                                    ->ajaxPipelineOnChange(ApiStudentSubject::pipelineLoadCheckSubjectsContent($DivisionCourseId)),
                                Panel::PANEL_TYPE_INFO
                            ), 12),
                        )),
                        new FormRow(new FormColumn(
                            ApiStudentSubject::receiverBlock($SubjectId
                                ? $this->loadCheckSubjectsContent($DivisionCourseId, $Data)
                                : new Warning('Bitte wählen Sie zunächst ein Fach aus.'), 'CheckSubjectsContent')
                        ))
                    )),
                )))->disableSubmitAction());
        }

        return new Danger('Kurs oder Schuljahr nicht gefunden', new Exclamation());
    }

    /**
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return string
     */
    public function loadCheckSubjectsContent($DivisionCourseId, $Data): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $dataList = array();
            $toggleList = array();
            $subjectTableFixedList = array();
            $subjectTableVariableList = array();
            $tblStudentList = DivisionCourse::useService()->getStudentListBy($tblDivisionCourse);

            $hasGrading = true;
            if (isset($Data['Subject']) && ($tblSubject = Subject::useService()->getSubjectById($Data['Subject']))) {
                $global = $this->getGlobal();
                $global->POST['Data']['StudentList'] = null;
                if (($tblStudentList)) {
                    foreach ($tblStudentList as $tblPerson) {
                        // SekII überspringen
                        if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                            continue;
                        }

                        $isPost = false;
                        // festes individuelles Fach am Schüler gespeichert
                        if (($tblStudentSubject = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                            $isPost = true;
                            if ($hasGrading) {
                                $hasGrading = $tblStudentSubject->getHasGrading();
                            }
                        // Stundentafel
                        } elseif (($tblSubjectTable = DivisionCourse::useService()->getSubjectTableByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                            if ($tblSubjectTable->getIsFixed()) {
                                $isPost = true;
                                if ($hasGrading) {
                                    $hasGrading = $tblSubjectTable->getHasGrading();
                                }
                                $subjectTableFixedList[$tblPerson->getId()] = $tblSubjectTable;
                            } elseif ($tblSubjectTable->getStudentMetaIdentifier()
                                && ($tblSubjectTemp = DivisionCourse::useService()->getSubjectFromStudentMetaIdentifier($tblSubjectTable, $tblPerson))
                                && $tblSubjectTemp->getId() == $tblSubject->getId()
                            ) {
                                $isPost = true;
                                if ($hasGrading) {
                                    $hasGrading = $tblSubjectTable->getHasGrading();
                                }
                                $subjectTableVariableList[$tblPerson->getId()] = $tblSubjectTable;
                            }
                        // reines Schülerakten-Fach z.B. 2.FS
                        } elseif (($virtualSubjectListFromStudentMeta = DivisionCourse::useService()->getVirtualSubjectListFromStudentMetaIdentifierListByPersonAndYear($tblPerson, $tblYear))
                            && (isset($virtualSubjectListFromStudentMeta[$tblSubject->getId()]))
                            && ($virtualSubject = $virtualSubjectListFromStudentMeta[$tblSubject->getId()])
                        ) {
                            $isPost = true;
                            if ($hasGrading) {
                                $hasGrading = $virtualSubject->getHasGrading();
                            }
                            $subjectTableVariableList[$tblPerson->getId()] = $virtualSubject->getTblSubjectTable();
                        }

                        if ($isPost) {
                            $global->POST['Data']['StudentList'][$tblPerson->getId()] = 1;
                        }
                    }
                }

                $global->POST['Data']['HasGrading'] = $hasGrading ? 1 : 0;
                $global->savePost();
            } else {
                return new Warning('Bitte wählen Sie zunächst ein Fach aus.');
            }

            if (($tblStudentList)) {
                foreach ($tblStudentList as $tblPerson) {
                    // SekII überspringen
                    if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                        continue;
                    }

                    $name = 'Data[StudentList][' . $tblPerson->getId() . ']';
                    $text = $tblPerson->getLastFirstName()
                        . (isset($subjectTableFixedList[$tblPerson->getId()]) ? ' (Stundentafel)' : '')
                        . (isset($subjectTableVariableList[$tblPerson->getId()]) ? ' (Schülerakte)' : '');
                    $checkBox = new CheckBox($name, $text, 1);
                    if (isset($subjectTableFixedList[$tblPerson->getId()])) {
                        $checkBox->setDisabled();
                    } else {
                        $toggleList[$tblPerson->getId()] = $name;
                    }

                    $dataList[$tblPerson->getId()] = $checkBox;
                }
            }

            if ($dataList) {
                $panel = new Panel('Schüler', $dataList, Panel::PANEL_TYPE_INFO);
                return new Panel(
                        'Benotung',
                        new CheckBox('Data[HasGrading]', 'Benotung', 1),
                        Panel::PANEL_TYPE_INFO
                    )
                    . new ToggleSelective( 'Alle wählen/abwählen', $toggleList)
                    . new Container('&nbsp;')
                    . $panel
                    . (empty($subjectTableVariableList) ? '' : new Warning('Wenn Sie die Fächer-Zuordnung von Schülern mit Verknüpfung zur Schülerakte speichern, haben
                        entsprechende Änderungen in der Schülerakte keinen Einfluss mehr auf dieses Fach.', new Exclamation()))
                    . (new Primary('Speichern', ApiStudentSubject::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineSaveStudentSubjectList($DivisionCourseId))
                    . (new Primary('Abbrechen', ApiStudentSubject::getEndpoint(), new Disable()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineLoadStudentSubjectContent($tblDivisionCourse->getId()));
            } else {
                return new Warning('Keine Schüler gefunden', new Exclamation());
            }
        }

        return new Danger('Kurs oder Schuljahr nicht gefunden', new Exclamation());
    }

    /**
     * @param array $studentSekIList
     * @param array $subjectList
     * @param bool $hasSubDivisionCourse
     * @param bool $hasMissingSubjects
     *
     * @return array[]
     */
    private function setSekIHeader(array $studentSekIList, array $subjectList,bool $hasSubDivisionCourse, bool $hasMissingSubjects ): array
    {
        $backgroundColor = '#E0F0FF';
        $headerColumnList[] = $this->getTableHeaderColumn('#', $backgroundColor);
        $headerColumnList[] = $this->getTableHeaderColumn('Schüler', $backgroundColor);
        if ($hasSubDivisionCourse) {
            $headerColumnList[] = $this->getTableHeaderColumn('Kurs', $backgroundColor);
        }
        if ($hasMissingSubjects) {
            $headerColumnList[] = $this->getTableHeaderColumn('Fehlende Fächer / Schülerakte', $backgroundColor);
        }
        foreach ($subjectList as $acronym) {
            $headerColumnList[] = $this->getTableHeaderColumn($acronym, $backgroundColor);
        }

        // reihenfolge inhalt und leere auffüllen
        $contentList = array();
        foreach ($studentSekIList as $student) {
            $item = array();
            $item['Number'] = $student['Number'];
            $item['FullName'] = $student['FullName'];
            if ($hasSubDivisionCourse) {
                $item['DivisionCourse'] = $student['DivisionCourse'];
            }

            if ($hasMissingSubjects) {
                if (isset($student['Missing'])) {
                    $item['Missing'] = new \SPHERE\Common\Frontend\Text\Repository\Warning(implode(', ', $student['Missing']));
                } else {
                    $item['Missing'] = '&nbsp;';
                }
            }

            foreach ($subjectList as $subjectId => $value) {
                if (isset($student[$subjectId])) {
                    $item[$subjectId] = $student[$subjectId];
                } else {
                    $item[$subjectId] = '&nbsp;';
                }
            }

            $contentList[] = $item;
        }
        return array($headerColumnList, $contentList);
    }

    /**
     * @param array $studentSekIIList
     * @param array $divisionCourseSekIIList
     * @param bool $hasSubDivisionCourse
     *
     * @return array[]
     */
    private function setSekIIHeader(array $studentSekIIList, array $divisionCourseSekIIList, bool $hasSubDivisionCourse): array
    {
        $backgroundColor = '#E0F0FF';
        for ($i = 1; $i <= 2; $i++) {
            $headerColumnList[$i][] = $this->getTableHeaderColumn('#', $backgroundColor);
            $headerColumnList[$i][] = $this->getTableHeaderColumn('Schüler', $backgroundColor);
            if ($hasSubDivisionCourse) {
                $headerColumnList[$i][] = $this->getTableHeaderColumn('Kurs', $backgroundColor);
            }
            if (isset($divisionCourseSekIIList[$i])) {
                if (isset($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_ADVANCED_COURSE])) {
                    asort($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_ADVANCED_COURSE]);
                    foreach ($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_ADVANCED_COURSE] as $value) {
                        $headerColumnList[$i][] = $this->getTableHeaderColumn($value, $backgroundColor);
                    }
                }
                if (isset($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_BASIC_COURSE])) {
                    asort($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_BASIC_COURSE]);
                    foreach ($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_BASIC_COURSE] as $value) {
                        $headerColumnList[$i][] = $this->getTableHeaderColumn($value, $backgroundColor);
                    }
                }
            }

            // reihenfolge inhalt und leere auffüllen
            $contentList[$i] = array();
            foreach ($studentSekIIList as $student) {
                $item = array();
                $item['Number'] = $student['Number'];
                $item['FullName'] = $student['FullName'];
                if ($hasSubDivisionCourse) {
                    $item['DivisionCourse'] = $student['DivisionCourse'];
                }

                if (isset($divisionCourseSekIIList[$i])) {
                    if (isset($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_ADVANCED_COURSE])) {
                        foreach ($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_ADVANCED_COURSE] as $divisionCourseId => $value) {
                            if (isset($student[$i][$divisionCourseId])) {
                                $item[$divisionCourseId] = $student[$i][$divisionCourseId];
                            } else {
                                $item[$divisionCourseId] = '&nbsp;';
                            }
                        }
                    }
                    if (isset($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_BASIC_COURSE])) {
                        foreach ($divisionCourseSekIIList[$i][TblDivisionCourseType::TYPE_BASIC_COURSE] as $divisionCourseId => $value) {
                            if (isset($student[$i][$divisionCourseId])) {
                                $item[$divisionCourseId] = $student[$i][$divisionCourseId];
                            } else {
                                $item[$divisionCourseId] = '&nbsp;';
                            }
                        }
                    }
                }

                $contentList[$i][] = $item;
            }
        }


        return array($headerColumnList, $contentList);
    }

    /**
     * @param $DivisionCourseId
     * @param TblDivisionCourse $tblDivisionCourseItem
     * @param TblYear $tblYear
     * @param TblPerson $tblPerson
     * @param int $count
     * @param array $subjectList
     * @param array $subjectTableList
     * @param bool $hasSubDivisionCourse
     * @param bool $hasMissingSubjects
     *
     * @return array
     */
    private function getStudentSekIData($DivisionCourseId, TblDivisionCourse $tblDivisionCourseItem, TblYear $tblYear, TblPerson $tblPerson, int &$count,
        array &$subjectList, array &$subjectTableList, bool $hasSubDivisionCourse, bool &$hasMissingSubjects): array
    {
        $item = array();
        $item['Number'] = ++$count;
        $item['FullName'] = $tblPerson->getLastFirstName();
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
                    // feste Fächer der Stundentafel
                    if (($tblSubjectTable->getIsFixed())
                        && ($tblSubject = $tblSubjectTable->getServiceTblSubject())
                    ) {
                        if ($addHeader && !isset($subjectList[$tblSubject->getId()])) {
                            $subjectList[$tblSubject->getId()] = new PullClear($tblSubject->getAcronym()
                                . (new PullRight((new Link('', ApiStudentSubject::getEndpoint(), new Pen))
                                    ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectContent($DivisionCourseId, $tblSubject->getId()))
                                )));
                        }
                        $item[$tblSubject->getId()] = new ToolTip(new Muted('ST'), 'Stundentafel')
                            . (!$tblSubjectTable->getHasGrading() ? ' ' . new ToolTip(new Muted(new Ban()), 'Keine Benotung') : '');
                        // variable Fächer der Stundentafel und Schülerakte
                    } else {
                        $tblSubject = false;
                        // Fach aus der Schülerakte
                        if ($tblSubjectTable->getStudentMetaIdentifier()) {
                            // gespeichertes Fach am Schüler mit Verknüpfung zur Schülerakte
                            if (($tblStudentSubjectTemp = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubjectTable(
                                    $tblPerson, $tblYear, $tblSubjectTable))
                                && ($tblSubject = $tblStudentSubjectTemp->getServiceTblSubject())
                            ) {
                                $item[$tblSubject->getId()] = new Check()
                                    . (!$tblStudentSubjectTemp->getHasGrading() ? ' ' . new ToolTip(new Muted(new Ban()), 'Keine Benotung') : '');
                                // nicht gespeichertes Fach aus der Schülerakte
                            } elseif (($tblSubject = DivisionCourse::useService()->getSubjectFromStudentMetaIdentifier($tblSubjectTable, $tblPerson))) {
                                $item[$tblSubject->getId()] = new ToolTip(new Muted('ST-SA'), 'Stundentafel-Schülerakte')
                                    . (!$tblSubjectTable->getHasGrading() ? ' ' . new ToolTip(new Muted(new Ban()), 'Keine Benotung') : '');
                            }
                            // Fach nicht aus der Schülerakte, aber individuell am Schüler
                        } elseif (($tblSubjectFromSubjectTable = $tblSubjectTable->getServiceTblSubject())) {
                            if (DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubject(
                                $tblPerson, $tblYear, $tblSubjectFromSubjectTable
                            )) {
                                $tblSubject = $tblSubjectFromSubjectTable;
                                $item[$tblSubject->getId()] = new Check()
                                    . (!$tblSubjectTable->getHasGrading() ? ' ' . new ToolTip(new Muted(new Ban()), 'Keine Benotung') : '');
                            }
                        }

                        $missingDisplayName = $tblSubjectTable->getStudentMetaIdentifier()
                            ? new ToolTip($tblSubjectTable->getSubjectAcronym(), 'Schülerakte - ' . $tblSubjectTable->getStudentMetaDisplayName())
                            : $tblSubjectTable->getSubjectAcronym();
                        if (($tblSubjectTableLink = DivisionCourse::useService()->getSubjectTableLinkBySubjectTable($tblSubjectTable))) {
                            if (!isset($item['MissingLinkedSubjects'][$tblSubjectTableLink->getLinkId()])) {
                                $item['MissingLinkedSubjects'][$tblSubjectTableLink->getLinkId()]['MinCount'] = $tblSubjectTableLink->getMinCount();
                                $item['MissingLinkedSubjects'][$tblSubjectTableLink->getLinkId()]['Count'] = 0;
                            }
                            if (!$tblSubject) {
                                $item['MissingLinkedSubjects'][$tblSubjectTableLink->getLinkId()]['SubjectTableList'][$tblSubjectTable->getId()]
                                    = $missingDisplayName;
                            } else {
                                $item['MissingLinkedSubjects'][$tblSubjectTableLink->getLinkId()]['Count']++;
                            }

                        } else {
                            if (!$tblSubject) {
                                $hasMissingSubjects = true;
                                $item['Missing'][] = $missingDisplayName;
                            }
                        }

                        // Tabellen-Kopf
                        if ($tblSubject) {
                            if (!isset($subjectList[$tblSubject->getId()])) {
                                $subjectList[$tblSubject->getId()] = new PullClear($tblSubject->getAcronym()
                                    . (new PullRight((new Link('', ApiStudentSubject::getEndpoint(), new Pen))
                                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectContent($DivisionCourseId, $tblSubject->getId()))
                                    )));
                            }
                        }
                    }
                }
            }

            /*
             * gespeicherte Fächer am Schüler
             */
            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear))) {
                // Sortierung der Fächer
                $tblStudentSubjectList = $this->getSorter($tblStudentSubjectList)->sortObjectBy('Sort');
                /** @var TblStudentSubject $tblStudentSubject */
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubjectItem = $tblStudentSubject->getServiceTblSubject())) {
//                        if (!isset($item[$tblSubjectItem->getId()])) {
                            $item[$tblSubjectItem->getId()] = new Check()
                                . (!$tblStudentSubject->getHasGrading() ? ' ' . new ToolTip(new Muted(new Ban()), 'Keine Benotung') : '');
//                        }

                        if (!isset($subjectList[$tblSubjectItem->getId()])) {
                            $subjectList[$tblSubjectItem->getId()] = new PullClear($tblSubjectItem->getAcronym()
                                . (new PullRight((new Link('', ApiStudentSubject::getEndpoint(), new Pen))
                                    ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectContent($DivisionCourseId, $tblSubjectItem->getId()))
                                )));
                        }
                    }
                }
            }

            /*
             * Prüfung fehlende Fächer
             */
            if (isset($item['MissingLinkedSubjects'])) {
                foreach ($item['MissingLinkedSubjects'] as $missingItem) {
                    if ($missingItem['Count'] < $missingItem['MinCount']) {
                        $hasMissingSubjects = true;
                        $item['Missing'][] = implode(' | ', $missingItem['SubjectTableList']);
                    }
                }
            }
        }

        return $item;
    }

    /**
     * @param $DivisionCourseId
     * @param TblDivisionCourse $tblDivisionCourseItem
     * @param TblYear $tblYear
     * @param TblPerson $tblPerson
     * @param int $count
     * @param array $divisionCourseSekIIList
     * @param bool $hasSubDivisionCourse
     *
     * @return array
     */
    private function getStudentSekIIData($DivisionCourseId, TblDivisionCourse $tblDivisionCourseItem, TblYear $tblYear, TblPerson $tblPerson, int &$count,
        array &$divisionCourseSekIIList, bool $hasSubDivisionCourse): array
    {
        $item = array();
        $item['Number'] = ++$count;
        $item['FullName'] = $tblPerson->getLastFirstName();
        if ($hasSubDivisionCourse) {
            $item['DivisionCourse'] = $tblDivisionCourseItem->getName();
        }

        if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear))) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblDivisionCourseSekII = $tblStudentSubject->getTblDivisionCourse())) {
                    if (($list = explode('/', $tblStudentSubject->getPeriodIdentifier()))
                        && isset($list[1])
                        && ($period = $list[1])
                    ) {
                        $identifier = $tblDivisionCourseSekII->getTypeIdentifier();
                        $content = ($tblSubject = $tblDivisionCourseSekII->getServiceTblSubject()) ? $tblSubject->getAcronym() : new Check();
                        $item[$period][$tblDivisionCourseSekII->getId()] = $identifier == TblDivisionCourseType::TYPE_ADVANCED_COURSE ? new Bold($content) : $content;
                        if (!isset($divisionCourseSekIIList[$period][$identifier][$tblDivisionCourseSekII->getId()])) {
                            $divisionCourseSekIIList[$period][$identifier][$tblDivisionCourseSekII->getId()] =
                                new PullClear(
                                    $tblDivisionCourseSekII->getName()
                                    . (new PullRight((new Link('', ApiStudentSubject::getEndpoint(), new Pen))
                                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineEditStudentSubjectDivisionCourseContent(
                                            $DivisionCourseId, $period, $tblDivisionCourseSekII->getId()
                                        ))
                                    ))
                                );
                        }
                    }
                }
            }
        }

        return $item;
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     * @param $SubjectDivisionCourseId
     * @param null $Data
     *
     * @return string
     */
    public function editStudentSubjectDivisionCourseContent($DivisionCourseId, $Period, $SubjectDivisionCourseId, $Data = null): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            if ($SubjectDivisionCourseId) {
                $global = $this->getGlobal();
                $global->POST['Data']['SubjectDivisionCourse'] = $SubjectDivisionCourseId;
                $Data['SubjectDivisionCourse'] = $SubjectDivisionCourseId;
                $global->savePost();
            }

            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $divisionCourseSelectList = array();
            if (($tblDivisionCourseAdvancedList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_ADVANCED_COURSE))) {
                $divisionCourseSelectList = $tblDivisionCourseAdvancedList;
            }
            if (($tblDivisionCourseBasicList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_BASIC_COURSE))) {
                $divisionCourseSelectList = array_merge($divisionCourseSelectList, $tblDivisionCourseBasicList);
            }

            return
                new Title(new Education() . ' Fächer der Schüler in der ' . $text
                    . (new Link('Anzeigen', ApiStudentSubject::getEndpoint(), new EyeOpen()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineLoadStudentSubjectContent($DivisionCourseId))
                )
                . new Well((new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(new Panel(
                                'SekII-Kurs',
                                (new SelectBox('Data[SubjectDivisionCourse]', '', array('{{ Name }}' => $divisionCourseSelectList)))
                                    ->setRequired()
                                    ->ajaxPipelineOnChange(ApiStudentSubject::pipelineLoadCheckSubjectDivisionCoursesContent($DivisionCourseId, $Period)),
                                Panel::PANEL_TYPE_INFO
                            ), 6),
                            new FormColumn(new Panel(
                                'Halbjahr',
                                $Period . '. Halbjahr',
                                Panel::PANEL_TYPE_INFO
                            ), 6)
                        )),
                        new FormRow(new FormColumn(
                            ApiStudentSubject::receiverBlock($SubjectDivisionCourseId
                                ? $this->loadCheckSubjectDivisionCoursesContent($DivisionCourseId, $Period, $Data)
                                : new Warning('Bitte wählen Sie zunächst ein Fach aus.'), 'CheckSubjectDivisionCoursesContent')
                        ))
                    )),
                )))->disableSubmitAction());
        }

        return new Danger('Kurs oder Schuljahr nicht gefunden', new Exclamation());
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     * @param $Data
     *
     * @return string
     */
    public function loadCheckSubjectDivisionCoursesContent($DivisionCourseId, $Period, $Data): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $dataList = array();
            $toggleList = array();
            $tblStudentList = DivisionCourse::useService()->getStudentListBy($tblDivisionCourse);

            if (isset($Data['SubjectDivisionCourse']) && ($tblSubjectDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Data['SubjectDivisionCourse']))) {
                $global = $this->getGlobal();
                $global->POST['Data']['StudentList'] = null;
                if (($tblStudentList)) {
                    foreach ($tblStudentList as $tblPerson) {
                        // SekI überspringen
                        if (!DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                            continue;
                        }

                        if (DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndDivisionCourseAndPeriod($tblPerson, $tblYear, $tblSubjectDivisionCourse, $Period)) {
                            $global->POST['Data']['StudentList'][$tblPerson->getId()] = 1;
                        }
                    }
                }
                $global->savePost();
            } else {
                return new Warning('Bitte wählen Sie zunächst einen SekII-Kurs aus.');
            }

            if ($tblStudentList) {
                foreach ($tblStudentList as $tblPerson) {
                    // SekI überspringen
                    if (!DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                        continue;
                    }

                    $name = 'Data[StudentList][' . $tblPerson->getId() . ']';
                    $toggleList[$tblPerson->getId()] = $name;
                    $dataList[$tblPerson->getId()] = new CheckBox($name, $tblPerson->getLastFirstName(), 1);
                }
            }

            if ($dataList) {
                $panel = new Panel('Schüler', $dataList, Panel::PANEL_TYPE_INFO);
                return new ToggleSelective( 'Alle wählen/abwählen', $toggleList)
                    . new Container('&nbsp;')
                    . $panel
                    . (new Primary('Speichern', ApiStudentSubject::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineSaveStudentSubjectDivisionCourseList($DivisionCourseId, $Period))
                    . (new Primary('Abbrechen', ApiStudentSubject::getEndpoint(), new Disable()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineLoadStudentSubjectContent($tblDivisionCourse->getId()));
            } else {
                return new Warning('Keine Schüler gefunden', new Exclamation());
            }
        }

        return new Danger('Kurs oder Schuljahr nicht gefunden', new Exclamation());
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function copySubjectDivisionCourse($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);

            $tblStudentSubjectList = array();
            foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                if (($tempList = DivisionCourse::useService()->getStudentSubjectListByStudentDivisionCourseAndPeriod($tblDivisionCourseItem, 1))) {
                    foreach ($tempList as $tblStudentSubject) {
                        if (($tblPerson = $tblStudentSubject->getServiceTblPerson())
                            && ($tblSubjectDivisionCourse = $tblStudentSubject->getTblDivisionCourse())
                            && !DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndDivisionCourseAndPeriod($tblPerson, $tblYear, $tblSubjectDivisionCourse, 2)
                        ) {
                            $tblStudentSubjectList[] = $tblStudentSubject;
                        }
                    }
                }
            }

            $title = new Title(new MoreItems() . ' SekII-Kurse des 1.HJ der Schüler ins 2. HJ kopieren für ' . $text);

            if (empty($tblStudentSubjectList)) {
                return $title . new Warning('Es können keine weiteren SekII-Kurse ins 2. Halbjahr kopiert werden.', new Exclamation());
            } else {
                return $title . new Success('Es können ' . count($tblStudentSubjectList) . ' SekII-Kurse ins 2. Halbjahr kopiert werden.')
                    . (new SuccessLink('Ja', ApiStudentSubject::getEndpoint(), new Ok()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineCopySubjectDivisionCourseSave($DivisionCourseId))
                    . (new Standard('Nein', ApiStudentSubject::getEndpoint(), new Remove()))
                        ->ajaxPipelineOnClick(ApiStudentSubject::pipelineClose());
            }
        }

        return new Danger('Kurs oder Schuljahr nicht gefunden', new Exclamation());
    }
}