<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekI;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

class FrontendLeave extends FrontendDiplomaTechnicalSchool
{
    /**
     * @param $YearId
     *
     * @return Stage
     */
    public function frontendLeaveSelectStudent($YearId = null): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler auswählen');
        Prepare::useFrontend()->setHeaderButtonList($Stage, View::LEAVE);

        $studentTable = array();
        $buttonList = array();

        $tblSelectYear = Term::useService()->getYearById($YearId);
        if (($tblYearAll = Term::useService()->getYearAll())) {
            if (!$tblSelectYear
                && ($tblYearByNowList = Term::useService()->getYearByNow())
            ) {
                $tblSelectYear = current($tblYearByNowList);
            }

            $tblYearAll = $this->getSorter($tblYearAll)->sortObjectBy('Name');
            /** @var TblYear $tblYear */
            foreach ($tblYearAll as $tblYear) {
                if ($tblSelectYear && $tblSelectYear->getId() == $tblYear->getId()) {
                    $icon = new Edit();
                    $text = new Info(new Bold($tblYear->getDisplayName()));
                } else {
                    $icon = null;
                    $text = $tblYear->getDisplayName();
                }

                $buttonList[] = new Standard($text, '/Education/Certificate/Prepare/Leave', $icon, array('YearId' => $tblYear->getId()));
            }
        }

