<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
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
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

abstract class FrontendPreview extends FrontendLeaveTechnicalSchool
{
    /**
     * @param $PrepareId
     * @param string $Route
     *
     * @return Stage|string
     */
    public function frontendPreparePreview(
        $PrepareId = null,
        string $Route = 'Teacher'
    ) {
        $Stage = new Stage('Zeugnisvorbereitung', 'Vorschau');
        $isDiploma = $Route == 'Diploma';

        $countBehavior = 0;
        $tblBehaviorTask = false;
        $studentTable = array();
        $isSekII = false;
        $hasColumnCertificate = false;
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(), array(
                    'DivisionId' => $tblDivisionCourse->getId(),
                    'Route' => $Route
                )
            ));

            $useClassRegisterForAbsence = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
                && $tblSetting->getValue();

            if ($tblPrepare->getServiceTblBehaviorTask()) {
                $tblBehaviorTask = $tblPrepare->getServiceTblBehaviorTask();
            } elseif (($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'UseMultipleBehaviorTasks'))
                && $tblSetting->getValue()
                && ($tblTaskList = Grade::useService()->getBehaviorTaskListByDivisionCourse($tblDivisionCourse))
            ) {
                $tblBehaviorTask = end($tblTaskList);
            }

            if ($tblBehaviorTask
                && ($tblGradeTypeList = Grade::useService()->getGradeTypeListByTask($tblBehaviorTask))
            ) {
                $countBehavior = count($tblGradeTypeList);
            }

            if ($isDiploma) {
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'IntegrationButton' => 'In&shy;klu&shy;si&shy;on',
                    'Course' => 'Bildungs&shy;gang',
                    'SubjectGrades' => 'Fachnoten'
                );
            } else {
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'IntegrationButton' => 'In&shy;klu&shy;si&shy;on',
                    'Course' => 'Bildungs&shy;gang',
                    'ExcusedAbsence' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                    'UnexcusedAbsence' => 'U-FZ', // 'unent&shy;schuld&shy;igte FZ',
                    'SubjectGrades' => 'Fachnoten',
                    'BehaviorGrades' => 'Kopfnoten',
                );
            }

            if ($useClassRegisterForAbsence) {
                if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                    && $tblGenerateCertificate->getAppointedDateForAbsence()
                ) {
                    $tillDateAbsence = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
                } else {
                    $tillDateAbsence = new DateTime($tblPrepare->getDate());
                }
                list($startDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            }

            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                $count = 0;
                $certificateList = array();
                $droppedSubjectsCreateList = array();
                foreach ($tblPersonList as $tblPerson) {
                    $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                    $data = Prepare::useFrontend()->getStudentBasicInformation($tblPerson, $tblYear, $tblPrepareStudent ?: null, $count, false);
                    $tblCertificate = $tblPrepareStudent ? $tblPrepareStudent->getServiceTblCertificate() : false;
                    $isSekII = DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear);
                    // Keine Zeugnisvorlage
                    if (!$tblCertificate) {
                        $studentTable[$tblPerson->getId()] = array_merge($data, array(
                            'ExcusedAbsence' => '',
                            'UnexcusedAbsence' => '',
                            'SubjectGrades' => '',
                            'BehaviorGrades' => '',
                            'CheckSubjects' => '',
                            'DroppedSubjects' => '',
                            'Option' => ''
                        ));

                        continue;
                    }

                    // Fächer zählen
                    $countSubjects = false;
                    if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYear))) {
                        $countSubjects = count($tblSubjectList);
                    }
                    // Stichtagsnoten zählen
                    $countSubjectGrades = 0;
                    if (($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                        && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblAppointedDateTask, $tblPerson))
                    ) {
                        foreach ($tblTaskGradeList as $tblTaskGrade) {
                            if (($tblTaskGrade->getGrade() !== null && $tblTaskGrade->getGrade() !== '') || $tblTaskGrade->getTblGradeText()) {
                                $countSubjectGrades++;
                            }
                        }
                    }

                    if ($tblPrepare->getServiceTblAppointedDateTask()) {
                        $subjectGradesText = $countSubjectGrades . ' von ' . $countSubjects; // . ' Zensuren&nbsp;';
                    } else {
                        $subjectGradesText = 'Kein Stichtagsnotenauftrag ausgewählt';
                    }

                    // Kopfnoten zählen
                    $countBehaviorGrades = 0;
                    if ($tblBehaviorTask) {
                        if (($tblTaskGradeList = Prepare::useService()->getBehaviorGradeAllByPrepareCertificateAndPerson($tblPrepare, $tblPerson))) {
                            $countBehaviorGrades = count($tblTaskGradeList);
                        }
                        $behaviorGradesText = $countBehaviorGrades . ' von ' . $countBehavior; // . ' Zensuren&nbsp;';
                    } else {
                        $behaviorGradesText = 'Kein Kopfnoten&shy;auftrag ausgewählt';
                    }

                    $excusedDays = '&nbsp;';
                    $unexcusedDays = '&nbsp;';
                    $tblCertificate = false;
                    if ($tblPrepareStudent && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                        // Prüfen, ob die Zeugnisvorlage: Fehlzeiten besitzt
                        if (isset($certificateList[$tblCertificate->getId()])) {
                            $hasCertificateAbsence = $certificateList[$tblCertificate->getId()];
                        } else {
                            $hasCertificateAbsence = Prepare::useService()->hasCertificateAbsence($tblCertificate, $tblPerson);
                            $certificateList[$tblCertificate->getId()] = $hasCertificateAbsence;
                        }

                        if ($hasCertificateAbsence) {
                            // Fehlzeiten nur Anzeigen, wenn Fehltage auf der Zeugnisvorlage sind
                            $hasColumnCertificate = true;
                            $excusedDays = $tblPrepareStudent->getExcusedDays();
                            $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();
                            if ($useClassRegisterForAbsence) {
                                $tblCompany = false;
                                $tblSchoolType = false;
                                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                                    $tblCompany = $tblStudentEducation->getServiceTblCompany();
                                    $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                                }

                                // Fehlzeiten aus dem Klassenbuch holen
                                if ($excusedDays === null) {
                                    $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                                        $startDateAbsence, $tillDateAbsence);
                                }
                                if ($unexcusedDays === null) {
                                    $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                                        $startDateAbsence, $tillDateAbsence);
                                }
                                // Zusatz-Tage von fehlenden Unterrichtseinheiten
                                $excusedDaysFromLessons = $tblPrepareStudent->getExcusedDaysFromLessons();
                                $unexcusedDaysFromLessons = $tblPrepareStudent->getUnexcusedDaysFromLessons();
                                if ($excusedDaysFromLessons) {
                                    $excusedDays = new ToolTip((string)($excusedDays + $excusedDaysFromLessons),
                                        'Fehltage: ' . $excusedDays . ' + Zusatz-Tage: ' . $excusedDaysFromLessons);
                                }
                                if ($unexcusedDaysFromLessons) {
                                    $unexcusedDays = new ToolTip((string)($unexcusedDays + $unexcusedDaysFromLessons),
                                        'Fehltage: ' . $unexcusedDays . ' + Zusatz-Tage: ' . $unexcusedDaysFromLessons);
                                }
                            } else {
                                if ($excusedDays === null) {
                                    $excusedDays = new ToolTip(new WarningText(new Exclamation()), 'Keine entschuldigten Fehltage erfasst');
                                }
                                if ($unexcusedDays === null) {
                                    $unexcusedDays = new ToolTip(new WarningText(new Exclamation()), 'Keine unentschuldigten Fehltage erfasst');
                                }
                            }
                        }
                    }

                    $subjectGradesDisplayText = $countSubjectGrades < $countSubjects || !$tblPrepare->getServiceTblAppointedDateTask()
                        ? new WarningText(new Exclamation() . ' ' . $subjectGradesText)
                        : new Success(new Enable() . ' ' . $subjectGradesText);
                    $behaviorGradesDisplayText = $countBehaviorGrades < $countBehavior || !$tblBehaviorTask
                        ? new WarningText(new Exclamation() . ' ' . $behaviorGradesText)
                        : new Success(new Enable() . ' ' . $behaviorGradesText);

                    // Abitur Fächerprüfung ignorieren
                    if ($tblCertificate
                        && ($tblCertificate->getCertificate() == 'GymAbitur'
                        || $tblCertificate->getCertificate() == 'BGymAbitur'
                        || $tblCertificate->getCertificate() == 'BGymKurshalbjahreszeugnis'
                        || $tblCertificate->getCertificate() == 'BGymAbgSekII')
                    ) {
                        $checkSubjectsString = new Success(
                            new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Keine Fächerzuordnung erforderlich.'
                        );
                    } elseif ($tblCertificate
                        && ($checkSubjectList = Setting::useService()->getCheckCertificateMissingSubjectsForPerson($tblPerson, $tblYear, $tblCertificate))
                    ) {
                        $checkSubjectsString = new WarningText(new Ban() . ' '
                            . implode(', ', $checkSubjectList)
                            . (count($checkSubjectList) > 1 ? ' fehlen' : ' fehlt') . ' auf Zeugnisvorlage');
                    } else {
                        $checkSubjectsString = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' alles ok');
                    }

                    $studentTable[$tblPerson->getId()] = array_merge($data, array(
                        'ExcusedAbsence' => $excusedDays . ' ',
                        'UnexcusedAbsence' => $unexcusedDays . ' ',
                        'SubjectGrades' => $subjectGradesDisplayText,
                        'BehaviorGrades' => $behaviorGradesDisplayText,
                        'CheckSubjects' => $checkSubjectsString,
                        'DroppedSubjects' => '',
                        'Option' =>
                            ($tblCertificate
                                ? (new Standard(
                                    '', '/Education/Certificate/Prepare/Certificate/Show', new EyeOpen(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'Route' => $Route
                                    ),
                                    'Zeugnisvorschau anzeigen'))
                                . (new External(
                                    '',
                                    '/Api/Education/Certificate/Generator/Preview',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'Name' => 'Zeugnismuster'
                                    ),
                                    'Zeugnis als Muster herunterladen'))
                                // Oberschule Abschlusszeugnis Realschule
                                . ((strpos($tblCertificate->getCertificate(), 'MsAbsRs') !== false
                                    && $tblPrepareStudent
                                    && !$tblPrepareStudent->isApproved())
                                    ? new Standard(
                                        '', '/Education/Certificate/Prepare/DroppedSubjects',
                                        new CommodityItem(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'PersonId' => $tblPerson->getId(),
                                            'Route' => $Route
                                        ),
                                        'Abgewählte Fächer verwalten')
                                    : '')
                                : '')
                    ));

                    if (!$isDiploma) {
                        Prepare::useService()->getTemplateInformationForPreview($tblPrepare, $tblPerson, $studentTable, $columnTable);
                    }

                    // Noten vom Vorjahr ermitteln (abgeschlossene Fächer) und speichern
                    // Oberschule Abschlusszeugnis Realschule
                    if ((strpos($tblCertificate->getCertificate(), 'MsAbsRs') !== false)
                        && ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                    ) {
                        if (!isset($columnTable['DroppedSubjects'])) {
                            $columnTable['DroppedSubjects'] = 'Abgewählte Fächer';
                        }

                        $gradeString = '';
                        if (!Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType)) {
                            $gradeString = Prepare::useService()->setAutoDroppedSubjects($tblPrepare, $tblPerson, $droppedSubjectsCreateList);
                        }

                        if ($gradeString) {
                            $studentTable[$tblPerson->getId()]['DroppedSubjects'] = $gradeString;
                        } elseif (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                            $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType))
                        ) {
                            $gradeString = '';
                            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                                if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                                    $gradeString .= $tblSubject->getAcronym() . ':' . $tblPrepareAdditionalGrade->getGrade() . ' ';
                                }
                            }
                            $studentTable[$tblPerson->getId()]['DroppedSubjects'] = $gradeString;
                        } else {
                            $studentTable[$tblPerson->getId()]['DroppedSubjects'] = new WarningText(new Exclamation() . ' nicht erledigt');
                        }
                    }
                }

                if (!empty($droppedSubjectsCreateList)) {
                    Prepare::useService()->createEntityListBulk($droppedSubjectsCreateList);
                }
            }

            // Sekundarstufe II besitzt keine Kopfnoten und Fehlzeiten
            if ($isSekII) {
                unset($columnTable['BehaviorGrades']);
            }

            if (!$hasColumnCertificate) {
                unset($columnTable['ExcusedAbsence']);
                unset($columnTable['UnexcusedAbsence']);
            }

            $columnTable['CheckSubjects'] = 'Prüfung Fächer / Zeugnis';
            $columnTable['Option'] = '';

            // Unterzeichner
            $panelSigner = false;
            if ($tblPrepare->getServiceTblGenerateCertificate()
                && $tblPrepare->getServiceTblGenerateCertificate()->isDivisionTeacherAvailable()
            ) {
                $tblPersonSigner = $tblPrepare->getServiceTblPersonSigner();
                $panelSigner = new Panel('Unterzeichner',
                    array(
                        $tblPersonSigner ? $tblPersonSigner->getFullName() : new Exclamation() . ' Kein Unterzeichner ausgewählt',
                        new Standard(
                            'Unterzeichner auswählen',
                            '/Education/Certificate/Prepare/Signer',
                            new Select(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'Route' => $Route
                            ),
                            'Unterzeichner auswählen'
                        )
                    ),
                    $tblPersonSigner ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING
                );
            }

            $columnDef = array(
                array(
                    "width" => "7px",
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
                array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 1),
            );

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        $tblDivisionCourse->getDisplayName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            $panelSigner ? new LayoutColumn($panelSigner, 6) : null,
                            new LayoutColumn(array(
                                $tblPrepare->getServiceTblAppointedDateTask()
                                    ? new Standard(
                                    'Fachnoten ansehen',
                                    '/Education/Certificate/Prepare/Prepare/Preview/SubjectGrades',
                                    null,
                                    array(
                                        'PrepareId' => $PrepareId,
                                        'Route' => $Route
                                    )
                                ) : null,
                                new External(
                                    'Alle Zeugnisse als Muster herunterladen',
                                    '/Api/Education/Certificate/Generator/PreviewMultiPdf',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Musterzeugnis'
                                    ),
                                    false
                                ),
                                $tblPrepare->getIsPrepared()
                                    ? new Standard(
                                        'Zeugnisvorbereitung abgeschlossen entfernen',
                                        '/Education/Certificate/Prepare/SetIsPrepared',
                                        new Remove(),
                                        array(
                                            'PrepareId' => $PrepareId,
                                            'Route' => $Route,
                                            'IsPrepared' => 0
                                        )
                                    )
                                    : new Standard(
                                        'Zeugnisvorbereitung abgeschlossen',
                                        '/Education/Certificate/Prepare/SetIsPrepared',
                                        new Check(),
                                        array(
                                            'PrepareId' => $PrepareId,
                                            'Route' => $Route,
                                            'IsPrepared' => 1
                                        )
                                    )
                            ))
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                ApiSupportReadOnly::receiverOverViewModal(),
                                new TableData($studentTable, null, $columnTable, array(
                                    'columnDefs' => $columnDef,
                                    'order' => array(
                                        array('0', 'asc'),
                                    ),
                                    "paging" => false, // Deaktivieren Blättern
                                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    "responsive" => false
                                ))
                            ))
                        ))
                    ), new Title('Übersicht'))
                ))
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $Route
     * @param int $IsPrepared
     *
     * @return Stage|string
     */
    public function frontendSetIsPrepared($PrepareId = null, $Route = null, int $IsPrepared = 0)
    {
        $IsPrepared = (bool) $IsPrepared;
        $stage = new Stage('Zeugnisvorbeitung', $IsPrepared ? 'abgeschlossen' : 'abgeschlossen entfernen');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))) {
            Prepare::useService()->updatePrepareData($tblPrepare, $tblPrepare->getServiceTblPersonSigner() ?: null, $IsPrepared);
            return $stage . new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    . ' Zeugnisvorbereitung abgeschlossen wurde ' . ($IsPrepared ? ' gesetzt.' : ' entfernt.'))
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route
                ));
        }

        return $stage;
    }

    /**
     * @param null $PrepareId
     * @param null $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSigner($PrepareId = null, $Route = null, $Data = null)
    {
        $Stage = new Stage('Unterzeichner', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data'] = $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : 0;
                $Global->savePost();
            }

            $personList[0] = '-[Nicht ausgewählt]-';
            if (($teacherList = $tblDivisionCourse->getDivisionTeacherList())) {
                foreach ($teacherList as $tblPerson) {
                    $personList[$tblPerson->getId()] = $tblPerson->getFullName();
                }
            }

            if (($tblPersonSigner = $tblPrepare->getServiceTblPersonSigner()) && !isset($personList[$tblPersonSigner->getId()])) {
                $personList[$tblPersonSigner->getId()] = $tblPersonSigner->getFullName();
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new SelectBox(
                                'Data',
                                'Unterzeichner (Klassenlehrer)',
                                $personList
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
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
                            new LayoutColumn(array(
                                !empty($personList)
                                    ? new Well(Prepare::useService()->updatePrepareSetSigner($form, $tblPrepare, $Data, $Route))
                                    : new Warning('Für diesen Kurs sind keine Klassenlehrer/Mentoren/Tutoren vorhanden.')
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param $PrepareId
     * @param $Route
     *
     * @return Stage|string
     */
    public function frontendPrepareShowSubjectGrades($PrepareId = null, $Route = null)
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten-Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Preview',
                new ChevronLeft(),
                array(
                    'PrepareId' => $PrepareId,
                    'Route' => $Route
                )
            )
        );

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $content = new Layout(array(
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
                    )),
                ))
            ));

            if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())) {
                $showFinaleGrade = false;
                if (($tblCertificateType = $tblPrepare->getCertificateType())
                    && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                    && ($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                    && ($tblSchoolTypeOS = Type::useService()->getTypeByShortName('OS'))
                    && ($tblSchoolTypeFOS = Type::useService()->getTypeByShortName('FOS'))
                    && ($tblSchoolTypeBFS = Type::useService()->getTypeByShortName('BFS'))
                    && (isset($tblSchoolTypeList[$tblSchoolTypeOS->getId()])
                        || isset($tblSchoolTypeList[$tblSchoolTypeFOS->getId()])
                        || isset($tblSchoolTypeList[$tblSchoolTypeBFS->getId()])
                    )
                ) {
                    $showFinaleGrade = true;
                }
                $content .= Grade::useFrontend()->getTaskGradeViewByAppointedDateTask($tblTask, $tblDivisionCourse, $showFinaleGrade ? $tblPrepare : null);
            } else {
                $content .= new Warning('Kein Stichtagsnotenauftrag hinterlegt.', new Exclamation());
            }

            return $Stage->setContent($content);
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendShowCertificate(
        $PrepareId = null,
        $PersonId = null,
        $Route = null
    ) {
        $Stage = new Stage('Zeugnisvorschau', 'Anzeigen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {
            $ContentLayout = array();
            $tblCertificate = false;
            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            ) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                if (class_exists($CertificateClass)) {
                    $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                    $tblDivisionCourse = $tblPrepare->getServiceTblDivision();
                    /** @var Certificate $Template */
                    $Template = new $CertificateClass($tblStudentEducation ?: null, $tblPrepare);

                    // get Content
                    $Content = Prepare::useService()->createCertificateContent($tblPerson, $tblPrepareStudent);
                    $personId = $tblPerson->getId();
                    if (isset($Content['P' . $personId]['Grade'])) {
                        $Template->setGrade($Content['P' . $personId]['Grade']);
                    }
                    if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                        $Template->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                    }

                    $pageList[$tblPerson->getId()] = $Template->buildPages($tblPerson);
                    $bridge = $Template->createCertificate($Content, $pageList);

                    $ContentLayout[] = $bridge->getContent();
                }
            }
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    $tblDivisionCourse->getTypeName(),
                                    $tblDivisionCourse->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorlage',
                                    $tblCertificate
                                        ? ($tblCertificate->getName() . ($tblCertificate->getDescription() ? ' - ' . $tblCertificate->getDescription() : ''))
                                        : new WarningText(new Exclamation() . ' Keine Zeugnisvorlage hinterlegt'),
                                    $tblCertificate
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 12),
                            new LayoutColumn(
                                $ContentLayout
                            ),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }
}