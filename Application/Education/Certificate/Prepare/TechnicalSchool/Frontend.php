<?php

namespace SPHERE\Application\Education\Certificate\Prepare\TechnicalSchool;

use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradesAllYears;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\TechnicalSchool
 */
class Frontend extends Extension
{
    private $subjectList = array();
    private $selectListGrades = array();
    private $selectListGradeTexts = array();

    public function __construct()
    {
        // Grades
        $this->selectListGrades[-1] = '';
        for ($i = 1; $i < 6; $i++) {
            $this->selectListGrades[$i] = (string)($i);
        }
        $this->selectListGrades[6] = 6;

        // GradeTexts
        if (($tblGradeTextList = Gradebook::useService()->getGradeTextAll())) {
            $this->selectListGradeTexts = $tblGradeTextList;
        }
    }

    /**
     * @param TblCertificate|null $tblCertificate
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param TblDivision|null $tblDivision
     * @param TblPerson|null $tblPerson
     * @param $Data
     * @param Stage $stage
     * @param $subjectData
     * @param TblType|null $tblType
     * @param bool $isBfs
     *
     * @return mixed
     */
    protected function setLeaveContentForTechnicalSchool(
        TblCertificate $tblCertificate = null,
        TblLeaveStudent $tblLeaveStudent = null,
        TblDivision $tblDivision = null,
        TblPerson $tblPerson = null,
        $Data,
        Stage $stage,
        $subjectData,
        TblType $tblType = null,
        $isBfs = true
    ) {

        $hasPreviewGrades = false;
        $isApproved = false;
        $hasMissingSubjects = false;

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
        if (($tblGradeTextList = Gradebook::useService()->getGradeTextAll())) {
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
                            if (($tblGradeText = Gradebook::useService()->getGradeTextByName($tblLeaveGrade->getGrade()))) {
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
                            && ($tblGradeText = Gradebook::useService()->getGradeTextByName($value))
                        ) {
                            $value = $tblGradeText->getId();
                        }

                        if ($field == 'SubjectArea') {
                            $isSetSubjectArea = true;
                        }

                        $Global->POST['Data']['InformationList'][$field] = $value;
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

                        if (($tblGradeText = Gradebook::useService()->getGradeTextByName($grade))) {
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

            if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
                && $tblDivision
                && ($tblYear = $tblDivision->getServiceTblYear())
                && ($tblDivisionSubjectListByPerson = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                    $tblPerson, $tblYear))
            ) {
                $tabIndex = 0;
                foreach ($tblDivisionSubjectListByPerson as $tblDivisionSubject) {
                    if (($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                        && ($tblSubjectItem = $tblDivisionSubject->getServiceTblSubject())
                    ) {
                        /*
                        * Calc Average over all Years
                        */
                        $average = Gradebook::useService()->calcStudentGrade(
                            $tblPerson,
                            $tblDivisionItem,
                            $tblSubjectItem,
                            $tblTestType,
                            null,
                            null,
                            null,
                            false,
                            false,
                            Gradebook::useService()->getSubjectGradesByAllYears(
                                $tblPerson,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType
                            )
                        );

                        if (is_array($average)) {
                            $average = '';
                        } else {
                            $posStart = strpos($average, '(');
                            if ($posStart !== false) {
                                $average = substr($average, 0, $posStart);
                            }

                            // Zensuren voreintragen, wenn noch keine vergeben ist
                            if (($average || $average === (float)0) && (!$tblLeaveStudent
                                    || !Prepare::useService()->getLeaveGradeBy($tblLeaveStudent,
                                        $tblSubjectItem))
                            ) {
                                $hasPreviewGrades = true;
                                $Global = $this->getGlobal();
                                $Global->POST['Data']['Grades'][$tblSubjectItem->getId()]['Grade'] =
                                    str_replace('.', ',', round($average, 0));
                                $Global->savePost();
                            }
                        }

                        $gradeList = ApiGradesAllYears::receiverModal()
                            . (new Standard('', ApiGradesAllYears::getEndpoint(), new EyeOpen()))
                                ->ajaxPipelineOnClick(ApiGradesAllYears::pipelineOpenAllGradesModal(
                                    $tblDivisionItem->getId(), $tblSubjectItem->getId(), $tblPerson->getId()
                                ));

                        $selectComplete = (new SelectCompleter('Data[Grades][' . $tblSubjectItem->getId() . '][Grade]',
                            '', '', $this->selectListGrades))
                            ->setTabIndex($tabIndex++);
                        if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                            $selectComplete->setDisabled();
                        }

                        // Zeugnistext
                        $gradeText = new SelectBox('Data[Grades][' . $tblSubjectItem->getId() . '][GradeText]',
                            '', array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

                        if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                            $gradeText->setDisabled();
                        }

                        $subjectCategory = '';
                        if (!($tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject(
                            $tblCertificate,
                            $tblSubjectItem,
                            $tblTechnicalCourse ? $tblTechnicalCourse : null
                        ))) {
                            $hasMissingSubjects = true;
                            $subjectCategory = '';
                            $subjectName = new \SPHERE\Common\Frontend\Text\Repository\Warning($tblSubjectItem->getName() . ' ' . new Ban());
                        } else {
                            $subjectName = $tblSubjectItem->getName();
                            $ranking = intval($tblCertificateSubject->getRanking());
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

                        $subjectData[$tblSubjectItem->getAcronym()] = array(
                            'SubjectName' => $subjectName,
                            'SubjectCategory' => $subjectCategory,
                            'GradeList' => $gradeList,
                            'Average' => $average,
                            'Grade' => $selectComplete,
                            'GradeText' => $gradeText
                        );
                    }
                }
            }
        }

        $layoutGroups[] = new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Schüler',
                        $tblPerson->getLastFirstName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Klasse',
                        $tblDivision
                            ? $tblDivision->getDisplayName()
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine aktuelle Klasse zum Schüler gefunden!'),
                        $tblDivision ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
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
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                            . ' Keine Zeugnisvorlage verfügbar!'),
                        $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3)
            )),
            ($support
                ? new LayoutRow(new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO)))
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
            // Vorsitzende/r des Prüfungsausschusses aller Lehrer -> oder kann dies eingeschränkt werden?
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
                        'GradeList' => 'Vornoten (' . TblTask::ALL_YEARS_PERIOD_Name . ')',
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

                if ($isApproved) {
                    $destinationInput->setDisabled();

                    $operationTimeTotal->setDisabled();
                    $operation1Input->setDisabled();
                    $operationTime1Input->setDisabled();
                    $operation2Input->setDisabled();
                    $operationTime2Input->setDisabled();
                    $operation3Input->setDisabled();
                    $operationTime3Input->setDisabled();
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

                $panelPraxis = new Panel(
                    'Berufspraktische Ausbildung',
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn($operationTimeTotal, 12)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn($operation1Input, 6),
                            new LayoutColumn($operationTime1Input, 6)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn($operation2Input, 6),
                            new LayoutColumn($operationTime2Input, 6)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn($operation3Input, 6),
                            new LayoutColumn($operationTime3Input, 6)
                        )),
                    ))),
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
                $this->setSubjectListByTechnicalCourse($tblCertificate, $tblTechnicalCourse ? $tblTechnicalCourse : null);
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

            $headmasterNameTextField = new TextField('Data[InformationList][HeadmasterName]', '',
                'Name des/der Schulleiters/in');
            $radioSex1 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Männlich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                    ? $tblCommonGender->getId() : 0));
            $radioSex2 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Weiblich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                    ? $tblCommonGender->getId() : 0));
            $teacherSelectBox = new SelectBox('Data[InformationList][DivisionTeacher]', 'Vorsitzende/r des Prüfungsausschusses',
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
                            'Unterzeichner - Vorsitzende/r des Prüfungsausschusses',
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
                    Prepare::useService()->updateLeaveContent($form, $tblPerson, $tblDivision, $tblCertificate, $Data)
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
        $hasLabel = false
    ) {
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
     * @param $hasLabel
     *
     * @return SelectCompleter
     */
    private function getComplexExamGradeInput(
        $identifier,
        $ranking,
        $isApproved,
        $hasLabel = false
    ) {
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
     * @param $hasLabel
     *
     * @return SelectBox
     */
    private function getComplexExamGradeTextSelect(
        $identifier,
        $ranking,
        $isApproved,
        $hasLabel = false
    ) {
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
        $input = new TextField('Data[InformationList][' . $identifier . ']', '', $inputName);
        $grade = new SelectCompleter('Data[InformationList][' . $identifier . '_Grade]', 'Zensur', '', $this->selectListGrades);
        $gradeText = new SelectBox('Data[InformationList][' . $identifier . '_GradeText]', 'oder Zeugnistext',
            array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

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

    /**
     * @param $panelName
     * @param $identifier
     * @param $isApproved
     *
     * @return Panel
     */
    public function getPanelWithoutInput($panelName, $identifier, $isApproved) : Panel
    {
        $grade = new SelectCompleter('Data[InformationList][' . $identifier . '_Grade]', 'Zensur', '', $this->selectListGrades);
        $gradeText = new SelectBox('Data[InformationList][' . $identifier . '_GradeText]', 'oder Zeugnistext',
            array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

        if ($isApproved) {
            $grade->setDisabled();
            $gradeText->setDisabled();
        }

        return new Panel(
            $panelName,
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($grade, 6),
                new LayoutColumn($gradeText, 6)
            )))),
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     */
    private function setSubjectListByTechnicalCourse(TblCertificate $tblCertificate, TblTechnicalCourse $tblTechnicalCourse = null)
    {
        $this->subjectList = array();
        if (($tblCertificateSubjectList = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse))) {
            foreach ($tblCertificateSubjectList as $tblCertificateSubject) {
                if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())
                    && $tblCertificateSubject->getRanking() > 4
                ) {
                    $this->subjectList[$tblSubject->getId()] = $tblSubject;
                }
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $CurrentTab
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaTechnicalSetting(
        $PrepareId = null,
        $GroupId = null,
        $CurrentTab = null,
        $Data = null
    ) {
        if ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId)) {
            // Grades
            $this->selectListGrades = array();
            $this->selectListGrades[-1] = '';
            for ($i = 1; $i < 6; $i++) {
                $this->selectListGrades[$i] = (string)($i);
            }
            $this->selectListGrades[6] = 6;

            // GradeTexts
            $this->selectListGradeTexts = array();
            if (($tblGradeTextList = Gradebook::useService()->getGradeTextAll())) {
                $this->selectListGradeTexts = $tblGradeTextList;
            }

            $tblDivision = false;
            $tblGroup = false;
            $tblPrepareList = false;
            $description = '';
            $tblType = false;
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())) {

                    $description = 'Klasse ' . $tblDivision->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);

                    $tblType = $tblDivision->getType();
                }
            }

            if ($CurrentTab == null) {
                $CurrentTab = 1;
            }

            $countInformationPages = 1;
            $additionalRemarkFhrTab = false;
            // Aufteilung der Sonstigen Informationen auf mehrere Seiten
            list($informationPageList) = Prepare::useService()->getCertificateInformationPages($tblPrepareList, $tblGroup, false);
            if (!empty($informationPageList)) {
                foreach ($informationPageList as $certificateId => $pageList) {
                    $countByCertificate = count($pageList);
                    $countByCertificate++;
                    if ($countByCertificate > $countInformationPages) {
                        $countInformationPages = $countByCertificate;
                    }

                    if (!$additionalRemarkFhrTab) {
                        foreach ($pageList as $page => $fieldList) {
                            if (isset($fieldList['AdditionalRemarkFhr'])) {
                                $additionalRemarkFhrTab = $page;

                                break;
                            }
                        }
                    }
                }
            }

            $tabs = $this->getTabsByType($tblType ? $tblType : null, $countInformationPages);

            $Stage = new Stage('Zeugnisvorbereitung', isset($tabs[$CurrentTab])
                ? $tabs[$CurrentTab]['StageName'] . ' festlegen' : '');

            if ($tblGroup) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'GroupId' => $tblGroup->getId(),
                        'Route' => 'Diploma'
                    )
                ));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision ? $tblDivision->getId() : 0,
                        'Route' => 'Diploma'
                    )
                ));
            }

            $buttonList = $this->createExamsTechnicalButtonList($tblPrepare, $tblGroup ? $tblGroup : null, $CurrentTab, $tabs);

            $studentTable = array();
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungsgang',
            );

            if (isset($tabs[$CurrentTab])) {
                $tabIdentifier = $tabs[$CurrentTab]['Identifier'];
                if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4'
                    || $tabIdentifier == 'P'
                ) {
                    // Komplexprüfungen
                    $columnTable['S1'] = '1. Fach';
                    $columnTable['S2'] = '2. Fach';
                    $columnTable['Grade'] = 'Zensur';
                    $columnTable['GradeText'] = 'oder Zeugnistext';
                }
            } else {
                $tabIdentifier = '';
            }

            $CertificateList = array();
            foreach ($tblPrepareList as $tblPrepareItem) {
                if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                    && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                ) {
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);

                            $studentTable[$tblPerson->getId()] = array(
                                'Number' => (count($studentTable) + 1) . ' '
                                    . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                        ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                            'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                        : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                                'Name' => $tblPerson->getLastFirstName()
                                    . ($tblGroup ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                            );
                            $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                            $studentTable[$tblPerson->getId()]['Course'] = $courseName ? $courseName
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                    . ' Kein Bildungsgang hinterlegt');

                            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);
                            if ($tblPrepareStudent) {
                                if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4'
                                ) {
                                    // Schriftliche Komplexprüfung
                                    $ranking = substr($tabIdentifier, 1, 1);
                                    $this->setPrepareComplexExamContent($studentTable, $tblPerson, $tblPrepareStudent,
                                        TblPrepareComplexExam::IDENTIFIER_WRITTEN, $ranking);
                                } elseif ($tabIdentifier == 'P') {
                                    // Praktische Komplexprüfung
                                    $this->setPrepareComplexExamContent($studentTable, $tblPerson, $tblPrepareStudent,
                                        TblPrepareComplexExam::IDENTIFIER_PRAXIS, 1);
                                } else {
                                    // Sonstige Informationen
                                    $page = null;
                                    if (strlen($tabIdentifier) == 2) {
                                        $page = intval(substr($tabIdentifier, 1, 1));
                                    }

                                    $this->getTemplateInformation(
                                        $tblPrepare, $tblPerson, $studentTable, $columnTable, $Data, $CertificateList,
                                        $page == 1 ? null : $page, $informationPageList, $GroupId
                                    );
                                }
                            }

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
                }
            }

            $tableData = new TableData($studentTable, null, $columnTable,
                array(
                    "columnDefs" => array(
                        array(
                            "width" => "18px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
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
                ),
                true
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

            $nextTab = $CurrentTab + 1;
            if (!isset($tabs[$nextTab])) {
                $nextTab = null;
            }

            if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4'
                || $tabIdentifier == 'P'
            ) {
                // Komplexprüfungen
                $service = Prepare::useService()->updatePrepareComplexExamList($form, $tblPrepare,
                    $tblGroup ? $tblGroup : null, $Data, $nextTab);
            } else {
                // Sonstige Informationen
                $hasAdditionalRemarkFhr = $additionalRemarkFhrTab && $tabIdentifier == ('I' . $additionalRemarkFhrTab);
                $service = Prepare::useService()->updateTechnicalDiplomaPrepareInformationList($form, $tblPrepare,
                    $tblGroup ? $tblGroup : null, $Data, $CertificateList, $nextTab, $hasAdditionalRemarkFhr);
            }

            $Stage->setContent(
                ApiPrepare::receiverModal()
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
                                    $tblGroup ? 'Gruppe' : 'Klasse',
                                    $description,
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn($buttonList),
                        )),
                    )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
