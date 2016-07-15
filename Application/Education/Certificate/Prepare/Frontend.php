<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quote;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendSelectDivision()
    {

        $Stage = new Stage('Klasse', 'Auswählen');

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        if ($tblPerson) {
            $tblDivisionList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
        } else {
            $tblDivisionList = false;
        }

        $divisionTable = array();
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivisionTeacher) {
                $tblDivision = $tblDivisionTeacher->getTblDivision();
                $divisionTable[] = array(
                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type' => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option' => new Standard(
                        '', '/Education/Certificate/Prepare/Prepare', new Select(),
                        array(
                            'DivisionId' => $tblDivision->getId()
                        ),
                        'Auswählen'
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                )
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPrepare($DivisionId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {

            $tableData = array();
            $tblPrepareAllByDivision = Prepare::useService()->getPrepareAllByDivision($tblDivision);
            if ($tblPrepareAllByDivision) {
                foreach ($tblPrepareAllByDivision as $tblPrepare) {
                    $tableData[] = array(
                        'Date' => $tblPrepare->getDate(),
                        'Name' => $tblPrepare->getName(),
                        'Option' =>
                            (new Standard(
                                '', '/Education/Certificate/Prepare/Prepare/Edit', new Edit(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                )
                                , 'Bearbeiten'
                            ))
                            . (new Standard(
                                '', '/Education/Certificate/Prepare/Division', new EyeOpen(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                )
                                , 'Anzeigen und Bearbeiten'
                            ))
                    );
                }
            }

            $Form = $this->formPrepare()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tableData, null, array(
                                        'Date' => 'Zeugnisdatum',
                                        'Name' => 'Name',
                                        'Option' => ''
                                    ),
                                        array(
                                            'order' => array(
                                                array(0, 'desc')
                                            ),
                                            'columnDefs' => array(
                                                array('type' => 'de_date', 'targets' => 0)
                                            )
                                        )
                                    )
                                ))
                            ))
                        ), new Title(new ListingTable() . ' Übersicht')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Prepare::useService()->createPrepare($Form, $tblDivision,
                                        $Data))
                                )
                            ))
                        ), new Title(new PlusSign() . ' Hinzufügen'))
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditPrepare($PrepareId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Bearbeiten');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array('DivisionId' => $tblDivision->getId())
                ));
            }

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Date'] = $tblPrepare->getDate();
                $Global->POST['Data']['Name'] = $tblPrepare->getName();
                $Global->savePost();
            }

            $Form = $this->formPrepare()
                ->appendFormButton(new Primary('Speichern', new Save()))
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
                                        'Klasse',
                                        $tblDivision ? $tblDivision->getDisplayName() : '',
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Prepare::useService()->updatePrepare($Form, $tblPrepare,
                                        $Data))
                                )
                            ))
                        ), new Title(new PlusSign() . ' Hinzufügen'))
                    )
                )
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
     * @return Form
     */
    private function formPrepare()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()), 3
                ),
                new FormColumn(
                    new TextField('Data[Name]', 'Name', 'Name'), 9
                ),
            )),
        )));
    }


    /**
     * @param null $PrepareId
     *
     * @return Stage
     */
    public function frontendDivision($PrepareId = null)
    {

        $Stage = new Stage('Klassen', 'Übersicht');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array('DivisionId' => $tblDivision->getId())
                ));

                $tblGradeTypeList = array();
                if ($tblPrepare->getServiceTblBehaviorTask()) {
                    $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask());
                    if ($tblTestAllByTask) {
                        foreach ($tblTestAllByTask as $tblTest) {
                            if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                                if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                                    $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                                }
                            }
                        }
                    }
                }
                $countBehavior = count($tblGradeTypeList);

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    foreach ($tblStudentList as $tblPerson) {
                        $tblAddress = $tblPerson->fetchMainAddress();
                        $birthday = '';
                        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                            if ($tblCommon->getTblCommonBirthDates()) {
                                $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                            }
                        }
                        $course = '';
                        if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                            $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                            if ($tblTransferType) {
                                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                    $tblTransferType);
                                if ($tblStudentTransfer) {
                                    $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                    if ($tblCourse) {
                                        $course = $tblCourse->getName();
                                    }
                                }
                            }
                        }

                        // Fächer zählen
                        if ($tblDivision->getServiceTblYear()) {
                            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                                $tblPerson, $tblDivision->getServiceTblYear()
                            );
                        } else {
                            $tblDivisionSubjectList = false;
                        }
                        if ($tblDivisionSubjectList) {
                            $countSubjects = count($tblDivisionSubjectList);
                        } else {
                            $countSubjects = 0;
                        }

                        // Zensuren zählen
                        if ($tblPrepare->getServiceTblAppointedDateTask()) {
                            $tblPrepareGradeSubjectList = Prepare::useService()->getPrepareGradeAllByPerson(
                                $tblPrepare, $tblPerson, $tblPrepare->getServiceTblAppointedDateTask()->getTblTestType()
                            );
                        } else {
                            $tblPrepareGradeSubjectList = false;
                        }
                        if ($tblPrepareGradeSubjectList) {
                            $countSubjectGrades = count($tblPrepareGradeSubjectList);
                        } else {
                            $countSubjectGrades = 0;
                        }

                        if ($tblPrepare->getServiceTblBehaviorTask()) {
                            $tblPrepareGradeBehaviorList = Prepare::useService()->getPrepareGradeAllByPerson(
                                $tblPrepare, $tblPerson, $tblPrepare->getServiceTblBehaviorTask()->getTblTestType()
                            );
                        } else {
                            $tblPrepareGradeBehaviorList = false;
                        }
                        if ($tblPrepareGradeBehaviorList) {
                            $countBehaviorGrades = count($tblPrepareGradeBehaviorList);
                        } else {
                            $countBehaviorGrades = 0;
                        }

                        if ($tblPrepare->getServiceTblAppointedDateTask()) {
                            $subjectGradesText = $countSubjectGrades . ' von ' . $countSubjects . ' Zensuren&nbsp;';
                        } else {
                            $subjectGradesText = new Exclamation() . ' Kein Stichtagsnotenauftrag ausgewählt';
                        }

                        if ($tblPrepare->getServiceTblBehaviorTask()) {
                            $behaviorGradesText = $countBehaviorGrades . ' von ' . $countBehavior . ' Zensuren&nbsp;';
                        } else {
                            $behaviorGradesText = new Exclamation() . ' Kein Stichtagsnotenauftrag ausgewählt';
                        }

                        $studentTable[] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => $tblAddress ? $tblAddress->getGuiString() : '',
                            'Birthday' => $birthday,
                            'Course' => $course,
                            'ExcusedAbsence' => Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision),
                            'UnexcusedAbsence' => Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                $tblDivision),
                            'SubjectGrades' => ($countSubjectGrades < $countSubjects
                                ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $subjectGradesText)
                                : new Success(new Enable() . ' ' . $subjectGradesText)),
                            'BehaviorGrades' => ($countBehaviorGrades < $countBehavior
                                ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $behaviorGradesText)
                                : new Success(new Enable() . ' ' . $behaviorGradesText)),
                            'Option' => new Standard(
                                '', '/Education/ClassRegister/Absence', new Time(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'PersonId' => $tblPerson->getId()
                                ),
                                'Fehlzeiten des Schülers verwalten'
                            )
                        );
                    }
                }
            }

            /*
             * Buttons
             */
            $buttonAppointedDateTask = new Standard(
                'Stichtagsnotenauftrag wählen und Fachnoten übernehmen',
                '/Education/Certificate/Prepare/AppointedDateTask',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId()
                ),
                'Stichtagsnotenauftrag auswählen und Fachnoten übernehmen'
            );
            $buttonAppointedDateTaskShowGrades = new Standard(
                'Fachnoten ansehen',
                '/Education/Certificate/Prepare/SubjectGrades',
                new EyeOpen(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Fachnoten ansehen'
            );
            $buttonBehaviorTask = new Standard(
                'Kopfnotenauftrag wählen',
                '/Education/Certificate/Prepare/BehaviorTask',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId()
                ),
                'Kopfnotenauftrag auswählen'
            );
            $buttonBehaviorTaskShowGrades = new Standard(
                'Kopfnoten ansehen und Kopfnoten festlegen',
                '/Education/Certificate/Prepare/BehaviorGrades',
                new EyeOpen(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Kopfnoten ansehen und Kopfnoten festlegen'
            );

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
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Stichtagsnotenauftrag',
                                    array(
                                        $tblPrepare->getServiceTblAppointedDateTask()
                                            ? $tblPrepare->getServiceTblAppointedDateTask()->getName()
                                            . ' ' . $tblPrepare->getServiceTblAppointedDateTask()->getDate()
                                            : new Exclamation() . ' Kein Stichtagsnotenauftrag ausgewählt',
                                        $buttonAppointedDateTask .
                                        ($tblPrepare->getServiceTblAppointedDateTask()
                                            ? $buttonAppointedDateTaskShowGrades
                                            : '')
                                    ),
                                    $tblPrepare->getServiceTblAppointedDateTask()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Kopfnotenauftrag',
                                    array(
                                        $tblPrepare->getServiceTblBehaviorTask()
                                            ? $tblPrepare->getServiceTblBehaviorTask()->getName()
                                            . ' ' . $tblPrepare->getServiceTblBehaviorTask()->getDate()
                                            : 'Kein Kopfnotenauftrag ausgewählt',
                                        $buttonBehaviorTask .
                                        ($tblPrepare->getServiceTblBehaviorTask()
                                            ? $buttonBehaviorTaskShowGrades
                                            : '')
                                    ),
                                    $tblPrepare->getServiceTblBehaviorTask()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6),
                        )),
                    ), new Title('Notenaufträge')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                '<br>',
                                new TableData($studentTable, null, array(
                                    'Name' => 'Name',
                                    'Address' => 'Adresse',
                                    'Birthday' => 'Geburtsdatum',
                                    'Course' => 'Bildungsgang',
                                    'ExcusedAbsence' => 'entschuldigte FZ',
                                    'UnexcusedAbsence' => 'unentschuldigte FZ',
                                    'SubjectGrades' => 'Fachnoten',
                                    'BehaviorGrades' => 'Kopfnoten',
                                ), array(
                                    'order' => array(
                                        array('0', 'asc'),
                                    ),
                                    "paging" => false, // Deaktivieren Blättern
                                    "iDisplayLength" => -1,    // Alle Einträge zeigen
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
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendAppointedDateTask($PrepareId = null, $Data = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data'] = $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : 0;
                $Global->savePost();
            }

            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
            $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType);

//            if ($tblTaskList) {
//                $tblTaskList = $this->getSorter($tblTaskList)->sortObjectBy('Date', new DateTimeSorter(),
//                    Sorter::ORDER_DESC);
//            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new SelectBox(
                                'Data',
                                'Stichtagsnotenauftrag',
                                array('{{ Name}} {{ Date }} ' => $tblTaskList)
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
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                $tblTaskList
                                    ? new Well(Prepare::useService()->updatePrepareSetAppointedDateTask($form,
                                    $tblPrepare, $Data))
                                    : new Warning('Für diese Klasse sind keine Notenaufträge vorhanden.')
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
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendSubjectGrades($PrepareId = null)
    {

        $Stage = new Stage('Fachnoten', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');

            $tableData = array();
            $tableHeader['Student'] = 'Schüler';
            if (($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblYear = $tblDivision->getServiceTblYear())
            ) {
                $tblStudentAllByDivision = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentAllByDivision) {
                    foreach ($tblStudentAllByDivision as $tblPerson) {
                        $tableData[$tblPerson->getId()]['Student'] = $tblPerson->getLastFirstName();
                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                            $tblPerson, $tblYear
                        );
                        if ($tblDivisionSubjectList) {
                            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                $tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject(
                                    $tblPrepare,
                                    $tblPerson,
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblTestType
                                );
                                if (!isset($tableHeader['Subject' . $tblDivisionSubject->getServiceTblSubject()])) {
                                    $tableHeader['Subject' . $tblDivisionSubject->getServiceTblSubject()]
                                        = $tblDivisionSubject->getServiceTblSubject()->getAcronym()
                                        . ($tblDivision->getId() == $tblDivisionSubject->getTblDivision()->getId()
                                            ? '' : ' (' . $tblDivisionSubject->getTblDivision()->getDisplayName() . ')');
                                }
                                $tableData[$tblPerson->getId()]['Subject' . $tblDivisionSubject->getServiceTblSubject()]
                                    = $tblPrepareGrade ? $tblPrepareGrade->getGrade()
                                    : new \SPHERE\Common\Frontend\Text\Repository\Warning('f');
                            }
                        }
                    }

                    // leere Zellen setzen
                    foreach ($tableHeader as $key => $value) {
                        foreach ($tblStudentAllByDivision as $tblPerson) {
                            if (!isset($tableData[$tblPerson->getId()][$key])) {
                                $tableData[$tblPerson->getId()][$key] = '';
                            }
                        }
                    }
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
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new TableData(
                                    $tableData,
                                    null,
                                    $tableHeader,
                                    null
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung oder Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendBehaviorTask($PrepareId = null, $Data = null)
    {

        $Stage = new Stage('Kopfnotenauftrag', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
            $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType);


            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data'] = $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : 0;
                $Global->savePost();
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new SelectBox(
                                'Data',
                                'Kofpnotenauftrag',
                                array('{{ Name}} {{ Date }} ' => $tblTaskList)
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $content = $tblTaskList
                ? new Well(Prepare::useService()->updatePrepareSetBehaviorTask($form,
                    $tblPrepare, $Data))
                : new Warning('Für diese Klasse sind keine Notenaufträge vorhanden.');

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
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(
                                $content
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

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendBehaviorGrades($PrepareId = null)
    {

        $Stage = new Stage('Fachnoten', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare && ($tblDivision = $tblPrepare->getServiceTblDivision())) {
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');

            $dataTable = array();
            $headerTable['Student'] = 'Name';

            $tblGradeTypeList = array();
            $tblStudentAllByDivision = Division::useService()->getStudentAllByDivision($tblDivision);
            $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask());
            if ($tblTestAllByTask) {
                foreach ($tblTestAllByTask as $tblTest) {
                    if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                        if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                            $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                            $headerTable['GradeType' . $tblGradeType->getId()] = $tblGradeType->getCode() . ' ('
                                . $tblGradeType->getName() . ')';
                        }
                    }
                }
            }

            $headerTable['Option'] = '';

            if ($tblStudentAllByDivision) {
                foreach ($tblStudentAllByDivision as $tblPerson) {
                    if ($tblDivision->getServiceTblYear()) {
                        $dataTable[$tblPerson->getId()]['Student'] = $tblPerson->getLastFirstName();
                        $dataTable[$tblPerson->getId()]['Option'] = new Standard(
                            '',
                            '/Education/Certificate/Prepare/BehaviorGrades/Edit',
                            new Edit(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId()
                            ),
                            'Kopfnoten bearbeiten'
                        );

                        /** @var TblGradeType $tblGradeType */
                        foreach ($tblGradeTypeList as $tblGradeType) {
                            $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                                $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType
                            );
                            $dataTable[$tblPerson->getId()]['GradeType' . $tblGradeType->getId()] = $tblPrepareGrade
                                ? $tblPrepareGrade->getGrade()
                                : '';
                        }
                    }
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
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new TableData(
                                    $dataTable,
                                    null,
                                    $headerTable,
                                    null
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung oder Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Data
     * @return Stage
     */
    public function frontendEditBehaviorGrades($PrepareId = null, $PersonId = null, $Data = null)
    {

        $Stage = new Stage('Kopfnoten', 'Festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/BehaviorTask', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblScoreType = false;
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $tblPerson = Person::useService()->getPersonById($PersonId);
            $tempTable = array();
            $dataTable = array();
            $headerTable = array(
                'GradeType' => 'Zensuren-Typ',
                'Grades' => 'Zensuren',
                'Average' => '&#216;',
                'Grade' => 'Zensur',
            );
            $gradeList = array();
            if ($tblDivision && $tblPerson) {

                $data = array();
                $tblGradeTypeList = array();
                $tblScoreType = $tblPrepare->getServiceTblBehaviorTask()->getServiceTblScoreType();
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask());
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                            if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                                $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                                $dataTable[$tblGradeType->getId()]['GradeType'] = $tblGradeType->getCode() . ' ('
                                    . $tblGradeType->getName() . ')';
                            }
                            if (($tblSubject = $tblTest->getServiceTblSubject())
                                && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                    $tblPerson))
                            ) {
                                $data[$tblGradeType->getId()][$tblSubject->getId()] = $tblGrade;
                                if (!$tblScoreType && $tblGrade->getServiceTblDivision() && $tblGrade->getServiceTblSubject()) {
                                    Gradebook::useService()->getScoreTypeByDivisionAndSubject($tblGrade->getServiceTblDivision(),
                                        $tblGrade->getServiceTblSubject());
                                }
                            }
                        }
                    }
                }

                // Zusammensetzen (für Anzeige) der vergebenen Kopfnoten
                foreach ($data as $gradeTypeId => $subjects) {
                    $tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId);
                    if ($tblGradeType && is_array($subjects)) {
                        /** @var TblGrade $grade */
                        foreach ($subjects as $subjectId => $grade) {
                            $tblSubject = Subject::useService()->getSubjectById($subjectId);
                            if ($tblSubject) {
                                if ($grade->getGrade() && is_numeric($grade->getGrade())) {
                                    $gradeList[$tblGradeType->getId()][] = floatval($grade->getGrade());
                                }
                                if (isset($tempTable[$tblGradeType->getId()])) {
                                    $tempTable[$tblGradeType->getId()] .= ' | '
                                        . $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                } else {
                                    $tempTable[$tblGradeType->getId()] =
                                        $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                }
                            }
                        }
                    }
                }

                // calc average
                foreach ($gradeList as $gradeTypeId => $valueArray) {
                    $count = count($valueArray);
                    $dataTable[$gradeTypeId]['Average'] = $count > 0 ? round(array_sum($valueArray) / $count, 2) : '';
                }

                // Post setzen
                if ($Data === null
                    && ($tblTask = $tblPrepare->getServiceTblBehaviorTask())
                    && ($tblTestType = $tblTask->getTblTestType())
                ) {
                    $Global = $this->getGlobal();
                    /** @var TblGradeType $tblGradeType */
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                            $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType
                        );
                        if ($tblPrepareGrade) {
                            $Global->POST['Data'][$tblGradeType->getId()] = $tblPrepareGrade->getGrade();
                        }
                    }
                    $Global->savePost();
                }

                /** @var TblGradeType $tblGradeType */
                foreach ($tblGradeTypeList as $tblGradeType) {
                    if (!isset($dataTable[$tblGradeType->getId()]['Average'])) {
                        $dataTable[$tblGradeType->getId()]['Average'] = '';
                    }

                    // Zensuren-Eingaben-Spalte
                    if ($tblScoreType && $tblScoreType->getIdentifier() == 'VERBAL') {
                        $dataTable[$tblGradeType->getId()]['Grade']
                            = new TextField('Data[' . $tblGradeType->getId() . ']', '', '', new Quote());
                    } else {
                        $dataTable[$tblGradeType->getId()]['Grade']
                            = new NumberField('Data[' . $tblGradeType->getId() . ']', '', '');
                    }
                }

                // fehlende Kopfnoten anzeigen
                if ($tblDivision->getServiceTblYear()) {
                    $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                        $tblDivision->getServiceTblYear()
                    );
                    if ($tblDivisionSubjectList) {
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                            if ($tblSubject) {
                                /** @var TblGradeType $tblGradeType */
                                foreach ($tblGradeTypeList as $tblGradeType) {
                                    if (!isset($data[$tblGradeType->getId()][$tblSubject->getId()])) {
                                        $text = new \SPHERE\Common\Frontend\Text\Repository\Warning('f');
                                        if (isset($tempTable[$tblGradeType->getId()])) {
                                            $tempTable[$tblGradeType->getId()] .= ' | '
                                                . $tblSubject->getAcronym() . ':' . $text;
                                        } else {
                                            $tempTable[$tblGradeType->getId()] =
                                                $tblSubject->getAcronym() . ':' . $text;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (is_array($tempTable)) {
                    foreach ($tempTable as $gradeTypeId => $value) {
                        $dataTable[$gradeTypeId]['Grades'] = $value;
                    }
                }
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new TableData(
                                $dataTable,
                                null,
                                $headerTable,
                                null
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
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson ? $tblPerson->getLastFirstName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                Prepare::useService()->updatePrepareGradeForBehaviorTask($form, $tblPrepare, $tblPerson,
                                    $tblScoreType ? $tblScoreType : null, $Data)
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
}
