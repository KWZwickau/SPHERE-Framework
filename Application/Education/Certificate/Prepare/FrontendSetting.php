<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Api\Platform\ReloadReceiver\ApiReloadReceiver;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGrade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;

abstract class FrontendSetting extends FrontendSelect
{
    /**
     * @param $PrepareId
     * @param string $Route
     * @param $GradeTypeId
     * @param $IsNotGradeType
     * @param $Data
     * @param $CertificateList
     * @param $Page
     *
     * @return Stage|string
     */
    public function frontendPrepareSetting(
        $PrepareId = null,
        string $Route = 'Teacher',
        $GradeTypeId = null,
        $IsNotGradeType = null,
        $Data = null,
        $CertificateList = null,
        $Page = null
    ) {
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $tblTaskList = false;
            $useMultipleBehaviorTasks = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'UseMultipleBehaviorTasks'))
                && $tblSetting->getValue();
            $showProposalBehaviorGrade = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Graduation', 'Evaluation', 'ShowProposalBehaviorGrade'))
                && $tblSetting->getValue();
            $useClassRegisterForAbsence = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
                && $tblSetting->getValue();

            // Kopfnoten festlegen
            if (!$IsNotGradeType
                && (($useMultipleBehaviorTasks && ($tblTaskList = Grade::useService()->getBehaviorTaskListByDivisionCourse($tblDivisionCourse)))
                    || $tblPrepare->getServiceTblBehaviorTask()
                )
            ) {
                return $this->getBehaviorGradesStage($tblPrepare, $tblDivisionCourse, $Route, $useClassRegisterForAbsence, $Data,
                    $GradeTypeId, $tblTaskList, $showProposalBehaviorGrade);
            // Sonstige Informationen
            } else {
                return $this->getInformationStage($tblPrepare, $tblDivisionCourse, $Route, $CertificateList, $useClassRegisterForAbsence, $Data, $Page);
            }
        }

        return (new Stage('Zeugnisvorbereitung'))
            . new Danger('Die Zeugnisvorbereitung wurde nicht gefunden', new Exclamation());
    }

    private function getBehaviorGradesStage(TblPrepareCertificate $tblPrepare, TblDivisionCourse $tblDivisionCourse, string $Route, $useClassRegisterForAbsence, $Data,
        $GradeTypeId, $tblTaskList, $showProposalBehaviorGrade): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Kopfnoten festlegen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
            array(
                'DivisionId' => $tblDivisionCourse->getId(),
                'Route' => $Route
            )
        ));

        if ($tblTaskList) {
            $tblTask = current($tblTaskList);
        } else {
            $tblTask = $tblPrepare->getServiceTblBehaviorTask();
        }

        if ($tblTask
            && ($tblGradeTypeList = Grade::useService()->getGradeTypeListByTask($tblTask))
        ) {
            // Ermittelung aktueller Zensuren-Typ und nächster Zensuren-Typ
            /** @var ?TblGradeType $tblCurrentGradeType */
            $tblCurrentGradeType = null;
            $tblNextGradeType = null;
            $buttonList = $this->getBehaviorButtonList($tblPrepare, $Route, $useClassRegisterForAbsence, $tblGradeTypeList,
                $tblCurrentGradeType, $tblNextGradeType, $GradeTypeId);

            $studentTable = array();
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungsgang',
                'Grades' => 'Einzelnoten in ' . ($tblCurrentGradeType ? $tblCurrentGradeType->getName() : ''),
                'Average' => '&#216;',
                'Data' => 'Zensur'
            );

            $selectListWithTrend[-1] = '';
            for ($i = 1; $i < 5; $i++) {
                $selectListWithTrend[$i . '+'] = ($i . '+');
                $selectListWithTrend[$i] = (string)$i;
                $selectListWithTrend[$i . '-'] = ($i . '-');
            }
            $selectListWithTrend[5] = "5";

            $selectListWithOutTrend[-1] = '';
            for ($i = 1; $i < 5; $i++) {
                $selectListWithOutTrend[$i] = (string)$i;
            }
            $selectListWithOutTrend[5] = "5";
            $tabIndex = 1;
            $hasPreviewGrades = false;

            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                $countPerson = 0;
                foreach ($tblPersonList as $tblPerson) {
                    $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                    $studentTable[$tblPerson->getId()] = $this->getStudentBasicInformation($tblPerson, $tblYear, $tblPrepareStudent ?: null, $countPerson);

                    if ($tblCurrentGradeType) {
                        $subjectGradeList = array();
                        $gradeList = array();
                        $gradeListString = '';
                        $averageStudent = false;
                        // mehrere Kopfnotenaufträge
                        if ($tblTaskList) {
                            foreach ($tblTaskList as $tblTaskItem) {
                                if (($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTaskItem, $tblPerson))) {
                                    foreach ($tblTaskGradeList as $tblTaskGrade) {
                                        if (($tblGradeType = $tblTaskGrade->getTblGradeType())
                                            && $tblGradeType->getId() == $tblCurrentGradeType->getId()
                                            && ($tblSubject = $tblTaskGrade->getServiceTblSubject())
                                        ) {
                                            $subjectGradeList[$tblTaskItem->getId()][$tblSubject->getAcronym()] = $tblTaskGrade;
                                        }
                                    }
                                }
                            }

                            $averageList = array();
                            // Zusammensetzen (für Anzeige) der vergebenen mehrfachen Kopfnoten
                            /** @var TblTaskGrade $grade */
                            foreach ($subjectGradeList as $taskId => $subjectTaskGradeList) {
                                $subString = '';
                                if (($tblTaskItem = Grade::useService()->getTaskById($taskId))) {
                                    ksort($subjectTaskGradeList);
                                    foreach ($subjectTaskGradeList as $subjectAcronym => $grade) {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym);
                                        if ($tblSubject) {
                                            if ($grade->getGrade() && $grade->getIsGradeNumeric()) {
                                                $gradeList[$taskId][] = $grade->getGradeNumberValue();
                                            }

                                            if (empty($subString)) {
                                                $subString =
                                                    $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                            } else {
                                                $subString .= ' | '
                                                    . $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                            }
                                        }
                                    }
                                    // Kopfnotenvorschlag
                                    if ($showProposalBehaviorGrade) {
                                        if (($tblProposalBehaviorGrade = Grade::useService()->getProposalBehaviorGradeByPersonAndTaskAndGradeType(
                                                $tblPerson, $tblTaskItem, $tblCurrentGradeType
                                            ))
                                            && ($proposalGrade = $tblProposalBehaviorGrade->getGrade())
                                        ) {
                                            $subString .= ' | (KL-Vorschlag:' . $proposalGrade . ')';
                                        }
                                    }
                                    if (!empty($subString) && isset($gradeList[$taskId])) {
                                        $count = count($gradeList[$taskId]);
                                        $average = $count > 0 ? round(array_sum($gradeList[$taskId]) / $count, 2) : '';
                                        if ($average) {
                                            $averageList[$taskId] = $average;
                                            $average = number_format($average, 2, ',', '.');
                                        }
                                        $gradeListString .= $tblTaskItem->getDateString() . '&nbsp;&nbsp;&nbsp;'
                                            . new Bold('&#216;' . $average) . '&nbsp;&nbsp;&nbsp;' . $subString
                                            . '<br>';
                                    }
                                }
                            }
                            $countAverages = count($averageList);
                            $average = $countAverages > 0 ? round(array_sum($averageList) / $countAverages, 2) : '';
                            $studentTable[$tblPerson->getId()]['Average'] = $average;
                            $averageStudent = $average;
                            $studentTable[$tblPerson->getId()]['Grades'] = $gradeListString;
                        // einzelner Kopfnotenauftrag
                        } else {
                            if (($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))) {
                                foreach ($tblTaskGradeList as $tblTaskGrade) {
                                    if (($tblGradeType = $tblTaskGrade->getTblGradeType())
                                        && $tblGradeType->getId() == $tblCurrentGradeType->getId()
                                        && ($tblSubject = $tblTaskGrade->getServiceTblSubject())
                                    ) {
                                        $subjectGradeList[$tblSubject->getAcronym()] = $tblTaskGrade;
                                    }
                                }
                            }

                            if (!empty($subjectGradeList)) {
                                ksort($subjectGradeList);
                            }

                            // Zusammensetzen (für Anzeige) der vergebenen Kopfnoten
                            /** @var TblTaskGrade $grade */
                            foreach ($subjectGradeList as $subjectAcronym => $grade) {
                                $tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym);
                                if ($tblSubject) {
                                    if ($grade->getGrade() && $grade->getIsGradeNumeric()) {
                                        $gradeList[] = $grade->getGradeNumberValue();
                                    }
                                    if (empty($gradeListString)) {
                                        $gradeListString = $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                    } else {
                                        $gradeListString .= ' | ' . $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                    }
                                }
                            }
                            // Kopfnotenvorschlag
                            if ($showProposalBehaviorGrade) {
                                if (($tblProposalBehaviorGrade = Grade::useService()->getProposalBehaviorGradeByPersonAndTaskAndGradeType(
                                        $tblPerson, $tblTask, $tblCurrentGradeType
                                    ))
                                    && ($proposalGrade = $tblProposalBehaviorGrade->getGrade())
                                ) {
                                    $gradeListString .= ' | (KL-Vorschlag:' . $proposalGrade . ')';
                                }
                            }
                            $studentTable[$tblPerson->getId()]['Grades'] = $gradeListString;

                            // calc average
                            if (!empty($gradeList)) {
                                $count = count($gradeList);
                                $average = $count > 0 ? round(array_sum($gradeList) / $count, 2) : '';
                                $studentTable[$tblPerson->getId()]['Average'] = $average;
                                if ($average) {
                                    $averageStudent = $average;
                                }
                            } else {
                                $studentTable[$tblPerson->getId()]['Average'] = '';
                            }
                        }

                        // Post setzen
                        $isGradeProposal = false;
                        if ($Data === null
                            && $tblPrepareStudent
                        ) {
                            $Global = $this->getGlobal();
                            $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                                $tblPrepare, $tblPerson, $tblCurrentGradeType
                            );
                            if ($tblPrepareGrade) {
                                $gradeValue = $tblPrepareGrade->getGrade();
                                $Global->POST['Data'][$tblPrepareStudent->getId()] = $gradeValue;
                            } elseif ($averageStudent && !$tblPrepareStudent->isApproved()) {
                                // Noten aus dem Notendurchschnitt als Vorschlag eintragen
                                if ($tblPrepareStudent->getServiceTblCertificate()) {
                                    $hasPreviewGrades = true;
                                }
                                $isGradeProposal = true;
                                $Global->POST['Data'][$tblPrepareStudent->getId()] = round($averageStudent);
                            }

                            $Global->savePost();
                        }

                        if ($tblPrepareStudent
                            && $tblPrepareStudent->getServiceTblCertificate()
                        ) {
                            if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                && $tblCertificate->isInformation()
                            ) {
                                $selectList = $selectListWithTrend;
                            } else {
                                $selectList = $selectListWithOutTrend;
                            }
                            $selectComplete = (new SelectCompleter('Data[' . $tblPrepareStudent->getId() . ']', '', '', $selectList))->setTabIndex($tabIndex++);
                            if ($tblPrepareStudent->isApproved()) {
                                $selectComplete->setDisabled();
                            }
                            if ($isGradeProposal) {
                                $selectComplete->setPrefixValue('Notenvorschlag');
                            }

                            $studentTable[$tblPerson->getId()]['Data'] = $selectComplete;
                        } else {
                            // keine Zeugnisvorlage ausgewählt
                            $studentTable[$tblPerson->getId()]['Data'] = '';
                        }
                    }
                }

                $columnDef = array(
                    array(
                        "width" => "18px",
                        "targets" => 0
                    ),
                    array(
                        "width" => "180px",
                        "targets" => 1
                    ),
                    array(
                        "width" => "80px",
                        "targets" => 2
                    ),
                    array(
                        "width" => "50px",
                        "targets" => array(4)
                    ),
                    array(
                        "width" => "150px",
                        "targets" => array(5)
                    ),
                );

                $tableData = new TableData($studentTable, null, $columnTable,
                    array(
                        "columnDefs" => $columnDef,
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        "paging" => false, // Deaktivieren Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                        "searching" => false, // Deaktivieren Suchen
                        "info" => false,  // Deaktivieren Such-Info
                        "sort" => false,
                        "responsive" => false
                    )
                );

                $form = new Form(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                $tableData
                            ),
                            new FormColumn(new HiddenField('Data[IsSubmit]'))
                        )),
                    ))
                    , new Primary('Speichern', new Save())
                );

                $Stage->setContent(
                    ApiReloadReceiver::receiverReload(ApiReloadReceiver::pipelineReload())
                    .new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnis',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn($buttonList),
                                $hasPreviewGrades
                                    ? new LayoutColumn(new Warning(
                                    'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                                ))
                                    : null,
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    Prepare::useService()->updatePrepareBehaviorGrades(
                                        $form,
                                        $tblPrepare,
                                        $tblCurrentGradeType,
                                        $tblNextGradeType ?: null,
                                        $Route,
                                        $Data
                                    )
                                ))
                            ))
                        ))
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblPrepareStudent|null $tblPrepareStudent
     * @param int $count
     * @param bool $hasEditToolTip
     *
     * @return array
     */
    public function getStudentBasicInformation(TblPerson $tblPerson, TblYear $tblYear, ?TblPrepareStudent $tblPrepareStudent, int &$count, bool $hasEditToolTip = true): array
    {
        $temp = '';
        $isMuted = !($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate());
        if ($hasEditToolTip) {
            $temp = ' '
                . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                    ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                        'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                    : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.'));
        }
        $data = array(
            'Number' => $isMuted
                ? new Muted(++$count) . ' ' . new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Danger(new Remove()),
                    'Für den Schüler wurde keine Zeugnisvorlage hinterlegt, es können keine Daten eingegeben werden.
                    Falls für den Schüler trotzdem ein Zeugnis erstellt werden soll, wenden Sie sich bitte an Ihre Schulleitung, diese kann
                    die Zeugnisvorlage am entsprechenden Zeugnisauftrag hinterlegen.
                    ')
                : ++$count . $temp,
            'Name' => $isMuted ? new Muted($tblPerson->getLastFirstNameWithCallNameUnderline()) : $tblPerson->getLastFirstNameWithCallNameUnderline()
        );

        // Bildungsgang
        $courseName = '';
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
            $tblCourse = $tblStudentEducation->getServiceTblCourse();
            // berufsbildende Schulart
            if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
            } else {
                $courseName = $tblCourse ? $tblCourse->getName() : '';
            }
        }
        $data['Course'] = $isMuted ? new Muted($courseName) : $courseName;
        // Integration ReadOnlyButton
        if(Student::useService()->getIsSupportByPerson($tblPerson)) {
            $data['IntegrationButton'] = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
        } else {
            $data['IntegrationButton'] = '';
        }

        return $data;
    }

    private function getBehaviorButtonList(TblPrepareCertificate $tblPrepare, string $Route, bool $useClassRegisterForAbsence, array $tblGradeTypeList,
        ?TblGradeType &$tblCurrentGradeType, ?TblGradeType &$tblNextGradeType, $GradeTypeId): array
    {
        foreach ($tblGradeTypeList as $tblGradeTypeItem) {
            if ($tblCurrentGradeType && !$tblNextGradeType) {
                $tblNextGradeType = $tblGradeTypeItem;
            }
            if ($GradeTypeId && $GradeTypeId == $tblGradeTypeItem->getId()) {
                $tblCurrentGradeType = $tblGradeTypeItem;
            }
        }
        if (!$tblCurrentGradeType) {
            $tblCurrentGradeType = current($tblGradeTypeList);
            if (count($tblGradeTypeList) > 1) {
                $tblNextGradeType = next($tblGradeTypeList);
            }
        }

        // Tabs für Zensuren-Typen
        $buttonList = array();
        /** @var TblGradeType $tblGradeType */
        foreach ($tblGradeTypeList as $tblGradeType) {
            if ($tblCurrentGradeType->getId() == $tblGradeType->getId()) {
                $name = new Info(new Bold($tblGradeType->getName()));
                $icon = new Edit();
            } else {
                $name = $tblGradeType->getName();
                $icon = null;
            }

            $buttonList[] = new Standard($name,
                '/Education/Certificate/Prepare/Prepare/Setting', $icon, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route,
                    'GradeTypeId' => $tblGradeType->getId()
                )
            );
        }

        // Erstellt zusätzliche "Tabs" für weitere Sonstige Informationen und die Fehlzeiten
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($informationPageList, $pageList, $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList)
            = Prepare::useService()->getCertificateInformationPages($tblPrepare, $useClassRegisterForAbsence);

        $buttonList[] = new Standard('Sonstige Informationen',
            '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                'PrepareId' => $tblPrepare->getId(),
                'Route' => $Route,
                'IsNotGradeType' => true
            )
        );
        foreach ($pageList as $item) {
            if ($item == 'Absence') {
                $text = 'Fehlzeiten';
            } else {
                $text = 'Sonstige Informationen (Seite ' . $item . ')';
            }

            $buttonList[] = new Standard($text,
                '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route,
                    'IsNotGradeType' => true,
                    'Page' => $item
                )
            );
        }

        return $buttonList;
    }

    private function getInformationStage(TblPrepareCertificate $tblPrepare, TblDivisionCourse $tblDivisionCourse, string $Route, $CertificateList,
        $useClassRegisterForAbsence, $Data, $Page): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Sonstige Informationen festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
            array(
                'DivisionId' => $tblDivisionCourse->getId(),
                'Route' => $Route
            )
        ));

        $tblGradeTypeList = false;
        if (($tblTask = $tblPrepare->getServiceTblBehaviorTask())) {
            $tblGradeTypeList = Grade::useService()->getGradeTypeListByTask($tblTask);
        }
        $buttonList = $this->getInformationButtonList($tblPrepare, $Route, $useClassRegisterForAbsence, $tblGradeTypeList ?: array(), $Page, $nextPage,
            $informationPageList, $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList);

        if ($Page == 'Absence') {
            $this->getAbsenceContent($tblPrepare, $Route, $CertificateList, $useClassRegisterForAbsence, $Stage, $Data, $buttonList, $nextPage,
                $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList);
        } else {
            $this->getInformationContent($tblPrepare, $Route, $CertificateList, $Stage, $Data, $buttonList, $nextPage, $Page, $informationPageList);
        }

        return $Stage;
    }

    private function getAbsenceContent(TblPrepareCertificate $tblPrepare, string $Route, $CertificateList, bool $useClassRegisterForAbsence, Stage $Stage,
        $Data, array $buttonList, $nextPage, $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList)
    {
        $Stage->setDescription('Fehlzeiten festlegen');
        if ($useClassRegisterForAbsence) {
            $Stage->setMessage(
                new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold('Hinweis: ')
                    . new Container('Die Fehlzeiten werden im Klassenbuch erfasst. Hier können für fehlende
                                    Unterrichtseinheiten zusätzliche Tage eingeben werden. Die Fehlzeiten auf dem Zeugnis
                                    ergeben sich dann aus den <b>"Fehltagen im Klassenbuch"</b> + <b>"die hier eingebenen
                                    Zusatz-Tage für fehlende Unterrichtseinheiten"</b>.')
                    . new Container('Es können nur Zusatz-Tage für Unterrichtseinheiten bei einem Schüler
                                    erfasst werden, wenn der Schüler eine Zeugnisvorlage mit Fehlzeiten besitzt und im
                                    Klassenbuch auch entsprechende fehlende Unterrichtseinheiten erfasst wurden.')
                    . new Container('&nbsp;')
                    . new Container(new Bold(new Exclamation() . ' Bitte speichern Sie die Fehlzeiten erst,
                                    wenn alle Fehlzeiten im Klassenbuch erfasst wurden. Sie können diese Seite mit
                                    "Ohne Speichern weiter" überspringen.'))
                )
            );
        } else {
            $Stage->setMessage(
                new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold('Hinweis: ')
                    . new Container('Die Fehlzeiten für die Zeugnisse werden hier erfasst. Bitte tragen
                                    Sie hier die Fehlzeiten-Tage für das Zeugnis ein. Wurden schon Fehltage im Klassenbuch
                                    erfasst, werden diese unter <b>"Ganze Tage"</b> angezeigt. Wurden schon fehlende
                                    Unterrichtseinheiten im Klassenbuch erfasst, werden diese unter <b>"UE"</b> angezeigt.')
                    . new Container('Es können nur Fehltage für das Zeugnis erfasst werden, wenn der Schüler
                                    eine Zeugnisvorlage mit Fehlzeiten besitzt.')
                )
            );
        }

        $isAbsenceHour = false;
        $tblPersonList = false;
        $tblYear = false;
        if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())) {
            $tblYear = $tblDivisionCourse->getServiceTblYear();
            $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses();
        }
        // Auf dem BFS Jahreszeugnis für Pflege sind jetzt auch die Fehlzeiten in Tagen und nicht mehr in Stunden
        if ($tblDivisionCourse
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                ) {
                    if ($tblCertificate->getName() == 'Berufsfachschule Jahreszeugnis' && $tblCertificate->getDescription() == 'Generalistik') {
                        $isAbsenceHour = true;
                        break;
                    }
                }
            }
        }

        $headTableColumnList[] = new TableColumn('Schüler', 4);
        if($isAbsenceHour){
            // Zusatzspalte für Stunden
            $headTableColumnList[] = new TableColumn('Entschuldigte Fehlzeiten', 4);
            $headTableColumnList[] = new TableColumn('Unentschuldigte Fehlzeiten', 4);
        } else {
            // Standard
            $headTableColumnList[] = new TableColumn('Entschuldigte Fehlzeiten', 3);
            $headTableColumnList[] = new TableColumn('Unentschuldigte Fehlzeiten', 3);
        }

        if ($useClassRegisterForAbsence) {
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'IntegrationButton' => 'Inklusion',
                'Course' => 'Bildungsgang',

                'ExcusedDays' => 'Ganze Tage',
                'ExcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten'),
                'ExcusedDaysFromLessons' => new ToolTip('Zusatz-Tage für Unterrichts&shy;einheiten',
                    'Für fehlende Unterrichtseinheiten können zusätzliche Fehltage erfasst werden'),

                'UnexcusedDays' => 'Ganze Tage',
                'UnexcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten'),
                'UnexcusedDaysFromLessons' => new ToolTip('Zusatz-Tage für Unterrichts&shy;einheiten',
                    'Für fehlende Unterrichtseinheiten können zusätzliche Fehltage erfasst werden')
            );
        } else {
            if($isAbsenceHour){
                // Zusatzspalte für Stunden
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'IntegrationButton' => 'Inklusion',
                    'Course' => 'Bildungsgang',

                    'ExcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                    'ExcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                    'ExcusedDaysInHours' => new ToolTip('Stunden','Gesamt: 1 Tag = 8 Stunden'),
                    'ExcusedDays' => 'Stunden auf dem Zeugnis',

                    'UnexcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                    'UnexcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                    'UnexcusedDaysInHours' => new ToolTip('Stunden','Gesamt: 1 Tag = 8 Stunden'),
                    'UnexcusedDays' => 'Stunden auf dem Zeugnis',
                );
            } else {
                // Standard
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'IntegrationButton' => 'Inklusion',
                    'Course' => 'Bildungsgang',

                    'ExcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                    'ExcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                    'ExcusedDays' => 'Tage auf dem Zeugnis',

                    'UnexcusedDaysInClassRegister' => new ToolTip('Ganze Tage', 'Ganze Tage im Klassenbuch'),
                    'UnexcusedLessons' => new ToolTip('Unterrichts&shy;einheiten', 'Unterrichtseinheiten im Klassenbuch'),
                    'UnexcusedDays' => 'Tage auf dem Zeugnis',
                );
            }
        }

        $studentTable = array();
        if ($tblPersonList && $tblYear) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && $tblGenerateCertificate->getAppointedDateForAbsence()
            ) {
                $tillDateAbsence = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
            } else {
                $tillDateAbsence = new DateTime($tblPrepare->getDate());
            }
            list($startDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);

            $count = 0;
            foreach ($tblPersonList as $tblPerson) {
                $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                $studentTable[$tblPerson->getId()] = $this->getStudentBasicInformation($tblPerson, $tblYear, $tblPrepareStudent ?: null, $count);
                $tblCompany = false;
                $tblSchoolType = false;
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $tblCompany = $tblStudentEducation->getServiceTblCompany();
                    $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                }

                $excusedDays = 0;
                $excusedLessons = 0;
                $excusedDaysFromClassRegister = 0;
                $unexcusedDays = 0;
                $unexcusedLessons = 0;
                $unexcusedDaysFromClassRegister = 0;

                if ($tblPrepareStudent) {
                    $excusedDays =  $tblPrepareStudent->getExcusedDays();
                    $excusedDaysFromClassRegister = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                        $startDateAbsence, $tillDateAbsence, $excusedLessons);

                    $unexcusedDays =  $tblPrepareStudent->getUnexcusedDays();
                    $unexcusedDaysFromClassRegister = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                        $startDateAbsence, $tillDateAbsence, $unexcusedLessons);

                    /*
                     * Post Fehlzeiten
                     */
                    if ($Data === null) {
                        $Global = $this->getGlobal();
                        if ($Global) {
                            if ($useClassRegisterForAbsence) {
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['ExcusedDaysFromLessons'] = $tblPrepareStudent->getExcusedDaysFromLessons();
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['UnexcusedDaysFromLessons'] = $tblPrepareStudent->getUnexcusedDaysFromLessons();
                            } else {
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['ExcusedDays'] = $excusedDays;
                                $Global->POST['Data'][$tblPrepareStudent->getId()]['UnexcusedDays'] = $unexcusedDays;
                            }
                        }
                        $Global->savePost();
                    }
                }

                if ($tblPrepareStudent
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                    && (isset($CertificateHasAbsenceList[$tblCertificate->getId()]))
                ) {
                    if ($useClassRegisterForAbsence) {
                        // Fehlzeiten werden im Klassenbuch gepflegt
                        if ($excusedDays === null) {
                            $excusedDays = $excusedDaysFromClassRegister;
                        }
                        if ($unexcusedDays === null) {
                            $unexcusedDays = $unexcusedDaysFromClassRegister;
                        }

                        $studentTable[$tblPerson->getId()]['ExcusedDays'] = $excusedDays;
                        $studentTable[$tblPerson->getId()]['ExcusedDaysInHours'] = $excusedDays * 8 + $excusedLessons;
                        $studentTable[$tblPerson->getId()]['ExcusedLessons'] = $excusedLessons;

                        $studentTable[$tblPerson->getId()]['UnexcusedDays'] = $unexcusedDays;
                        $studentTable[$tblPerson->getId()]['UnexcusedDaysInHours'] = $unexcusedDays * 8 + $unexcusedLessons;
                        $studentTable[$tblPerson->getId()]['UnexcusedLessons'] = $unexcusedLessons;

                        // Eingabe nur möglich wenn UEs beim Schüler erfasst wurden
                        if (isset($StudentHasAbsenceLessonsList[$tblPerson->getId()][TblAbsence::VALUE_STATUS_EXCUSED])) {
                            $inputExcusedDaysFromLessons = new NumberField(
                                'Data[' . $tblPrepareStudent->getId() . '][ExcusedDaysFromLessons]',
                                '',
                                ''
                            );

                            if ($tblPrepareStudent->isApproved()) {
                                $inputExcusedDaysFromLessons->setDisabled();
                            }
                            $studentTable[$tblPerson->getId()]['ExcusedDaysFromLessons'] = $inputExcusedDaysFromLessons;
                        }
                        if (isset($StudentHasAbsenceLessonsList[$tblPerson->getId()][TblAbsence::VALUE_STATUS_UNEXCUSED])) {
                            $inputUnexcusedDaysFromLessons = new NumberField(
                                'Data[' . $tblPrepareStudent->getId() . '][UnexcusedDaysFromLessons]',
                                '',
                                ''
                            );
                            if ($tblPrepareStudent->isApproved()) {
                                $inputUnexcusedDaysFromLessons->setDisabled();
                            }
                            $studentTable[$tblPerson->getId()]['UnexcusedDaysFromLessons'] = $inputUnexcusedDaysFromLessons;
                        }
                    } else {
                        // Fehlzeiten werden hier (Zeugnisvorbereitung) gepflegt
                        $studentTable[$tblPerson->getId()]['ExcusedDaysInClassRegister'] = $excusedDaysFromClassRegister;
                        $studentTable[$tblPerson->getId()]['ExcusedDaysInHours'] = $excusedDaysFromClassRegister * 8 + $excusedLessons;
                        $studentTable[$tblPerson->getId()]['ExcusedLessons'] = $excusedLessons;

                        $studentTable[$tblPerson->getId()]['UnexcusedDaysInClassRegister'] = $unexcusedDaysFromClassRegister;
                        $studentTable[$tblPerson->getId()]['UnexcusedDaysInHours'] = $unexcusedDaysFromClassRegister * 8 + $unexcusedLessons;
                        $studentTable[$tblPerson->getId()]['UnexcusedLessons'] = $unexcusedLessons;

                        $inputExcusedDays = new NumberField(
                            'Data[' . $tblPrepareStudent->getId() . '][ExcusedDays]',
                            '',
                            ''
                        );
                        $inputUnexcusedDays = new NumberField(
                            'Data[' . $tblPrepareStudent->getId() . '][UnexcusedDays]',
                            '',
                            ''
                        );
                        if ($tblPrepareStudent->isApproved()) {
                            $inputExcusedDays->setDisabled();
                            $inputUnexcusedDays->setDisabled();
                        }
                        $studentTable[$tblPerson->getId()]['ExcusedDays'] = $inputExcusedDays;
                        $studentTable[$tblPerson->getId()]['UnexcusedDays'] = $inputUnexcusedDays;
                    }
                }

                foreach ($columnTable as $keyColumn => $itemColumn) {
                    if (!isset($studentTable[$tblPerson->getId()][$keyColumn])) {
                        $studentTable[$tblPerson->getId()][$keyColumn] = '';
                    }
                }
            }
        }

        $Interactive = array(
            "columnDefs" => array(
                array(
                    "width" => "18px",
                    "targets" => 0
                ),
                array(
                    "width" => "200px",
                    "targets" => 1
                ),
                array(
                    "width" => "80px",
                    "targets" => 2
                ),
            ),
            'order' => array(
                array('0', 'asc'),
            ),
            "paging" => false, // Deaktivieren Blättern
            "iDisplayLength" => -1,    // Alle Einträge zeigen
            "searching" => false, // Deaktivieren Suchen
            "info" => false,  // Deaktivieren Such-Info
            "sort" => false,
            "responsive" => false
        );

        $tableData = new TableData($studentTable, null, $columnTable, $Interactive, true);

        $formButtons[] = new Primary('Speichern', new Save());
        if (!empty($headTableColumnList)) {
            $tableData->prependHead(
                new TableHead(
                    new TableRow(
                        $headTableColumnList
                    )
                )
            );

            $formButtons[] = new Standard('Ohne Speichern weiter', '/Education/Certificate/Prepare/Prepare/Preview',
                null, array('PrepareId' => $tblPrepare->getId(), 'Route' => $Route = 'Teacher'));
        }

        $form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $tableData
                    ),
                    new FormColumn(new HiddenField('Data[IsSubmit]'))
                )),
                new FormRow(new FormColumn($formButtons))
            ))
        );

        $Stage->setContent(
            ApiReloadReceiver::receiverReload(ApiReloadReceiver::pipelineReload())
            .ApiPrepare::receiverModal()
            .new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnis',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                $tblDivisionCourse ? $tblDivisionCourse->getTypeName() : '',
                                $tblDivisionCourse ? $tblDivisionCourse->getDisplayName() : '',
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn($buttonList),
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiSupportReadOnly::receiverOverViewModal(),
                            Prepare::useService()->updatePrepareInformationList($form, $tblPrepare, $Route, $Data, $CertificateList, $nextPage)
                        ))
                    ))
                ))
            ))
        );
    }

    public function getInformationContent(TblPrepareCertificate $tblPrepare, string $Route, $CertificateList, Stage $Stage, $Data, array $buttonList,
        $nextPage, $Page, $informationPageList)
    {
        $columnTable = array(
            'Number' => '#',
            'Name' => 'Name',
            'IntegrationButton' => 'Inklusion',
            'Course' => 'Bildungsgang'
        );

        $studentTable = array();
        if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $count = 0;
            foreach ($tblPersonList as $tblPerson) {
                $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                $studentTable[$tblPerson->getId()] = $this->getStudentBasicInformation($tblPerson, $tblYear, $tblPrepareStudent ?: null, $count);

                /*
                 * Sonstige Informationen der Zeugnisvorlage
                 */
                Prepare::useService()->getTemplateInformation(
                    $tblPrepare,
                    $tblPerson,
                    $studentTable,
                    $columnTable,
                    $Data,
                    $CertificateList,
                    $Page,
                    $informationPageList
                );

                // leere Elemente auffühlen (sonst steht die Spaltennummer drin)
                foreach ($columnTable as $columnKey => $columnName) {
                    foreach ($studentTable as $personId => $value) {
                        if (!isset($studentTable[$personId][$columnKey])) {
                            $studentTable[$personId][$columnKey] = '';
                        }
                    }
                }
            }
        }

        $SpaceWithFalseTable = '';
        // if using false table, need to be space between buttons & table
        // same consumer as those who using Editor ad inputfield
        if (Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVAB')) {
            $Interactive = false;
            $SpaceWithFalseTable = new Container('&nbsp;');
        } else {
            $Interactive = array(
                "columnDefs" => array(
                    array(
                        "width" => "18px",
                        "targets" => 0
                    ),
                    array(
                        "width" => "200px",
                        "targets" => 1
                    ),
                    array(
                        "width" => "80px",
                        "targets" => 2
                    ),
                ),
                'order' => array(
                    array('0', 'asc'),
                ),
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false
            );
        }

        $tableData = new TableData($studentTable, null, $columnTable, $Interactive, true);

        $formButtons[] = new Primary('Speichern', new Save());
        $form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $tableData
                    ),
                    new FormColumn(new HiddenField('Data[IsSubmit]'))
                )),
                new FormRow(new FormColumn($formButtons))
            ))
        );

        $Stage->setContent(
            ApiReloadReceiver::receiverReload(ApiReloadReceiver::pipelineReload())
            .ApiPrepare::receiverModal()
            .new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnis',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                $tblDivisionCourse->getTypeName(),
                                $tblDivisionCourse->getDisplayName(),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn($buttonList),
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiSupportReadOnly::receiverOverViewModal(),
                            $SpaceWithFalseTable,
                            Prepare::useService()->updatePrepareInformationList($form, $tblPrepare, $Route, $Data, $CertificateList, $nextPage)
                        ))
                    ))
                ))
            ))
        );
    }

    private function getInformationButtonList(TblPrepareCertificate $tblPrepare, string $Route, bool $useClassRegisterForAbsence, array $tblGradeTypeList,
        $Page, &$nextPage, &$informationPageList, &$CertificateHasAbsenceList, &$StudentHasAbsenceLessonsList): array
    {
        // Tabs für Zensuren-Typen
        $buttonList = array();
        /** @var TblGradeType $tblGradeType */
        foreach ($tblGradeTypeList as $tblGradeType) {
            $buttonList[] = new Standard($tblGradeType->getName(),
                '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route,
                    'GradeTypeId' => $tblGradeType->getId()
                )
            );
        }

        // Erstellt zusätzliche "Tabs" für weitere Sonstige Informationen und die Fehlzeiten
        list($informationPageList, $pageList, $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList)
            = Prepare::useService()->getCertificateInformationPages($tblPrepare, $useClassRegisterForAbsence);

        if ($Page == null) {
            $buttonList[] = new Standard(new Info(new Bold('Sonstige Informationen')),
                '/Education/Certificate/Prepare/Prepare/Setting', new Edit(), array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route,
                    'IsNotGradeType' => true
                )
            );
        } else {
            $buttonList[] = new Standard('Sonstige Informationen',
                '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route,
                    'IsNotGradeType' => true
                )
            );
        }

        $nextPage = null;
        $isCurrentPage = $Page == null;
        foreach ($pageList as $item) {
            if ($item == 'Absence') {
                $text = 'Fehlzeiten';
            } else {
                $text = 'Sonstige Informationen (Seite ' . $item . ')';
            }

            if ($Page == $item) {
                $buttonList[] = new Standard(new Info(new Bold($text)),
                    '/Education/Certificate/Prepare/Prepare/Setting', new Edit(), array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'IsNotGradeType' => true,
                        'Page' => $item
                    )
                );
                $isCurrentPage = true;
            } else {
                $buttonList[] = new Standard($text,
                    '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'IsNotGradeType' => true,
                        'Page' => $item
                    )
                );

                if ($isCurrentPage) {
                    $nextPage = $item;
                    $isCurrentPage = false;
                }
            }
        }

        return $buttonList;
    }
}