//                                    !$tblTestList
//                                        ? new Warning('Die aktuelle Klasse ist nicht in dem ausgewählten Stichttagsnotenauftrag enthalten.'
//                                        , new Exclamation())
//                                        : null,
                                    $service
                                ))
                            ))
                        ))
                ))
            );

            return $Stage;
        }

        $Stage = new Stage('Zeugnisvorbereitung', 'Einstellungen');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     * @param null $currentTab
     * @param array $tabs
     *
     * @return Standard[]
     */
    private function createExamsTechnicalButtonList(
        TblPrepareCertificate $tblPrepare,
        TblGroup $tblGroup = null,
        $currentTab = null,
        $tabs = array()
    ) {
        $buttonList = array();
        foreach ($tabs as $key => $value) {
            if ($currentTab == $key) {
                $icon = new Edit();
                $name = new Info(new Bold($value['TabName']));
            } else {
                $icon = null;
                $name = $value['TabName'];
            }

            $buttonList[] = new Standard($name,
                '/Education/Certificate/Prepare/Prepare/Diploma/Technical/Setting', $icon, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                    'CurrentTab' => $key
                )
            );
        }

        return $buttonList;
    }

    /**
     * @param TblType|null $tblType
     * @param int $countInformationPages
     *
     * @return array
     */
    public function getTabsByType(TblType $tblType = null, $countInformationPages = 1)
    {
        $tabs = array();
        $count = 1;
        if ($tblType->getName() == 'Fachschule') {
            $tabs = array(
                $count++ => array(
                    'Identifier' => 'K1',
                    'TabName' => 'K1',
                    'StageName' => 'Schriftliche Komplexprüfung 1',
                ),
                $count++ => array(
                    'Identifier' => 'K2',
                    'TabName' => 'K2',
                    'StageName' => 'Schriftliche Komplexprüfung 2',
                ),
                $count++ => array(
                    'Identifier' => 'K3',
                    'TabName' => 'K3',
                    'StageName' => 'Schriftliche Komplexprüfung 3',
                ),
                $count++ => array(
                    'Identifier' => 'K4',
                    'TabName' => 'K4',
                    'StageName' => 'Schriftliche Komplexprüfung 4',
                ),
                $count++ => array(
                    'Identifier' => 'P',
                    'TabName' => 'P',
                    'StageName' => 'Praktische Komplexprüfung',
                )
            );
        }

        // Sonstige Informationen
        $informationTabName = $tblType->getName() == 'Fachschule' ?  'Sonstige Info' : 'Sonstige Informationen';
        for ($i = 1; $i <= $countInformationPages; $i++) {
            $tabs[$count++] = array(
                'Identifier' => 'I' . (string) $i,
                'TabName' => $informationTabName . ($i > 1 ? ' (Seite ' . $i . ')' : ''),
                'StageName' => 'Sonstige Informationen' . ($i > 1 ? ' (Seite ' . $i . ')' : ''),
            );
        }

        return $tabs;
    }

    /**
     * @param $studentTable
     * @param TblPerson $tblPerson
     * @param TblPrepareStudent $tblPrepareStudent
     * @param $identifier
     * @param $ranking
     */
    private function setPrepareComplexExamContent(
        &$studentTable,
        TblPerson $tblPerson,
        TblPrepareStudent $tblPrepareStudent,
        $identifier,
        $ranking
    ) {
        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblCertificate->getCertificate() == 'FsAbs' || $tblCertificate->getCertificate() == 'FsAbsFhr')
        ) {
            /*
             * Post setzen
             */
            if (($tblPrepareComplexExam = Prepare::useService()->getPrepareComplexExamBy($tblPrepareStudent, $identifier, $ranking))) {
                $global = $this->getGlobal();
                if (($tblFirstSubject = $tblPrepareComplexExam->getServiceTblFirstSubject())) {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['S1'] = $tblFirstSubject->getId();
                }
                if (($tblSecondSubject = $tblPrepareComplexExam->getServiceTblSecondSubject())) {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['S2'] = $tblSecondSubject->getId();
                }
                $grade = $tblPrepareComplexExam->getGrade();
                if (($tblGradeText = Gradebook::useService()->getGradeTextByName($grade))) {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['GradeText'] = $tblGradeText->getId();
                } else {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['Grade'] = $grade;
                }

                $global->savePost();
            }

            $tblTechnicalCourse = Student::useService()->getTechnicalCourseByPerson($tblPerson);
            $this->setSubjectListByTechnicalCourse($tblCertificate, $tblTechnicalCourse ? $tblTechnicalCourse : null);

            $preName = 'Data[' . $tblPrepareStudent->getId() . '][' . $identifier . '_' . $ranking . ']';

            $firstSubjectSelectBox = new SelectBox($preName . '[S1]', '',
                array('{{ TechnicalAcronymForCertificateFromName }}' => $this->subjectList));
            $secondSubjectSelectBox = new SelectBox($preName . '[S2]', '',
                array('{{ TechnicalAcronymForCertificateFromName }}' => $this->subjectList));
            $gradeInput = new SelectCompleter($preName . '[Grade]', '', '', $this->selectListGrades);
            $gradeTextSelectBox = new SelectBox($preName . '[GradeText]', '',
                array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

            if ($tblPrepareStudent->isApproved()) {
                $firstSubjectSelectBox->setDisabled();
                $secondSubjectSelectBox->setDisabled();
                $gradeInput->setDisabled();
                $gradeTextSelectBox->setDisabled();
            }

            $studentTable[$tblPerson->getId()]['S1'] = $firstSubjectSelectBox;
            $studentTable[$tblPerson->getId()]['S2'] = $secondSubjectSelectBox;
            $studentTable[$tblPerson->getId()]['Grade'] = $gradeInput;
            $studentTable[$tblPerson->getId()]['GradeText'] = $gradeTextSelectBox;
        }
    }
}