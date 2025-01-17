<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

abstract class FrontendLeaveTechnicalSchool extends FrontendLeaveSekTwoBGy
{
    private array $selectListGrades = array();
    private array $selectListGradeTexts = array();

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param Stage $stage
     * @param $Data
     * @param $subjectData
     * @param TblCertificate|null $tblCertificate
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param TblType|null $tblType
     * @param bool $isBfs
     *
     * @return array
     */
    protected function setLeaveContentForTechnicalSchool(
        TblPerson $tblPerson,
        TblYear $tblYear,
        Stage $stage,
        $Data,
        $subjectData,
        ?TblCertificate $tblCertificate,
        ?TblLeaveStudent $tblLeaveStudent,
        ?TblType $tblType,
        bool $isBfs = true
    ): array {

        $hasPreviewGrades = false;
        $isApproved = false;
        $hasMissingSubjects = false;
        $certificateDate = new DateTime('today');

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
        }

        if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
            && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
        ) {
            $tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse();
        } else {
            $tblTechnicalCourse = false;
            $tblStudentTechnicalSchool = false;
        }

        // Grades
        $this->selectListGrades = array();
        $this->selectListGrades[-1] = '';
        for ($i = 1; $i < 6; $i++) {
            $this->selectListGrades[$i] = (string)($i);
        }
        $this->selectListGrades[6] = 6;

        // GradeTexts
        $this->selectListGradeTexts = array();
        if (($tblGradeTextList = Grade::useService()->getGradeTextAll())) {
            $this->selectListGradeTexts = $tblGradeTextList;
        }

        if ($tblCertificate) {
            if ($tblLeaveStudent) {
                $isApproved = $tblLeaveStudent->isApproved();

                $stage->addButton(new External(
                    'Zeugnis als Muster herunterladen',
                    '/Api/Education/Certificate/Generator/PreviewLeave',
                    new Download(),
                    array(
                        'LeaveStudentId' => $tblLeaveStudent->getId(),
                        'Name' => 'Zeugnismuster'
                    ),
                    'Zeugnis als Muster herunterladen'));
            }

            // Post setzen
            if ($tblLeaveStudent) {
                $Global = $this->getGlobal();
                if (($tblLeaveGradeList = Prepare::useService()->getLeaveGradeAllByLeaveStudent($tblLeaveStudent))) {
                    foreach ($tblLeaveGradeList as $tblLeaveGrade) {
                        if (($tblSubject = $tblLeaveGrade->getServiceTblSubject())) {
                            if (($tblGradeText = Grade::useService()->getGradeTextByName($tblLeaveGrade->getGrade()))) {
                                $Global->POST['Data']['Grades'][$tblSubject->getId()]['GradeText'] = $tblGradeText->getId();
                            } else {
                                $Global->POST['Data']['Grades'][$tblSubject->getId()]['Grade'] = $tblLeaveGrade->getGrade();
                            }
                        }
                    }
                }

                $isSetSubjectArea = false;
                if (($tblLeaveInformationList = Prepare::useService()->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
                    foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                        $field = $tblLeaveInformation->getField();
                        $value = $tblLeaveInformation->getValue();

                        // Zeugnistext umwandeln
                        if (strpos($field, '_GradeText')
                            && ($tblGradeText = Grade::useService()->getGradeTextByName($value))
                        ) {
                            $value = $tblGradeText->getId();
                        }

                        if ($field == 'SubjectArea') {
                            $isSetSubjectArea = true;
                        }

                        $Global->POST['Data']['InformationList'][$field] = $value;

                        if ($tblLeaveInformation->getField() == 'CertificateDate' && $value != '') {
                            $certificateDate = new DateTime($value);
                        }
                    }
                }

                if (!$isBfs && !$isSetSubjectArea
                    && $tblStudentTechnicalSchool
                    && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                ) {
                    $Global->POST['Data']['InformationList']['SubjectArea'] = $tblTechnicalSubjectArea->getName();
                }

                if (($tblLeaveComplexExamList = Prepare::useService()->getLeaveComplexExamAllByLeaveStudent($tblLeaveStudent))) {
                    foreach ($tblLeaveComplexExamList as $tblLeaveComplexExam) {
                        $identifier = $tblLeaveComplexExam->getIdentifier();
                        $ranking = $tblLeaveComplexExam->getRanking();
                        $grade = $tblLeaveComplexExam->getGrade();

                        if (($tblFirstSubject = $tblLeaveComplexExam->getServiceTblFirstSubject())) {
                            $Global->POST['Data']['ExamList'][$identifier . '_' . $ranking]['S1'] = $tblFirstSubject->getId();
                        }
                        if (($tblSecondSubject = $tblLeaveComplexExam->getServiceTblSecondSubject())) {
                            $Global->POST['Data']['ExamList'][$identifier . '_' . $ranking]['S2'] = $tblSecondSubject->getId();
                        }

                        if (($tblGradeText = Grade::useService()->getGradeTextByName($grade))) {
                            $Global->POST['Data']['ExamList'][$identifier . '_' . $ranking]['GradeText'] = $tblGradeText->getId();
                        } else {
                            $Global->POST['Data']['ExamList'][$identifier . '_' . $ranking]['Grade'] = $grade;
                        }
                    }
                }

                $Global->savePost();
            } else {
                if (!$isBfs
                    && $tblStudentTechnicalSchool
                    && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                ) {
                    $Global = $this->getGlobal();

                    $Global->POST['Data']['InformationList']['SubjectArea'] = $tblTechnicalSubjectArea->getName();

                    $Global->savePost();
                }
            }

            if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYear))) {
                $tabIndex = 0;
                foreach ($tblSubjectList as $tblSubject) {
                    $gradeDisplayList = array();
                    $gradeValueList = array();
                    $contentAverage = '';
                    if (($tblGradeList = Grade::useService()->getTestGradeListToDateTimeByPersonAndSubject($tblPerson, $tblSubject, $certificateDate))) {
                        $tblGradeList = $this->getSorter($tblGradeList)->sortObjectBy('SortDate', new DateTimeSorter());
                        /** @var TblTestGrade $tblGrade */
                        foreach ($tblGradeList as $tblGrade) {
                            if (($tblGradeType = $tblGrade->getTblGradeType())
                                && $tblGrade->getIsGradeNumeric()
                            ) {
                                $description = '';
                                if (($tblTest = $tblGrade->getTblTest())) {
                                    $description = $tblTest->getDescription();
                                }

                                $text = new ToolTip($tblGradeType->getCode() . ':' . str_replace('.', ',', $tblGrade->getGrade()),
                                    $tblGrade->getSortDate()->format('d.m.Y') . ' ' . $description);
                                $gradeDisplayList[] = $tblGradeType->getIsHighlighted() ? new Bold($text) : $text;
                                $gradeValueList[] = $tblGrade;
                            }
                        }

                        $hasNoLeaveGrade = !($tblLeaveStudent && Prepare::useService()->getLeaveGradeBy($tblLeaveStudent, $tblSubject));
                        /**
                         * Average
                         */
                        $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
                        list ($average, $scoreRuleText, $error) = Grade::useService()->getCalcStudentAverage($tblPerson, $tblYear, $gradeValueList, $tblScoreRule ?: null);
                        $contentAverage = '&#216; ' . Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);
                        // Zensuren voreintragen, wenn noch keine vergeben ist
                        if (($average || $average === (float)0) && $hasNoLeaveGrade) {
                            $hasPreviewGrades = true;
                            $Global = $this->getGlobal();
                            $Global->POST['Data']['Grades'][$tblSubject->getId()]['Grade'] = round(floatval(str_replace(',', '.', $average)), 0);
                            $Global->savePost();
                        }
                    }

                    $selectComplete = (new SelectCompleter('Data[Grades][' . $tblSubject->getId() . '][Grade]', '', '', $this->selectListGrades))
                        ->setTabIndex($tabIndex++);
                    if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                        $selectComplete->setDisabled();
                    }

                    // Zeugnistext
                    $gradeText = new SelectBox('Data[Grades][' . $tblSubject->getId() . '][GradeText]', '', array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

                    if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                        $gradeText->setDisabled();
                    }

                    $subjectCategory = '';
                    if (!($tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject(
                        $tblCertificate,
                        $tblSubject,
                        $tblTechnicalCourse ?: null
                    ))) {
                        $hasMissingSubjects = true;
                        $subjectName = new \SPHERE\Common\Frontend\Text\Repository\Warning($tblSubject->getName() . ' ' . new Ban());
                    } else {
                        $subjectName = $tblSubject->getName();
                        $ranking = $tblCertificateSubject->getRanking();
                        if ($isBfs) {
                            if ($ranking <= 4) {
                                $subjectCategory = 'Berufsübergreifender';
                            } elseif ($ranking <= 12) {
                                $subjectCategory = 'Berufsbezogener';
                            } elseif ($ranking <= 14) {
                                $subjectCategory = 'Wahlpflicht';
                            } elseif ($ranking <= 15) {
                                $subjectCategory = 'Berufspraktische Ausbildung';
                            }
                        } else {
                            if ($ranking <= 4) {
                                $subjectCategory = 'Fachrichtungsübergreifender';
                            } elseif ($ranking <= 14) {
                                $subjectCategory = 'Fachrichtungsbezogener';
                            } elseif ($ranking <= 16) {
                                $subjectCategory = 'Wahlpflicht';
                            } elseif ($ranking <= 17) {
                                $subjectCategory = 'Berufspraktische Ausbildung';
                            }
                        }
                    }

                    $subjectData[$tblSubject->getAcronym()] = array(
                        'SubjectName' => $subjectName,
                        'SubjectCategory' => $subjectCategory,
                        'GradeList' => implode(' | ', $gradeDisplayList),
                        'Average' => $contentAverage,
                        'Grade' => $selectComplete,
                        'GradeText' => $gradeText
                    );
                }
            }
        }

        if (!$isApproved && $tblType && $tblType->getShortName() == 'BFS') {
            $canChangeCertificate = true;
        } else {
            $canChangeCertificate = false;
        }

        $layoutGroups[] = new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Schüler',
                        $tblPerson->getLastFirstNameWithCallNameUnderline(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schuljahr',
                        $tblYear->getDisplayName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schulart',
                        $tblType
                            ? $tblType->getName()
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                        $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Zeugnisvorlage',
                        $tblCertificate
                            ? $tblCertificate->getName()
                            . ($tblCertificate->getDescription()
                                ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                            . ($canChangeCertificate
                                ? new Link('Bearbeiten', '/Education/Certificate/Prepare/Leave/Student', new Pencil(), array(
                                    'PersonId' => $tblPerson->getId(),
                                    'YearId' => $tblYear->getId(),
                                    'ChangeCertificate' => true
                                ))
                                : '')
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine Zeugnisvorlage verfügbar!'),
                        $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3)
            )),
            ($support
                ? new LayoutRow(new LayoutColumn(new Panel('Inklusion', $support, Panel::PANEL_TYPE_INFO)))
                : null
            ),
            ($hasMissingSubjects
                ? new LayoutRow(new LayoutColumn(new Warning(
                    'Es sind nicht alle Fächer auf der Zeugnisvorlage eingestellt.', new Exclamation()
                )))
                : null
            ),
            ($hasPreviewGrades
                ? new LayoutRow(new LayoutColumn(new Warning(
                    'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                )))
                : null
            )
        ));

        if ($tblCertificate) {
            // Vorsitzende/r des Prüfungsausschusses aller Lehrer
            $divisionTeacherList = array();
            if (($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
                && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
            ) {
                foreach ($tblPersonList as $tblPersonTeacher) {
                    $divisionTeacherList[$tblPersonTeacher->getId()] = $tblPersonTeacher->getFullName();
                }
            }

            if (!empty($subjectData)) {
                ksort($subjectData);
                $subjectTable = new TableData(
                    $subjectData,
                    null,
                    array(
                        'SubjectName' => 'Fach',
                        'SubjectCategory' => 'Bereich',
                        'GradeList' => 'Vornoten (Alle Schuljahre)',
                        'Average' => '&#216;',
                        'Grade' => 'Zensur',
                        'GradeText' => 'oder Zeugnistext'
                    ),
                    null
                );
            } else {
                $subjectTable = false;
            }

            $text = $isBfs ? 'Berufsfachschule' : 'Fachschule';
            $dateFromPicker = (new DatePicker('Data[InformationList][DateFrom]', '', 'Besucht "seit" die ' . $text, new Calendar()))->setRequired();
            $dateToPicker = (new DatePicker('Data[InformationList][DateTo]', '', 'Besucht "bis" die ' . $text, new Calendar()))->setRequired();

            $datePicker = (new DatePicker('Data[InformationList][CertificateDate]', '', 'Zeugnisdatum', new Calendar()))->setRequired();
            $remarkTextArea = new TextArea('Data[InformationList][RemarkWithoutTeam]', '', 'Bemerkungen');

            if ($isApproved) {
                $dateFromPicker->setDisabled();
                $dateToPicker->setDisabled();

                $datePicker->setDisabled();
                $remarkTextArea->setDisabled();
            }

            $panelSkilledWork = false;
            $panelWrittenComplexExams = false;
            $panelPraxisComplexExams = false;
            $panelAddEducation = false;
            $panelChosenArea = false;

            if ($isBfs) {
                // Berufsfachschule
                $destinationInput = (new TextField('Data[InformationList][BfsDestination]', '', 'Berufsfachschule für ...'))->setRequired();

                $operationTimeTotal = (new TextField('Data[InformationList][OperationTimeTotal]', '', 'Gesamtdauer in Wochen'));
                $operation1Input = (new TextField('Data[InformationList][Operation1]', '', 'Einsatzgebiet 1'));
                $operationTime1Input = (new TextField('Data[InformationList][OperationTime1]', '', 'Einsatzgebiet Dauer in Wochen 1'));
                $operation2Input = (new TextField('Data[InformationList][Operation2]', '', 'Einsatzgebiet 2'));
                $operationTime2Input = (new TextField('Data[InformationList][OperationTime2]', '', 'Einsatzgebiet Dauer in Wochen 2'));
                $operation3Input = (new TextField('Data[InformationList][Operation3]', '', 'Einsatzgebiet 3'));
                $operationTime3Input = (new TextField('Data[InformationList][OperationTime3]', '', 'Einsatzgebiet Dauer in Wochen 3'));
                if ($tblCertificate->getCertificate() == 'BfsAbgGeneralistik') {
                    $operation4Input = (new TextField('Data[InformationList][Operation4]', '', 'Einsatzgebiet 4'));
                    $operationTime4Input = (new TextField('Data[InformationList][OperationTime4]', '', 'Einsatzgebiet Dauer in Wochen 4'));
                }

                if ($isApproved) {
                    $destinationInput->setDisabled();

                    $operationTimeTotal->setDisabled();
                    $operation1Input->setDisabled();
                    $operationTime1Input->setDisabled();
                    $operation2Input->setDisabled();
                    $operationTime2Input->setDisabled();
                    $operation3Input->setDisabled();
                    $operationTime3Input->setDisabled();

                    if ($tblCertificate->getCertificate() == 'BfsAbgGeneralistik') {
                        $operation4Input->setDisabled();
                        $operationTime4Input->setDisabled();
                    }
                }

                $panelEducation = new Panel(
                    'Ausbildung',
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn($dateFromPicker, 6),
                            new LayoutColumn($dateToPicker, 6)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn($destinationInput)
                        )),
                    ))),
                    Panel::PANEL_TYPE_INFO
                );

                $layoutRows[] = new LayoutRow(array(
                    new LayoutColumn($operationTimeTotal, 12)
                ));
                $layoutRows[] = new LayoutRow(array(
                    new LayoutColumn($operation1Input, 6),
                    new LayoutColumn($operationTime1Input, 6)
                ));
                $layoutRows[] = new LayoutRow(array(
                    new LayoutColumn($operation2Input, 6),
                    new LayoutColumn($operationTime2Input, 6)
                ));
                $layoutRows[] = new LayoutRow(array(
                    new LayoutColumn($operation3Input, 6),
                    new LayoutColumn($operationTime3Input, 6)
                ));
                if ($tblCertificate->getCertificate() == 'BfsAbgGeneralistik') {
                    $layoutRows[] = new LayoutRow(array(
                        new LayoutColumn($operation4Input, 6),
                        new LayoutColumn($operationTime4Input, 6)
                    ));
                }
                $panelPraxis = new Panel(
                    'Berufspraktische Ausbildung',
                    new Layout(new LayoutGroup($layoutRows)),
                    Panel::PANEL_TYPE_INFO
                );
            } else {
                // Fachschule

                $destinationInput = (new TextField('Data[InformationList][FsDestination]', '', 'Fachbereich'))->setRequired();
                $subjectAreaInput = (new TextField('Data[InformationList][SubjectArea]', '', 'Fachrichtung'));
                $focusInput = (new TextField('Data[InformationList][Focus]', '', 'Schwerpunkt'));

                $jobEducationDuration = (new TextField('Data[InformationList][JobEducationDuration]', '', 'Dauer in Wochen'));

                $chosenArea1Input = new TextField('Data[InformationList][ChosenArea1]', '', 'Wahlbereich 1');
                $chosenArea2Input = new TextField('Data[InformationList][ChosenArea2]', '', 'Wahlbereich 2');

                if ($isApproved) {
                    $destinationInput->setDisabled();
                    $subjectAreaInput->setDisabled();
                    $focusInput->setDisabled();

                    $jobEducationDuration->setDisabled();

                    $chosenArea1Input->setDisabled();
                    $chosenArea2Input->setDisabled();
                }

                $panelEducation = new Panel(
                    'Ausbildung',
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn($dateFromPicker, 6),
                            new LayoutColumn($dateToPicker, 6)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn($destinationInput, 4),
                            new LayoutColumn($subjectAreaInput, 4),
                            new LayoutColumn($focusInput, 4)
                        )),
                    ))),
                    Panel::PANEL_TYPE_INFO
                );

                // Schriftliche Komplexprüfung
                $this->setSubjectListByTechnicalCourse($tblCertificate, $tblTechnicalCourse ?: null);
                $columns = array(
                    'Ranking' => '#',
                    'FirstSubject' => '1. Fach',
                    'SecondSubject' => '2. Fach',
                    'Grade' => 'Zensur',
                    'GradeText' => 'oder Zeugnistext'
                );
                $dataWrittenExamList = array();
                $identifier = TblLeaveComplexExam::IDENTIFIER_WRITTEN;
                for ($i = 1; $i < 5; $i++) {
                    $dataWrittenExamList[$i] = array(
                        'Ranking' => 'K' . $i,
                        'FirstSubject' => $this->getComplexExamSubjectSelectBox($identifier, $i, 1, $isApproved),
                        'SecondSubject' => $this->getComplexExamSubjectSelectBox($identifier, $i, 2, $isApproved),
                        'Grade' => $this->getComplexExamGradeInput($identifier, $i, $isApproved),
                        'GradeText' => $this->getComplexExamGradeTextSelect($identifier, $i, $isApproved)
                    );
                }
                $panelWrittenComplexExams = new Panel(
                    'Schriftliche Komplexprüfung',
                    new TableData(
                        $dataWrittenExamList,
                        null,
                        $columns,
                        null
                    ),
                    Panel::PANEL_TYPE_INFO
                );

                // Praktische Komplexprüfung
                $identifier = TblLeaveComplexExam::IDENTIFIER_PRAXIS;

                $dataPraxisExamList = array();
                $i = 1;
                $dataPraxisExamList[$i] = array(
                    'Ranking' => $i,
                    'FirstSubject' => $this->getComplexExamSubjectSelectBox($identifier, $i, 1, $isApproved),
                    'SecondSubject' => $this->getComplexExamSubjectSelectBox($identifier, $i, 2, $isApproved),
                    'Grade' => $this->getComplexExamGradeInput($identifier, $i, $isApproved),
                    'GradeText' => $this->getComplexExamGradeTextSelect($identifier, $i, $isApproved)
                );
                $panelPraxisComplexExams = new Panel(
                    'Praktische Komplexprüfung',
                    new TableData(
                        $dataPraxisExamList,
                        null,
                        $columns,
                        null
                    ),
//                    new Layout(new LayoutGroup(new LayoutRow(array(
//                        new LayoutColumn($this->getComplexExamSubjectSelectBox($identifier, $i, 1, $isApproved, true), 4),
//                        new LayoutColumn($this->getComplexExamSubjectSelectBox($identifier, $i, 2, $isApproved, true), 4),
//                        new LayoutColumn($this->getComplexExamGradeInput($identifier, $i, $isApproved, true), 2),
//                        new LayoutColumn($this->getComplexExamGradeTextSelect($identifier, $i, $isApproved, true), 2),
//                    )))),
                    Panel::PANEL_TYPE_INFO
                );

                $panelPraxis = new Panel(
                    'Berufspraktische Ausbildung',
                    $jobEducationDuration,
                    Panel::PANEL_TYPE_INFO
                );

                // Facharbeit Thema
                $panelSkilledWork = $this->getPanel('Facharbeit', 'SkilledWork', 'Thema', $isApproved);

                // Zusatzausbildung zum Erwerb der Fachhochschulreife muss raus
                $panelAddEducation = $this->getPanel('Zusatzausbildung zum Erwerb der Fachhochschulreife',
                    'AddEducation', 'Zusatzausbildung', $isApproved);

                // Wahlbereich
                $panelChosenArea = new Panel(
                    'Wahlbereich',
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn($chosenArea1Input, 6),
                        new LayoutColumn($chosenArea2Input, 6)
                    )))),
                    Panel::PANEL_TYPE_INFO
                );
            }

            $otherInformationList[] =  $datePicker;
            $otherInformationList[] =  $remarkTextArea;

            if ($tblCertificate->getCertificate() == 'BfsAbgGeneralistik') {
                $teacherDescription = 'Klassenlehrer/in';
            } else {
                $teacherDescription = 'Vorsitzende/r des Prüfungsausschusses';
            }

            $headmasterNameTextField = new TextField('Data[InformationList][HeadmasterName]', '',
                'Name des/der Schulleiters/in');
            $radioSex1 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Männlich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                    ? $tblCommonGender->getId() : 0));
            $radioSex2 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Weiblich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                    ? $tblCommonGender->getId() : 0));
            $teacherSelectBox = new SelectBox('Data[InformationList][DivisionTeacher]', $teacherDescription,
                $divisionTeacherList);
            if ($isApproved) {
                $headmasterNameTextField->setDisabled();
                $radioSex1->setDisabled();
                $radioSex2->setDisabled();
                $teacherSelectBox->setDisabled();
            }

            $form = new Form(new FormGroup(array(
                $subjectTable ? new FormRow(new FormColumn(
                    new Panel('Zensuren', $subjectTable, Panel::PANEL_TYPE_INFO)
                )) : null,
                new FormRow(new FormColumn($panelEducation)),
                $panelWrittenComplexExams ? new FormRow(new FormColumn($panelWrittenComplexExams)) : null,
                $panelPraxisComplexExams ? new FormRow(new FormColumn($panelPraxisComplexExams)) : null,
                new FormRow(new FormColumn($panelPraxis)),
                $panelSkilledWork ? new FormRow(new FormColumn($panelSkilledWork)) : null,
                $panelAddEducation ? new FormRow(new FormColumn($panelAddEducation)) : null,
                $panelChosenArea ? new FormRow(new FormColumn($panelChosenArea)) : null,
                new FormRow(new FormColumn(
                    new Panel(
                        'Sonstige Informationen',
                        $otherInformationList,
                        Panel::PANEL_TYPE_INFO
                    )
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Unterzeichner - ' . $teacherDescription,
                            $teacherSelectBox,
                            Panel::PANEL_TYPE_INFO
                        )
                        , 6),
                    new FormColumn(
                        new Panel(
                            'Unterzeichner - Schulleiter/in',
                            array(
                                $headmasterNameTextField,
                                new Panel(
                                    new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                    array($radioSex1, $radioSex2),
                                    Panel::PANEL_TYPE_DEFAULT
                                )
                            ),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 6)
                )),
            )));
            if (!$isApproved) {
                $form->appendFormButton(new Primary('Speichern', new Save()));
            }

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Well(
                    Prepare::useService()->updateLeaveContent($form, $tblPerson, $tblYear, $tblCertificate, $Data)
                )
            )));
        }

        return $layoutGroups;
    }

    /**
     * @param $identifier
     * @param $ranking
     * @param $subjectCount
     * @param $isApproved
     * @param bool $hasLabel
     *
     * @return SelectBox
     */
    private function getComplexExamSubjectSelectBox(
        $identifier,
        $ranking,
        $subjectCount,
        $isApproved,
        bool $hasLabel = false
    ): SelectBox {
        $selectBox = new SelectBox(
            'Data[ExamList][' . $identifier . '_' . $ranking . '][S' . $subjectCount . ']',
            $hasLabel ? $subjectCount . '. Fach' : '',
            array('{{ TechnicalAcronymForCertificateFromName }}' => $this->subjectList)
        );

        if ($isApproved) {
            $selectBox->setDisabled();
        }

        return $selectBox;
    }

    /**
     * @param $identifier
     * @param $ranking
     * @param $isApproved
     * @param bool $hasLabel
     *
     * @return SelectCompleter
     */
    private function getComplexExamGradeInput(
        $identifier,
        $ranking,
        $isApproved,
        bool $hasLabel = false
    ): SelectCompleter {
        $input = new SelectCompleter(
            'Data[ExamList][' . $identifier . '_' . $ranking . '][Grade]',
            $hasLabel ? 'Zensur' : '',
            '',
            $this->selectListGrades
        );

        if ($isApproved) {
            $input->setDisabled();
        }

        return $input;
    }

    /**
     * @param $identifier
     * @param $ranking
     * @param $isApproved
     * @param bool $hasLabel
     *
     * @return SelectBox
     */
    private function getComplexExamGradeTextSelect(
        $identifier,
        $ranking,
        $isApproved,
        bool $hasLabel = false
    ): SelectBox {
        $selectBox = new SelectBox('Data[ExamList][' . $identifier . '_' . $ranking . '][GradeText]',
            $hasLabel ? 'oder Zeugnistext' : '', array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

        if ($isApproved) {
            $selectBox->setDisabled();
        }

        return $selectBox;
    }

    /**
     * @param $panelName
     * @param $identifier
     * @param $inputName
     * @param $isApproved
     *
     * @return Panel
     */
    public function getPanel($panelName, $identifier, $inputName, $isApproved) : Panel
    {
        // da es public ist und auch von anderen Frontends drauf zugegriffen werden kann, können die privaten Properties nicht verwendet werden.

        // Grades
        $selectListGrades = array();
        $selectListGrades[-1] = '';
        for ($i = 1; $i < 6; $i++) {
            $selectListGrades[$i] = (string)($i);
        }
        $selectListGrades[6] = 6;

        // GradeTexts
        $selectListGradeTexts = array();
        if (($tblGradeTextList = Grade::useService()->getGradeTextAll())) {
            $selectListGradeTexts = $tblGradeTextList;
        }

        $input = new TextField('Data[InformationList][' . $identifier . ']', '', $inputName);
        $grade = new SelectCompleter('Data[InformationList][' . $identifier . '_Grade]', 'Zensur', '', $selectListGrades);
        $gradeText = new SelectBox('Data[InformationList][' . $identifier . '_GradeText]', 'oder Zeugnistext', array(TblGradeText::ATTR_NAME => $selectListGradeTexts));

        if ($isApproved) {
            $input->setDisabled();
            $grade->setDisabled();
            $gradeText->setDisabled();
        }

        return new Panel(
            $panelName,
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($input, 8),
                new LayoutColumn($grade, 2),
                new LayoutColumn($gradeText, 2)
            )))),
            Panel::PANEL_TYPE_INFO
        );
    }
}