        if ($tblSelectYear) {
            if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblSelectYear))) {
                foreach ($tblStudentEducationList as $tblStudentEducation) {
                    if (($tblPerson = $tblStudentEducation->getServiceTblPerson())
                        && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
                        && ($tblType->getName() == 'Mittelschule / Oberschule'
                            || $tblType->getName() == 'Gymnasium'
                            || $tblType->getName() == 'Berufsfachschule'
                            || $tblType->getName() == 'Fachschule'
                            || $tblType->getName() == 'Fachoberschule'
                            || $tblType->getName() == 'Förderschule'
                        )
                    ) {
                        $studentTable[] = array(
                            'Name' => $tblPerson->getLastFirstNameWithCallNameUnderline(),
                            'Division' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndYear($tblPerson, $tblSelectYear),
                            'Option' => new Standard(
                                '', '/Education/Certificate/Prepare/Leave/Student', new Select(),
                                array(
                                    'PersonId' => $tblPerson->getId(),
                                    'YearId' => $tblSelectYear->getId(),
                                ),
                                'Auswählen'
                            )
                        );
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn($buttonList)
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TableData(
                                $studentTable,
                                null,
                                array(
                                    'Name' => 'Name',
                                    'Division' => 'Kurs',
                                    'Option' => ''
                                ),
                                array(
                                    'order'      => array(
                                        array('0', 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 1),
                                        array('orderable' => false, 'width' => '1%', 'targets' => -1),
                                    ),
                                )
                            )
                        )
                    ))
                ), new Title(new Select() . ' Auswahl des Schülers'))
            ))
        );

        return $Stage;
    }

    /**
     * @param $PersonId
     * @param $YearId
     * @param $Data
     * @param $ChangeCertificate
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentTemplate($PersonId = null, $YearId = null, $Data = null, $ChangeCertificate = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $stage = new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler');
            $stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Leave', new ChevronLeft()));

            $tblType = false;
            $tblCourse = false;
            $tblCertificate = false;
            $subjectData = array();
            $tblLeaveStudent = false;

            $tblConsumer = Consumer::useService()->getConsumerBySession();

            if (($tblYear = Term::useService()->getYearById($YearId))
                && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            ) {
                $tblCourse = $tblStudentEducation->getServiceTblCourse();
                $tblType = $tblStudentEducation->getServiceTblSchoolType();
                $level = $tblStudentEducation->getLevel();

                // nachträgliche Änderung der Zeugnisvorlage
                if ($ChangeCertificate && $tblType) {
                    return $this->getSelectLeaveCertificateStage($tblPerson, $tblYear, $tblType, $tblCourse ?: null, $Data);
                }

                if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblYear))) {
                    $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();
                } else {
                    if ($tblType) {
                        if ($tblType->getShortName() == 'OS') {
                            // Herrnhut hat ein individuelles Abgangszeugnis
                            if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EZSH')) {
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('EZSH\EzshMsAbg');
                            } else {
                                // Auswahl der Zeugnisvorlage, da es mehrere gibt
                                return $this->getSelectLeaveCertificateStage($tblPerson, $tblYear, $tblType, $tblCourse ?: null, $Data);
                            }
                        } elseif ($tblType->getName() == 'Gymnasium') {
                            // Herrnhut hat ein individuelles Abgangszeugnis
                            if ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EZSH')
                                && $level == 10
                            ) {
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('EZSH\EzshGymAbg');
                            } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')
                                && $level <= 10
                            ) {
                                // HOGA hat ein individuelles Abgangszeugnis
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('HOGA\GymAbgSekI');
                            } elseif ($level <= 10) {
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbgSekI');
                            } else {
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbgSekII');
                                if ($tblCertificate) {
                                    $tblLeaveStudent = Prepare::useService()->createLeaveStudent(
                                        $tblPerson,
                                        $tblYear,
                                        $tblCertificate
                                    );
                                }
                            }
                        } elseif ($tblType->getName() == 'Berufsfachschule') {
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('BfsAbg');
                        } elseif ($tblType->getName() == 'Fachschule') {
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('FsAbg');
                        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA') && $tblType->getName() == 'Fachoberschule') {
                            // HOGA hat ein individuelles Abgangszeugnis
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('HOGA\FosAbg');
                        } elseif ($tblType->getName() == 'Förderschule') {
                            $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('FoesAbgGeistigeEntwicklung');
                        }
                    }
                }
            }

            if ($tblCertificate && $tblCertificate->getCertificate() == 'GymAbgSekII') {
                $layoutGroups = Prepare::useFrontend()->setLeaveContentForSekTwo(
                    $tblPerson,
                    $tblYear,
                    $stage,
                    $tblCertificate,
                    $tblLeaveStudent ?: null,
                    $tblType ?: null
                );
            } elseif ($tblCertificate
                && ($tblCertificate->getCertificate() == 'BfsAbg' || $tblCertificate->getCertificate() == 'FsAbg')
            ) {
                $layoutGroups = Prepare::useFrontend()->setLeaveContentForTechnicalSchool(
                    $tblPerson,
                    $tblYear,
                    $stage,
                    $Data,
                    $subjectData,
                    $tblCertificate,
                    $tblLeaveStudent ?: null,
                    $tblType ?: null,
                    $tblCertificate->getCertificate() == 'BfsAbg'
                );
            } else {
                $layoutGroups = $this->setLeaveContentForSekOne(
                    $tblPerson,
                    $tblYear,
                    $stage,
                    $Data,
                    $subjectData,
                    $tblCertificate ?: null,
                    $tblLeaveStudent ?: null,
                    $tblType ?: null,
                    $tblCourse ?: null
                );
            }

            $stage->setContent(
                new Layout($layoutGroups)
            );

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblType $tblType
     * @param TblCourse|null $tblCourse
     * @param null $Data
     *
     * @return Stage|string
     */
    private function getSelectLeaveCertificateStage(
        TblPerson $tblPerson,
        TblYear $tblYear,
        TblType $tblType,
        TblCourse $tblCourse = null,
        $Data = null
    ) {
        $stage = new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Zeugnisvorlage auswählen');

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblYear))
            && ($tblLeaveCertificate = $tblLeaveStudent->getServiceTblCertificate())
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['Certificate'] = $tblLeaveCertificate->getId();
            $global->savePost();

            $stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                'PersonId' => $tblPerson->getId(),
                'YearId' => $tblYear->getId()
            )));
        } else {
            $stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Leave', new ChevronLeft()));
        }

        $list = array();
        if (($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
            && ($tblLeaveCertificateList = Generator::useService()->getCertificateAllByType($tblCertificateType))
        ) {
            foreach ($tblLeaveCertificateList as $tblCertificate) {
                if (($tblTypeFromCertificate = $tblCertificate->getServiceTblSchoolType())
                    && $tblTypeFromCertificate->getId() == $tblType->getId()
                ) {
                    $list[] = $tblCertificate;
                }
            }
        }

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
        }

        if (!empty($list)) {
            $form = new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('Data[Certificate]', 'Zeugnisvorlage auswählen', array('{{ Name }} - {{ Description }}' => $list))
                ),
                new FormColumn(
                    new Primary('Speichern', new Save())
                )
            ))));

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 4),
                            new LayoutColumn(
                                new Panel(
                                    'Schuljahr',
                                    $tblYear->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 4),
                            new LayoutColumn(
                                new Panel(
                                    'Schulart',
                                    $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : ''),
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 4),
                        )),
                        ($support
                            ? new LayoutRow(new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO)))
                            : null
                        ),
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Well(Prepare::useService()->createLeaveStudentFromForm($form, $tblPerson, $tblYear, $Data))
                    )))
                ))
            );

            return $stage;
        } else {
            return $stage . new Danger('Keine Abgangszeugnisvorlagen gefunden!', new Exclamation());
        }
    }

    private function setLeaveContentForSekOne(
        TblPerson $tblPerson,
        TblYear $tblYear,
        Stage $stage,
        $Data,
        $subjectData,
        ?TblCertificate $tblCertificate,
        ?TblLeaveStudent $tblLeaveStudent,
        ?TblType $tblType,
        ?TblCourse $tblCourse
    ): array {
        $hasPreviewGrades = false;
        $isApproved = false;
        $hasMissingSubjects = false;
        $hasCertificateGrades = false;
        $tblAppointedDateTask = false;

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
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
                    'Zeugnis als Muster herunterladen'
                ));
            }

            $certificateDate = false;

            // Post setzen
            $isSetSubjectArea = false;
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
                if (($tblLeaveInformationList = Prepare::useService()->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
                    foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                        $value = $tblLeaveInformation->getValue();
                        // HOGA\FosAbg
                        if ($tblLeaveInformation->getField() == 'Job_Grade_Text') {
                            switch ($value) {
                                case 'bestanden': $value = 1; break;
                                case 'nicht bestanden': $value = 2; break;
                                default: $value = '';
                            }
                        }
                        if ($tblLeaveInformation->getField() == 'Exam_Text') {
                            switch ($value) {
                                case 'Die Abschlussprüfung wurde erstmalig nicht bestanden. Sie kann wiederholt werden.': $value = 1; break;
                                case 'Die Abschlussprüfung wurde endgültig nicht bestanden. Sie kann nicht wiederholt werden.': $value = 2; break;
                                default: $value = '';
                            }
                        }

                        $Global->POST['Data']['InformationList'][$tblLeaveInformation->getField()] = $value;

                        if ($tblLeaveInformation->getField() == 'CertificateDate' && $value != '') {
                            $certificateDate = new DateTime($value);
                        }

                        if ($tblLeaveInformation->getField() == 'SubjectArea') {
                            $isSetSubjectArea = true;
                        }
                    }
                }

                if (!$isSetSubjectArea
                    && $tblCertificate->getCertificate() == 'HOGA\FosAbg'
                    && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                    && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                    && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                ) {
                    $Global->POST['Data']['InformationList']['SubjectArea'] = $tblTechnicalSubjectArea->getName();
                }

                $Global->savePost();
            }

            if (!$certificateDate) {
                $certificateDate = new DateTime('now');
            }

            $hasCertificateGrades = $tblCertificate->getCertificate() != 'MsAbgGeistigeEntwicklung'
                && $tblCertificate->getCertificate() != 'FoesAbgGeistigeEntwicklung';
            if ($hasCertificateGrades) {
                // Grades
                $selectListGrades[-1] = '';
                for ($i = 1; $i < 6; $i++) {
                    $selectListGrades[$i] = (string)($i);
                }
                $selectListGrades[6] = 6;

                // Points
                $selectListPoints[-1] = '';
                for ($i = 0; $i < 16; $i++) {
                    $selectListPoints[$i] = (string)$i;
                }

                if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                    $selectList = $selectListPoints;
                } else {
                    $selectList = $selectListGrades;
                }

                if ($certificateDate) {
                    // Suche nach einem Stichtagsnotenauftrag der nicht älter als 30 Tage ist
                    if (($tblTaskList = Grade::useService()->getTaskListByStudentAndYear($tblPerson, $tblYear))) {
                        foreach ($tblTaskList as $tblTask) {
                            $dateInterval = new DateInterval('P30D');
                            $dateFrom = new DateTime($certificateDate->format('d.m.Y'));
                            $dateFrom = $dateFrom->sub($dateInterval);
                            $dateEnd = new DateTime($certificateDate->format('d.m.Y'));
                            $dateEnd = $dateEnd->add($dateInterval);
                            if (!$tblTask->getIsTypeBehavior()
                                && $tblTask->getDate() >= $dateFrom
                                && $tblTask->getDate() <= $dateEnd
                            ) {
                                $tblAppointedDateTask = $tblTask;
                                break;
                            }
                        }
                    }
                }

                if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYear))) {
                    $tabIndex = 0;
                    foreach ($tblSubjectList as $tblSubject) {
                        $gradeDisplayList = array();
                        $gradeValueList = array();
                        $contentAverage = '';
                        $taskGrade = '';

                        if (($tblGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject(
                            $tblPerson,
                            $tblYear,
                            $tblSubject
                        ))) {
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

                            $hasNoLeaveGrade = !$tblLeaveStudent || !Prepare::useService()->getLeaveGradeBy($tblLeaveStudent, $tblSubject);

                            $hasTaskGrade = false;
                            // Stichtagsnote ermitteln, falls vorhanden
                            if ($tblAppointedDateTask
                                && ($tblGradeFromTask = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblAppointedDateTask, $tblSubject))
                            ) {
                                if ($hasNoLeaveGrade) {
                                    if (($value = $tblGradeFromTask->getGrade())) {
                                        $hasPreviewGrades = true;
                                        $hasTaskGrade = true;
                                        $Global = $this->getGlobal();
                                        $Global->POST['Data']['Grades'][$tblSubject->getId()]['Grade'] = $value;
                                        $Global->savePost();
                                    } elseif (($gradeTextValue = $tblGradeFromTask->getTblGradeText())) {
                                        $hasPreviewGrades = true;
                                        $hasTaskGrade = true;
                                        $Global = $this->getGlobal();
                                        $Global->POST['Data']['Grades'][$tblSubject->getId()]['GradeText'] = $gradeTextValue;
                                        $Global->savePost();
                                    }
                                }

                                $taskGrade = $tblGradeFromTask->getDisplayGrade();
                            }

                            /**
                             * Average
                             */
                            $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
                            list ($average, $scoreRuleText, $error) = Grade::useService()->getCalcStudentAverage($tblPerson, $tblYear, $gradeValueList, $tblScoreRule ?: null);
                            $contentAverage = '&#216; ' . Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);
                            // Zensuren voreintragen, wenn noch keine vergeben ist
                            if (($average || $average === (float)0) && $hasNoLeaveGrade && !$hasTaskGrade) {
                                $hasPreviewGrades = true;
                                $Global = $this->getGlobal();
                                $Global->POST['Data']['Grades'][$tblSubject->getId()]['Grade'] = str_replace('.', ',', round($average, 0));
                                $Global->savePost();
                            }
                        }

                        $selectComplete = (new SelectCompleter('Data[Grades][' . $tblSubject->getId() . '][Grade]', '', '', $selectList))
                            ->setTabIndex($tabIndex++);
                        if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                            $selectComplete->setDisabled();
                        }

                        // Zeugnistext
                        if (($tblGradeTextList = Grade::useService()->getGradeTextAll())) {
                            $gradeText = new SelectBox('Data[Grades][' . $tblSubject->getId() . '][GradeText]', '', array(TblGradeText::ATTR_NAME => $tblGradeTextList));

                            if ($tblLeaveStudent && $tblLeaveStudent->isApproved()) {
                                $gradeText->setDisabled();
                            }
                        } else {
                            $gradeText = '';
                        }

                        if (!Generator::useService()->getCertificateSubjectBySubject($tblCertificate, $tblSubject)) {
                            $hasMissingSubjects = true;
                            $subjectName = new \SPHERE\Common\Frontend\Text\Repository\Warning($tblSubject->getDisplayName() . ' ' . new Ban());
                        } else {
                            $subjectName = $tblSubject->getDisplayName();
                        }

                        $subjectData[$tblSubject->getAcronym()] = array(
                            'SubjectName' => $subjectName,
                            'GradeList' => implode(' | ', $gradeDisplayList),
                            'Average' => $contentAverage,
                            'TaskGrade' => $taskGrade,
                            'Grade' => $selectComplete,
                            'GradeText' => $gradeText
                        );
                    }
                }
            }
        }

        if (!$isApproved && $tblType && $tblType->getShortName() == 'OS') {
            $canChangeCertificate = true;
        } else {
            $canChangeCertificate = false;
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
                        'Schuljahr',
                        $tblYear->getDisplayName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schulart',
                        $tblType
                            ? $tblType->getName() . ($tblCourse ? ' - ' . $tblCourse->getName() : '')
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
                ? new LayoutRow(new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO)))
                : null
            ),
            ($hasCertificateGrades && $hasMissingSubjects
                ? new LayoutRow(new LayoutColumn(new Warning(
                    'Es sind nicht alle Fächer auf der Zeugnisvorlage eingestellt.', new Exclamation()
                )))
                : null
            ),
            ($hasCertificateGrades && $hasPreviewGrades
                ? new LayoutRow(new LayoutColumn(new Warning(
                    'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                )))
                : null
            )
        ));

        if ($tblCertificate) {
            // DivisionTeacher
            $divisionTeacherList = array();
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                if ($tblStudentEducation->getTblDivision()
                    && ($tblPersonList = $tblStudentEducation->getTblDivision()->getDivisionTeacherList())
                ) {
                    foreach ($tblPersonList as $tblPersonTeacher) {
                        $divisionTeacherList[$tblPersonTeacher->getId()] = $tblPersonTeacher->getFullName();
                    }
                }
                if ($tblStudentEducation->getTblCoreGroup()
                    && ($tblPersonList = $tblStudentEducation->getTblCoreGroup()->getDivisionTeacherList())
                ) {
                    foreach ($tblPersonList as $tblPersonTeacher) {
                        $divisionTeacherList[$tblPersonTeacher->getId()] = $tblPersonTeacher->getFullName();
                    }
                }
            }

            if ($tblAppointedDateTask) {
                $columns = array(
                    'SubjectName' => 'Fach',
                    'GradeList' => 'Noten',
                    'Average' => '&#216;',
                    'TaskGrade' => new ToolTip('SN', 'Stichtagsnote vom ' . ($tblAppointedDateTask->getDateString() ?: '-')),
                    'Grade' => 'Zensur',
                    'GradeText' => 'oder Zeugnistext'
                );
            } else {
                $columns = array(
                    'SubjectName' => 'Fach',
                    'GradeList' => 'Noten',
                    'Average' => '&#216;',
                    'Grade' => 'Zensur',
                    'GradeText' => 'oder Zeugnistext'
                );
            }

            if (!empty($subjectData)) {
                ksort($subjectData);
                $subjectTable = new TableData(
                    $subjectData,
                    null,
                    $columns,
                    null
                );
            } else {
                $subjectTable = false;
            }

            $datePicker = (new DatePicker('Data[InformationList][CertificateDate]', '', 'Zeugnisdatum',
                new Calendar()))->setRequired();
            if ($tblCertificate->getCertificate() == 'EZSH\EzshGymAbg') {
                $arrangementTextArea = new TextArea('Data[InformationList][Arrangement]', '', 'Besonderes Engagement an den Zinzendorfschulen');
                $remarkTextArea = new TextArea('Data[InformationList][RemarkWithoutTeam]', '', 'Bemerkungen');

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $arrangementTextArea->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $arrangementTextArea,
                    $remarkTextArea
                );
            } elseif ($tblCertificate->getCertificate() == 'EZSH\EzshMsAbg') {
                $remarkTextArea = new TextArea('Data[InformationList][RemarkWithoutTeam]', '', 'Bemerkungen');

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $remarkTextArea
                );
            } elseif ($tblCertificate->getCertificate() == 'FoesAbgGeistigeEntwicklung') {
                $remarkTextArea = new TextArea('Data[InformationList][RemarkWithoutTeam]', '', 'Bemerkungen');

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $remarkTextArea
                );
            } else {
                if ($tblCertificate->getCertificate() == 'MsAbgGeistigeEntwicklung') {
                    $remarkTextArea = new TextArea('Data[InformationList][Support]', '', 'Inklusive Unterrichtung');
                } else {
                    $remarkTextArea = new TextArea('Data[InformationList][Remark]', '', 'Bemerkungen');
                }

                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                }
                $otherInformationList = array(
                    $datePicker,
                    $remarkTextArea
                );
            }

            if ($tblCertificate->getCertificate() == 'GymAbgSekI'
                || $tblCertificate->getCertificate() == 'EZSH\EzshGymAbg'
                || $tblCertificate->getCertificate() == 'HOGA\GymAbgSekI'
            ) {
                $radio1 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 7 Absatz 7 Satz 2 des Sächsischen Schulgesetzes mit der Versetzung von Klassenstufe 10 nach
                     Jahrgangsstufe 11 des Gymnasiums einen dem Realschulabschluss gleichgestellten mittleren Schulabschluss erworben.',
                    GymAbgSekI::COURSE_RS
                ));
                $radio2 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 7 Absatz 7 Satz 1 des Sächsischen Schulgesetzes mit der Versetzung von Klassenstufe 9 nach
                     Klassenstufe 10 des Gymnasiums einen dem Hauptschulabschluss gleichgestellten Schulabschluss erworben.',
                    GymAbgSekI::COURSE_HS
                ));
                if ($isApproved) {
                    $radio1->setDisabled();
                    $radio2->setDisabled();
                }
                $otherInformationList[] = new Panel(
                    'Gleichgestellter Schulabschluss',
                    array($radio1, $radio2),
                    Panel::PANEL_TYPE_DEFAULT
                );
            } elseif ($tblCertificate->getCertificate() == 'MsAbg'
                || $tblCertificate->getCertificate() == 'EZSH\EzshMsAbg'
                || $tblCertificate->getCertificate() == 'HOGA\MsAbg'
            ) {
                $radio1 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 6 Absatz 1 Satz 7 des Sächsischen Schulgesetzes mit der Versetzung in die Klassenstufe 10
                     des Realschulbildungsganges einen dem Hauptschulabschluss gleichgestellten Abschluss erworben',
                    GymAbgSekI::COURSE_HS
                ));
                $radio2 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 27 Absatz 9 Satz 3 der Schulordnung Ober- und Abendoberschulen mit der Versetzung in die
                     Klassenstufe 10 des Realschulbildungsganges und der erfolgreichen Teilnahme an der Prüfung zum Erwerb des Hauptschulabschlusses
                     den qualifizierenden Hauptschulabschluss erworben.',
                    GymAbgSekI::COURSE_HSQ
                ));
                $radio3 = (new RadioBox(
                    'Data[InformationList][EqualGraduation]',
                    'gemäß § 63 Absatz 3 Nummer 3 der Schulordnung Ober- und Abendoberschulen einen dem Abschluss im Förderschwerpunkt Lernen gemäß 
                     § 34a Absatz 1 der Schulordnung Förderschulen gleichgestellten Abschluss erworben.',
                    GymAbgSekI::COURSE_LERNEN
                ));
                if ($isApproved) {
                    $radio1->setDisabled();
                    $radio2->setDisabled();
                    $radio3->setDisabled();
                }
                $otherInformationList[] = new Panel(
                    'Gleichgestellter Schulabschluss',
                    array($radio1, $radio2, $radio3),
                    Panel::PANEL_TYPE_DEFAULT
                );
            }

            $headmasterNameTextField = new TextField('Data[InformationList][HeadmasterName]', '',
                'Name des/der Schulleiters/in');
            $radioSex1 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Männlich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                    ? $tblCommonGender->getId() : 0));
            $radioSex2 = (new RadioBox('Data[InformationList][HeadmasterGender]', 'Weiblich',
                ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                    ? $tblCommonGender->getId() : 0));
            $teacherSelectBox = new SelectBox('Data[InformationList][DivisionTeacher]', 'Klassenlehrer(in):',
                $divisionTeacherList);
            if ($isApproved) {
                $headmasterNameTextField->setDisabled();
                $radioSex1->setDisabled();
                $radioSex2->setDisabled();
                $teacherSelectBox->setDisabled();
            }

            // Facharbeit
            if ($tblCertificate->getCertificate() == 'HOGA\FosAbg') {
                $frontend = Prepare::useFrontend();
//                $panelJob = $frontend->getPanelWithoutInput('Fachpraktischer Teil der Ausbildung', 'Job', $isApproved);
                $selectBoxJob = new SelectBox('Data[InformationList][Job_Grade_Text]', 'Fachpraktischer Teil der Ausbildung', array(
                    1 => "bestanden",
                    2 => "nicht bestanden"
                ));
                if ($isApproved) {
                    $selectBoxJob->setDisabled();
                }
                $panelJob = new Panel(
                    'Fachpraktischer Teil der Ausbildung',
                    $selectBoxJob,
                    Panel::PANEL_TYPE_INFO
                );
                $panelSkilledWork = $frontend->getPanel('Facharbeit', 'SkilledWork', 'Thema', $isApproved);

                $subjectAreaInput = (new TextField('Data[InformationList][SubjectArea]', '', 'Fachrichtung'));
                $educationDateFrom = new DatePicker('Data[InformationList][EducationDateFrom]', '', 'Ausbildung Datum vom');
                if ($isApproved) {
                    $subjectAreaInput->setDisabled();
                    $educationDateFrom->setDisabled();
                }
                $panelEducation = new Panel(
                    'Ausbildung',
                    array(
                        $educationDateFrom,
                        $subjectAreaInput
                    ),
                    Panel::PANEL_TYPE_INFO
                );

                $selectBoxExam = new SelectBox('Data[InformationList][Exam_Text]', 'Abschlussprüfung', array(
                    1 => 'Die Abschlussprüfung wurde erstmalig nicht bestanden. Sie kann wiederholt werden.',
                    2 => 'Die Abschlussprüfung wurde endgültig nicht bestanden. Sie kann nicht wiederholt werden.'
                ));
                if ($isApproved) {
                    $selectBoxExam->setDisabled();
                }
                $panelExam = new Panel(
                    'Abschlussprüfung',
                    $selectBoxExam,
                    Panel::PANEL_TYPE_INFO
                );
            } else {
                $panelJob = false;
                $panelSkilledWork = false;
                $panelEducation = false;
                $panelExam = false;
            }

            $form = new Form(new FormGroup(array(
                $subjectTable ? new FormRow(new FormColumn($subjectTable)) : null,
                $panelEducation ? new FormRow(new FormColumn($panelEducation)) : null,
                $panelJob ? new FormRow(new FormColumn($panelJob)) : null,
                $panelSkilledWork ? new FormRow(new FormColumn($panelSkilledWork)) : null,
                $panelExam ? new FormRow(new FormColumn($panelExam)) : null,
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
                            'Unterzeichner - Schulleiter',
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
                        , 6),
                    $tblCertificate->getCertificate() != 'GymAbgSekII'
                        ? new FormColumn(
                        new Panel(
                            'Unterzeichner - Klassenlehrer',
                            $teacherSelectBox,
                            Panel::PANEL_TYPE_INFO
                        )
                        , 6)
                        : null
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
}