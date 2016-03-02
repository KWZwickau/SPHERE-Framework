<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:39
 */

namespace SPHERE\Application\Education\Graduation\Evaluation;

use DateTime;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
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
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Dice;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quote;
use SPHERE\Common\Frontend\Icon\Repository\Rate15;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendTest()
    {

        $Stage = new Stage('Leistungsüberprüfung', 'Auswahl');
        $Stage->setMessage(
            'Verwaltung der Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten),
            wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
        );

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        if ($tblPerson) {
            // Fachlehrer
            $tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
            if ($tblSubjectTeacherAllByTeacher) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    $tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject();
                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                            [$tblDivisionSubject->getServiceTblSubject()->getId()]
                            [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                = $tblDivisionSubject->getId();
                        }
                    } else {
                        if ($tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()
                            && $tblDivisionSubject->getServiceTblSubject()
                        ) {
                            $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                $tblDivisionSubject->getTblDivision(),
                                $tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()
                            );
                            if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                    $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                    [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                    [$item->getTblSubjectGroup()->getId()]
                                        = $item->getId();
                                }
                            } else {
                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                [$tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()->getId()]
                                    = $tblSubjectTeacher->getTblDivisionSubject()->getId();
                            }
                        }
                    }
                }
            }

            // Klassenlehrer
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherAllByTeacher) {
                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                    $tblDivisionSubjectAllByDivision
                        = Division::useService()->getDivisionSubjectByDivision($tblDivisionTeacher->getTblDivision());
                    if ($tblDivisionSubjectAllByDivision) {
                        foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                            if ($tblDivisionSubject->getTblSubjectGroup()) {
                                if ($tblDivisionSubject->getServiceTblSubject()) {
                                    $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                    [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                    [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                        = $tblDivisionSubject->getId();
                                }
                            } else {
                                if ($tblDivisionSubject->getServiceTblSubject()) {
                                    $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                        = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                        $tblDivisionSubject->getTblDivision(),
                                        $tblDivisionSubject->getServiceTblSubject()
                                    );
                                    if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                        foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                            $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                            [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                            [$item->getTblSubjectGroup()->getId()]
                                                = $item->getId();
                                        }
                                    } else {
                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                            = $tblDivisionSubject->getId();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                foreach ($subjectList as $subjectId => $value) {
                    $tblSubject = Subject::useService()->getSubjectById($subjectId);
                    if (is_array($value)) {
                        foreach ($value as $subjectGroupId => $subValue) {
                            $item = Division::useService()->getSubjectGroupById($subjectGroupId);
                            $divisionSubjectTable[] = array(
                                'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                                'Type' => $tblDivision->getTypeName(),
                                'Division' => $tblDivision->getDisplayName(),
                                'Subject' => $tblSubject->getName(),
                                'SubjectGroup' => $item->getName(),
                                'Option' => new Standard(
                                    '', '/Education/Graduation/Evaluation/Test/Selected', new Select(), array(
                                    'DivisionSubjectId' => $subValue
                                ),
                                    'Auswählen'
                                )
                            );
                        }
                    } else {
                        $divisionSubjectTable[] = array(
                            'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Subject' => $tblSubject->getName(),
                            'SubjectGroup' => '',
                            'Option' => new Standard(
                                '', '/Education/Graduation/Evaluation/Test/Selected', new Select(), array(
                                'DivisionSubjectId' => $value
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
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'Option' => ''
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendHeadmasterTest()
    {

        $Stage = new Stage('Leistungsüberprüfung (Leitung)', 'Auswahl');
        $Stage->setMessage(
            'Verwaltung aller Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten).'
        );

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as $tblDivision) {
                $tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                if ($tblDivisionSubjectAllByDivision) {
                    foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            if ($tblDivisionSubject->getTblSubjectGroup()) {
                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                    = $tblDivisionSubject->getId();
                            } else {
                                $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                    = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject()
                                );
                                if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                    foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        [$item->getTblSubjectGroup()->getId()]
                                            = $item->getId();
                                    }
                                } else {
                                    $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                    [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        = $tblDivisionSubject->getId();
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                foreach ($subjectList as $subjectId => $value) {
                    $tblSubject = Subject::useService()->getSubjectById($subjectId);
                    if (is_array($value)) {
                        foreach ($value as $subjectGroupId => $subValue) {
                            $item = Division::useService()->getSubjectGroupById($subjectGroupId);
                            $divisionSubjectTable[] = array(
                                'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                                'Type' => $tblDivision->getTypeName(),
                                'Division' => $tblDivision->getDisplayName(),
                                'Subject' => $tblSubject->getName(),
                                'SubjectGroup' => $item->getName(),
                                'Option' => new Standard(
                                    '', '/Education/Graduation/Evaluation/Headmaster/Test/Selected', new Select(),
                                    array(
                                        'DivisionSubjectId' => $subValue
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    } else {
                        $divisionSubjectTable[] = array(
                            'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getName() : '',
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Subject' => $tblSubject->getName(),
                            'SubjectGroup' => '',
                            'Option' => new Standard(
                                '', '/Education/Graduation/Evaluation/Headmaster/Test/Selected', new Select(), array(
                                'DivisionSubjectId' => $value
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
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'Option' => ''
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $Test
     *
     * @return Stage
     */
    public function frontendTestSelected(
        $DivisionSubjectId = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Übersicht');
        $this->contentTestSelected($DivisionSubjectId, $Test, $Stage, '/Education/Graduation/Evaluation/Test');

        return $Stage;
    }

    /**
     * @param        $DivisionSubjectId
     * @param        $Test
     * @param Stage $Stage
     * @param string $BasicRoute
     */
    private function contentTestSelected($DivisionSubjectId, $Test, Stage $Stage, $BasicRoute)
    {

        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft()));

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
        $tblDivision = $tblDivisionSubject->getTblDivision();

        if ($tblDivisionSubject->getServiceTblSubject()) {
            $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                $tblDivision,
                $tblDivisionSubject->getServiceTblSubject(),
                null,
                null,
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
        } else {
            $tblTestList = false;
        }
        if ($tblTestList) {
            array_walk($tblTestList, function (TblTest &$tblTest) use (&$BasicRoute) {

                $tblDivision = $tblTest->getServiceTblDivision();
                if ($tblDivision) {
                    $tblTest->Division = $tblDivision->getDisplayName();
                } else {
                    $tblTest->Division = '';
                }
                $tblTask = $tblTest->getTblTask();
                $tblTest->Subject = $tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getName() : '';
                $tblTest->Period = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getName() : '';
                if ($tblTest->getServiceTblGradeType()) {
                    if ($tblTask) {
                        $tblTest->GradeType = new Bold('Kopfnote: ' . $tblTest->getServiceTblGradeType()->getName())
                            . ($tblTask->getServiceTblPeriod()
                                ? new Small(new Muted(' ' . $tblTask->getServiceTblPeriod()->getName()))
                                : new Small(new Muted(' Gesamtes Schuljahr')));
                    } else {
                        $tblTest->GradeType = $tblTest->getServiceTblGradeType()->getName();
                    }
                } elseif ($tblTask) {
                    $tblTest->GradeType = new Bold('Stichtagsnote') . ($tblTask->getServiceTblPeriod()
                            ? new Small(new Muted(' ' . $tblTask->getServiceTblPeriod()->getName()))
                            : new Small(new Muted(' Gesamtes Schuljahr')));
                } else {
                    $tblTest->GradeType = '';
                }
                if ($tblTask) {
                    $tblTest->DisplayDescription = $tblTask->getName();
                    $tblTest->DisplayPeriod = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                } else {
                    $tblTest->DisplayDescription = $tblTest->getDescription();
                    $tblTest->DisplayPeriod = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getName() : '';
                }

                $tblTest->Option =
                    ($tblTest->getTblTestType()->getId() == Evaluation::useService()->getTestTypeByIdentifier('TEST')->getId()
                        ? (new Standard('', $BasicRoute . '/Edit', new Edit(),
                            array('Id' => $tblTest->getId()), 'Bearbeiten')) : '')
                    . (new Standard('', $BasicRoute . '/Grade/Edit', new Listing(),
                        array('Id' => $tblTest->getId()), 'Zensuren bearbeiten'));

                $tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
                if ($tblGradeList) {
                    $countGrades = count($tblGradeList);
                } else {
                    $countGrades = 0;
                }

                if ($tblTest->getServiceTblDivision() && $tblTest->getServiceTblSubject()) {
                    if ($tblTest->getServiceTblSubjectGroup()) {
                        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                            $tblTest->getServiceTblDivision(),
                            $tblTest->getServiceTblSubject(),
                            $tblTest->getServiceTblSubjectGroup()
                        );
                        $countStudent = Division::useService()->countSubjectStudentByDivisionSubject($tblDivisionSubject);
                    } else {
                        $countStudent = Division::useService()->countDivisionStudentAllByDivision($tblTest->getServiceTblDivision());
                    }
                    $tblTest->Grades = $countGrades == $countStudent ? new Success($countGrades . ' von ' . $countStudent) :
                        new Warning($countGrades . ' von ' . $countStudent);
                }
            });
        } else {
            $tblTestList = array();
        }

        if ($tblDivision->getServiceTblYear()) {
            $Form = $this->formTest($tblDivision->getServiceTblYear())
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        } else {
            $Form = false;
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Fach-Klasse',
                                'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                ($tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '') .
                                ($tblDivisionSubject->getTblSubjectGroup() ? new Small(
                                    ' (Gruppe: ' . $tblDivisionSubject->getTblSubjectGroup()->getName() . ')') : ''),
                                Panel::PANEL_TYPE_INFO
                            )
                        ))
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblTestList, null, array(
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'DisplayPeriod' => 'Zeitraum',
                                'GradeType' => 'Zensuren-Typ',
                                'DisplayDescription' => 'Beschreibung',
                                'Date' => 'Datum',
                                'CorrectionDate' => 'Korrekturdatum',
                                'ReturnDate' => 'R&uuml;ckgabedatum',
                                'Grades' => 'Noten eingetragen',
                                'Option' => ''
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $Form
                                ? new Well(Evaluation::useService()->createTest($Form, $tblDivisionSubject->getId(),
                                $Test, $BasicRoute))
                                : new Danger('Schuljahr nicht gefunden', new Ban())
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );
    }

    /**
     * @param TblYear $tblYear
     * @return Form
     */
    private function formTest(TblYear $tblYear)
    {

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);

        // select current period
        $Global = $this->getGlobal();
        if (!$Global->POST && $tblPeriodList) {
            foreach ($tblPeriodList as $tblPeriod) {
                if ($tblPeriod->getFromDate() && $tblPeriod->getToDate()) {
                    $fromDate = (new \DateTime($tblPeriod->getFromDate()))->format("Y-m-d");
                    $toDate = (new \DateTime($tblPeriod->getToDate()))->format("Y-m-d");
                    $now = (new \DateTime('now'))->format("Y-m-d");
                    if ($fromDate <= $now && $now <= $toDate) {
                        $Global->POST['Test']['Period'] = $tblPeriod->getId();
                    }
                }
            }
            $Global->savePost();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Test[Period]', 'Zeitraum', array('Name' => $tblPeriodList)), 6
                ),
                new FormColumn(
                    new SelectBox('Test[GradeType]', 'Zensuren-Typ', array('Name' => $tblGradeTypeList)), 6
                )
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Test[Description]', '1. Klassenarbeit', 'Beschreibung'), 12
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Test[Date]', '', 'Datum', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Test[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Test[ReturnDate]', '', 'R&uuml;ckgabedatum', new Calendar()), 4
                ),
            ))
        )));
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $Test
     *
     * @return Stage
     */
    public function frontendHeadmasterTestSelected(
        $DivisionSubjectId = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung (Leitung)', 'Übersicht');
        $this->contentTestSelected($DivisionSubjectId, $Test, $Stage,
            '/Education/Graduation/Evaluation/Headmaster/Test');

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Test
     *
     * @return Stage|string
     */
    public function frontendEditTest(
        $Id = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Bearbeiten');

        return $this->contentEditTest($Stage, $Id, $Test, '/Education/Graduation/Evaluation/Test');
    }

    /**
     * @param Stage $Stage
     * @param        $Id
     * @param        $Test
     * @param string $BasicRoute
     *
     * @return string
     */
    private function contentEditTest(Stage $Stage, $Id, $Test, $BasicRoute)
    {

        $tblTest = Evaluation::useService()->getTestById($Id);
        if ($tblTest) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Test']['Description'] = $tblTest->getDescription();
                $Global->POST['Test']['Date'] = $tblTest->getDate();
                $Global->POST['Test']['CorrectionDate'] = $tblTest->getCorrectionDate();
                $Global->POST['Test']['ReturnDate'] = $tblTest->getReturnDate();
                $Global->savePost();
            }

            if (!$tblTest->getServiceTblDivision()) {
                return new Danger(new Ban() . ' Klasse nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
            }
            if (!$tblTest->getServiceTblSubject()) {
                return new Danger(new Ban() . ' Fach nicht gefunden')
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
            }

            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                $tblTest->getServiceTblDivision(),
                $tblTest->getServiceTblSubject(),
                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
            );

            $Stage->addButton(
                new Standard('Zur&uuml;ck', $BasicRoute . '/Selected', new ChevronLeft()
                    , array('DivisionSubjectId' => $tblDivisionSubject->getId()))
            );

            $Form = new Form(new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Test[Description]', '1. Klassenarbeit', 'Beschreibung'), 12
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new DatePicker('Test[Date]', '', 'Datum', new Calendar()), 4
                    ),
                    new FormColumn(
                        new DatePicker('Test[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), 4
                    ),
                    new FormColumn(
                        new DatePicker('Test[ReturnDate]', '', 'R&uuml;ckgabedatum', new Calendar()), 4
                    ),
                ))
            )));
            $Form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $tblDivision = $tblTest->getServiceTblDivision();

            $Stage->setContent(
                new Layout (array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Fach-Klasse',
                                    'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                    ($tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getName() : '') .
                                    ($tblTest->getServiceTblSubjectGroup() ? new Small(
                                        ' (Gruppe: ' . $tblTest->getServiceTblSubjectGroup()->getName() . ')') : ''),
                                    Panel::PANEL_TYPE_INFO
                                ), 6
                            ),
                            new LayoutColumn(
                                new Panel('Zeitraum:',
                                    $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getName() : '',
                                    Panel::PANEL_TYPE_INFO), 3
                            ),
                            new LayoutColumn(
                                new Panel('Zensuren-Typ:',
                                    $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '',
                                    Panel::PANEL_TYPE_INFO), 3
                            )
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Evaluation::useService()->updateTest($Form, $tblTest->getId(), $Test,
                                    $BasicRoute))
                            )
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {

            return new Danger(new Ban() . ' Test nicht gefunden')
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     * @param $Test
     *
     * @return Stage|string
     */
    public function frontendHeadmasterEditTest(
        $Id = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung (Leitung)', 'Bearbeiten');

        return $this->contentEditTest($Stage, $Id, $Test, '/Education/Graduation/Evaluation/Headmaster/Test');
    }

    /**
     * @param $Id
     * @param $Grade
     *
     * @return Stage|string
     */
    public function frontendEditTestGrade(
        $Id = null,
        $Grade = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Zensuren bearbeiten');

        $tblTest = Evaluation::useService()->getTestById($Id);
        if ($tblTest) {

            // Klassenlehrer darf Noten editieren
            $tblPerson = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAllByAccount) {
                    $tblPerson = $tblPersonAllByAccount[0];
                }
            }

            if (!$tblTest->getServiceTblDivision()) {
                return new Danger(new Ban() . ' Klasse nicht gefunden')
                . new Redirect('/Education/Graduation/Evaluation/Test', Redirect::TIMEOUT_ERROR);
            }

            if (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblTest->getServiceTblDivision(),
                $tblPerson)
            ) {
                $isEdit = true;
            } else {
                $isEdit = false;
            }

            $this->contentEditTestGrade($Stage, $tblTest, $Grade, '/Education/Graduation/Evaluation/Test', $isEdit);

            return $Stage;
        } else {

            return new Danger(new Ban() . ' Test nicht gefunden')
            . new Redirect('/Education/Graduation/Evaluation/Test', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param Stage $Stage
     * @param TblTest $tblTest
     * @param $Grade
     * @param $BasicRoute
     * @param bool|false $IsEdit
     *
     * @return string
     */
    private function contentEditTestGrade(Stage $Stage, TblTest $tblTest, $Grade, $BasicRoute, $IsEdit = false)
    {

        if (!$tblTest->getServiceTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }
        if (!$tblTest->getServiceTblSubject()) {
            return new Danger(new Ban() . ' Fach nicht gefunden')
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );

        $Stage->addButton(
            new Standard('Zur&uuml;ck', $BasicRoute . '/Selected', new ChevronLeft()
                , array('DivisionSubjectId' => $tblDivisionSubject->getId()))
        );


        $isTestAppointedDateTask = ($tblTest->getTblTestType()->getId()
            == Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')->getId());
        $tblDivision = $tblTest->getServiceTblDivision();
        $tblSubject = $tblTest->getServiceTblSubject();

        $tblScoreRule = false;
        $scoreRuleText = array();
        $tblScoreType = false;
        $showPriority = false;
        if ($tblDivision && $tblSubject) {
            $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                $tblDivision,
                $tblSubject
            );
            if ($tblScoreRuleDivisionSubject) {
                if ($tblScoreRuleDivisionSubject->getTblScoreRule()) {
                    $tblScoreType = $tblScoreRuleDivisionSubject->getTblScoreType();
                    $tblScoreRule = $tblScoreRuleDivisionSubject->getTblScoreRule();
                    if ($tblScoreRule) {
                        if ($isTestAppointedDateTask) {
                            $scoreRuleText[] = new Bold($tblScoreRule->getName());
                            $tblScoreRuleConditionListByRule = Gradebook::useService()->getScoreRuleConditionListByRule($tblScoreRule);
                            if ($tblScoreRuleConditionListByRule) {

                                $showPriority = true;

                                $tblScoreRuleConditionListByRule =
                                    $this->getSorter($tblScoreRuleConditionListByRule)->sortObjectList('Priority');

                                /** @var TblScoreRuleConditionList $tblScoreRuleConditionList */
                                foreach ($tblScoreRuleConditionListByRule as $tblScoreRuleConditionList) {
                                    $scoreRuleText[] = '&nbsp;&nbsp;&nbsp;&nbsp;' . 'Priorität: '
                                        . $tblScoreRuleConditionList->getTblScoreCondition()->getPriority()
                                        . '&nbsp;&nbsp;&nbsp;' . $tblScoreRuleConditionList->getTblScoreCondition()->getName();
                                }
                            } else {
                                $scoreRuleText[] = new Bold(new Warning(
                                    new Ban() . ' Keine Berechnungsvariante hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                                ));
                            }
                        }
                    }
                }
            }
        }

        $gradeList = Gradebook::useService()->getGradeAllByTest($tblTest);

        /*
         * set post
         */
        if ($gradeList) {
            $Global = $this->getGlobal();
            /** @var TblGrade $grade */
            foreach ($gradeList as $grade) {
                if (empty($Grade)) {
                    if ($grade->getServiceTblPerson()) {
                        if ($grade->getGrade() === null) {
                            $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Attendance'] = 1;
                        } else {
                            if ($IsEdit) {
                                $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Grade'] = $grade->getGrade();

                                $trend = $grade->getTrend();
                                if ($trend !== null) {
                                    $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Trend'] = $trend;
                                }
                            } else {
                                $trend = $grade->getTrend();
                                if ($trend !== null) {
                                    if ($trend == TblGrade::VALUE_TREND_PLUS) {
                                        $trend = '+';
                                    } elseif ($trend == TblGrade::VALUE_TREND_MINUS) {
                                        $trend = '-';
                                    } else {
                                        $trend = '';
                                    }
                                } else {
                                    $trend = '';
                                }
                                $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Grade'] = $grade->getGrade() . $trend;
                            }

                        }
                        $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Comment'] = $grade->getComment();
                    }
                }
            }
            $Global->savePost();
        }

        /*
         * set grade mirror
         */
        $minRange = null;
        $maxRange = null;
        if ($tblScoreType) {
            $gradeMirror = array();
            $gradeMirror[] = new Bold('Bewertungssystem: ' . $tblScoreType->getName());
            if ($tblScoreType->getIdentifier() == 'VERBAL') {
                $gradeMirror[] = new Bold(new Warning(new Exclamation() . ' Für die verbale Bewertung ist kein Notenspiegel verfügbar.'));
            } else {
                $mirror = array();
                $count = 0;

                if ($tblScoreType->getIdentifier() == 'GRADES') {
                    $minRange = 1;
                    $maxRange = 6;
                    $description = 'Note ';
                } else {
                    $minRange = 0;
                    $maxRange = 15;
                    $description = 'Punkte ';
                }

                for ($i = $minRange; $i <= $maxRange; $i++) {
                    $mirror[$i] = 0;
                }

                if ($gradeList) {
                    /** @var TblGrade $grade */
                    foreach ($gradeList as $grade) {
                        if (empty($Grade)) {

                            if (is_numeric($grade->getGrade())) {
                                $gradeValue = floor(floatval($grade->getGrade()));
                                if ($gradeValue >= $minRange && $gradeValue <= $maxRange) {
                                    $mirror[$grade->getGrade()]++;
                                    $count++;
                                }
                            }
                        }
                    }
                }

                for ($i = $minRange; $i <= $maxRange; $i++) {
                    if (isset($mirror[$i])) {
                        $gradeMirror[] = $description . new Bold($i) . ': ' . $mirror[$i] .
                            ($count > 0 ? ' (' . (round(($mirror[$i] / $count) * 100, 0)) . '%)' : '');
                    }
                }
            }
        } else {
            $gradeMirror = new Bold(new Warning(
                new Ban() . ' Kein Bewertungssystem hinterlegt.'
            ));
        }

        $studentList = array();
        $errorRowList = array();

        $tblTask = $tblTest->getTblTask();
        $IsTaskAndInPeriod = true;
        if ($tblTask && !$tblTask->isInEditPeriod()) {
            $IsTaskAndInPeriod = false;
        }

        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblSubjectStudentAllByDivisionSubject = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
            if ($tblSubjectStudentAllByDivisionSubject) {
                foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                    $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                    if ($tblPerson) {

                        $average = false;
                        $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName()
                            . ($average ? new Bold('&nbsp;&nbsp;&#216; ' . $average) : '');
                        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                            $tblPerson);

                        $studentList = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $studentList,
                            $IsTaskAndInPeriod, $tblScoreType ? $tblScoreType : null
                        );
                    }
                }
            }
        } else {
            $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentAll) {
                foreach ($tblDivisionStudentAll as $tblPerson) {

                    $average = false;
                    $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName()
                        . ($average ? new Bold('&nbsp;&nbsp;&#216; ' . $average) : '');
                    $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                        $tblPerson);

                    $studentList = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $studentList,
                        $IsTaskAndInPeriod, $tblScoreType ? $tblScoreType : null
                    );
                }
            }
        }
        if ($tblTask) {
            $period = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
            $tableColumns = array(
                'Name' => 'Schüler',
            );

            // Stichtagsnotenauftrag
            if ($isTestAppointedDateTask) {

                $gradeType = 'Stichtagsnote' . ($tblTask->getServiceTblPeriod()
                        ? new Small(new Muted(' ' . $tblTask->getServiceTblPeriod()->getName()))
                        : new Small(new Muted(' Gesamtes Schuljahr')));

                foreach ($studentList as $personId => $student) {
                    $tblPerson = Person::useService()->getPersonById($personId);
                    $tblYearAll = Term::useService()->getYearAllByDate(DateTime::createFromFormat('d.m.Y',
                        $tblTask->getDate()));
                    if ($tblYearAll) {
                        foreach ($tblYearAll as $tblYear) {
                            if ($tblTask->getServiceTblPeriod()) {
                                $tblPeriodList = array($tblTask->getServiceTblPeriod());
                            } else {
                                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                            }
                            if ($tblPeriodList) {
                                foreach ($tblPeriodList as $tblPeriod) {
                                    $tblGrades = Gradebook::useService()->getGradesByStudent(
                                        $tblPerson,
                                        $tblDivision,
                                        $tblTest->getServiceTblSubject(),
                                        Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                        $tblPeriod
                                    );
                                    if ($tblGrades) {
                                        $tableColumns['Period' . $tblPeriod->getId()] = $tblPeriod->getName();
                                        foreach ($tblGrades as $tblGrade) {
                                            $tblGradeType = $tblGrade->getTblGradeType();

                                            $gradeValue = $tblGrade->getGrade();
                                            $trend = $tblGrade->getTrend();
                                            if (TblGrade::VALUE_TREND_PLUS === $trend) {
                                                $gradeValue .= '+';
                                            } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                                                $gradeValue .= '-';
                                            }

                                            $grade = $tblGrade->getGrade()
                                                ? ($tblGradeType->isHighlighted()
                                                    ? new Bold($gradeValue . ' (' . $tblGradeType->getCode() . ')')
                                                    : $gradeValue . ' (' . $tblGradeType->getCode() . ')')
                                                : '';

                                            if (isset($studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()])) {
                                                $studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()]
                                                    .= '&nbsp;&nbsp;&nbsp;' . $grade;
                                            } else {
                                                $studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()] = $grade;
                                            }
                                        }

//                                        $average = Gradebook::useService()->calcStudentGrade(
//                                            $tblPerson,
//                                            $tblDivision,
//                                            $tblDivisionSubject->getServiceTblSubject(),
//                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
//                                            $tblScoreRule ? $tblScoreRule : null,
//                                            $tblPeriod
//                                        );
//                                        if (is_array($average)) {
//                                            $errorRowList = $average;
//                                            $average = ' ';
//                                        }
//                                        if ($average) {
//                                            $studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()]
//                                                .= '&nbsp;&nbsp;&nbsp;' . new Bold('&#216; ' . $average);
//                                        }
                                    }
                                }
                            }
                        }

                    }

                    if ($showPriority) {
                        $tableColumns['Priority'] = 'Priorität';
                    }
                    $tableColumns['Average'] = '&#216;';

                    $average = Gradebook::useService()->calcStudentGrade(
                        $tblPerson,
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                        $tblScoreRule ? $tblScoreRule : null,
                        $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null
                    );
                    if (is_array($average)) {
                        $errorRowList = $average;
                        $average = ' ';
                        $priority = '';
                    } else {
                        $priority = '';
                        $posStart = strpos($average, '(');
                        if ($posStart !== false) {
                            $posEnd = strpos($average, ')');
                            if ($posEnd) {
                                $priority = substr($average, $posStart + 1, $posEnd - $posStart - 1);
                            }
                            $average = substr($average, 0, $posStart);
                        }
                    }

                    if ($average) {
                        $studentList[$tblPerson->getId()]['Average']
                            = new Bold($average);
                    }
                    if ($showPriority) {
                        $studentList[$tblPerson->getId()]['Priority'] = $priority;
                    }

                }
            } else {
                // Kopfnote
                $gradeType = 'Kopfnote: ' . ($tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '')
                    . ($tblTask->getServiceTblPeriod()
                        ? new Small(new Muted(' ' . $tblTask->getServiceTblPeriod()->getName()))
                        : new Small(new Muted(' Gesamtes Schuljahr')));
            }

            $tableColumns['Grade'] = 'Zensur';
            $tableColumns['Comment'] = 'Optionaler Grund';
        } else {
            $period = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getName() : '';
            $gradeType = $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '';

            $tableColumns = array(
                'Name' => 'Schüler',
                'Grade' => 'Zensur',
                'Comment' => 'Optionaler Grund',
                'Attendance' => 'Nicht teilgenommen'
            );
        }

        $Stage->setContent(
            new Layout (array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Fach-Klasse',
                                'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                ($tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getName() : '') .
                                ($tblTest->getServiceTblSubjectGroup() ? new Small(
                                    ' (Gruppe: ' . $tblTest->getServiceTblSubjectGroup()->getName() . ')') : ''),
                                Panel::PANEL_TYPE_INFO
                            ), 6
                        ),
                        new LayoutColumn(
                            new Panel('Zeitraum:',
                                $period,
                                Panel::PANEL_TYPE_INFO), 3
                        ),
                        new LayoutColumn(
                            new Panel('Zensuren-Typ:',
                                $gradeType,
                                Panel::PANEL_TYPE_INFO), 3
                        ),
                        ($isTestAppointedDateTask ? new LayoutColumn(new Panel(
                            'Berechnungsvorschrift',
                            $tblScoreRule ? $scoreRuleText : new Bold(new Warning(
                                new Ban() . ' Keine Berechnungsvorschrift hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                            )),
                            Panel::PANEL_TYPE_INFO
                        ), 12) : null),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Notenspiegel',
                                $gradeMirror,
                                Panel::PANEL_TYPE_PRIMARY
                            )
                        )
                    )),
                    (($tblTask && !$IsEdit && !$tblTask->isInEditPeriod())
                        ? new LayoutRow(new LayoutColumn(new \SPHERE\Common\Frontend\Message\Repository\Warning(
                                'Sie befinden sich nicht mehr im Bearbeitungszeitraum.
                            Zensuren können von Ihnen nicht mehr eingetragen werden.', new Exclamation())
                        ))
                        : null
                    ),
                )),
                (!empty($errorRowList) ? new LayoutGroup($errorRowList) : null),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Gradebook::useService()->updateGradeToTest(
                                new Form(
                                    new FormGroup(array(
                                        new FormRow(
                                            new FormColumn(
                                                new TableData($studentList, null, $tableColumns, null)
                                            )
                                        ),
                                    ))
                                    , new Primary('Speichern', new Save()))
                                , $tblTest->getId(), $Grade, $BasicRoute, $IsEdit, $minRange, $maxRange
                            )
                        )
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $tblGrade
     * @param $IsEdit
     * @param $student
     * @param TblScoreType|null $tblScoreType
     * @param bool $IsTaskAndInPeriod
     *
     * @return array
     */
    private function contentEditTestGradeTableRow(
        TblPerson $tblPerson,
        $tblGrade,
        $IsEdit,
        $student,
        $IsTaskAndInPeriod = false,
        TblScoreType $tblScoreType = null
    ) {

        if ($tblScoreType === null) {
            $tblScoreType = false;
        }

        $selectBoxContent = array(
            TblGrade::VALUE_TREND_NULL => '',
            TblGrade::VALUE_TREND_PLUS => 'Plus',
            TblGrade::VALUE_TREND_MINUS => 'Minus'
        );

        if (!$IsEdit && $tblGrade) {
            $student = $this->setGradeDisabled($tblPerson, $student);
        } elseif (!$IsEdit && !$IsTaskAndInPeriod) {
            $student = $this->setGradeDisabled($tblPerson, $student);
        } else {
            if ($tblScoreType) {
                if ($tblScoreType->getIdentifier() == 'VERBAL') {
                    $student[$tblPerson->getId()]['Grade']
                        = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', '', new Quote()));
                } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                    $student[$tblPerson->getId()]['Grade']
                        = (new NumberField('Grade[' . $tblPerson->getId() . '][Grade]', '', '', new Rate15()));
                } else {
                    $student[$tblPerson->getId()]['Grade']
                        = (new NumberField('Grade[' . $tblPerson->getId() . '][Grade]', '', '', new Dice()))
                        . (new SelectBox('Grade[' . $tblPerson->getId() . '][Trend]', '', $selectBoxContent,
                            new ResizeVertical()));
                }
            } else {
                $student[$tblPerson->getId()]['Grade']
                    = (new NumberField('Grade[' . $tblPerson->getId() . '][Grade]', '', '', new Dice()))
                    . (new SelectBox('Grade[' . $tblPerson->getId() . '][Trend]', '', $selectBoxContent,
                        new ResizeVertical()));
            }
            $student[$tblPerson->getId()]['Comment']
                = (new TextField('Grade[' . $tblPerson->getId() . '][Comment]', '', '', new Comment()));
            $student[$tblPerson->getId()]['Attendance'] =
                (new CheckBox('Grade[' . $tblPerson->getId() . '][Attendance]', ' ', 1));
        }

        return $student;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $student
     * @return mixed
     */
    private function setGradeDisabled(TblPerson $tblPerson, $student)
    {
        $student[$tblPerson->getId()]['Grade']
            = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', ''))->setDisabled();
        $student[$tblPerson->getId()]['Comment']
            = (new TextField('Grade[' . $tblPerson->getId() . '][Comment]', '', '', new Comment()))->setDisabled();
        $student[$tblPerson->getId()]['Attendance'] =
            (new CheckBox('Grade[' . $tblPerson->getId() . '][Attendance]', ' ', 1))->setDisabled();
        return $student;
    }

    /**
     * @param $Id
     * @param $Grade
     *
     * @return Stage|string
     */
    public function frontendHeadmasterEditTestGrade(
        $Id = null,
        $Grade = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Zensuren bearbeiten');

        $tblTest = Evaluation::useService()->getTestById($Id);
        if ($tblTest) {

            $this->contentEditTestGrade($Stage, $tblTest, $Grade, '/Education/Graduation/Evaluation/Headmaster/Test',
                true);

            return $Stage;
        } else {

            return new Danger(new Ban() . ' Test nicht gefunden')
            . new Redirect('/Education/Graduation/Evaluation/Headmaster/Test', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Task
     *
     * @return Stage
     */
    public function frontendHeadmasterTask($Task = null)
    {

        $Stage = new Stage('Notenaufträge', 'Übersicht');
        $Stage->setMessage(
            'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebener Zensuren).'
        );

        $tblTaskAll = Evaluation::useService()->getTaskAll();

        if ($tblTaskAll) {
            foreach ($tblTaskAll as $tblTask) {
                $tblTask->Type = $tblTask->getTblTestType()->getName();
                $tblTask->EditPeriod = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                $tblTask->Period = $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod()->getName() : 'Gesamtes Schuljahr';
                $tblTask->Option =
                    (new Standard('',
                        '/Education/Graduation/Evaluation/Headmaster/Task/Edit',
                        new Edit(),
                        array('Id' => $tblTask->getId()),
                        'Bearbeiten'))
                    . (new Standard('',
                        '/Education/Graduation/Evaluation/Headmaster/Task/Division',
                        new Listing(),
                        array('Id' => $tblTask->getId()),
                        'Klassen auswählen')
                    )
                    . (new Standard('',
                        '/Education/Graduation/Evaluation/Headmaster/Task/Grades',
                        new Equalizer(),
                        array('Id' => $tblTask->getId()),
                        'Zensuren ansehen')
                    );
            }
        }

        $Form = ($this->formTask());
        $Form
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new TableData(
                                    $tblTaskAll, null, array(
                                        'Date' => 'Stichtag',
                                        'Type' => 'Kategorie',
                                        'Name' => 'Name',
                                        'Period' => 'Noten-Zeitraum',
                                        'EditPeriod' => 'Bearbeitungszeitraum',
                                        'Option' => '',
                                    )
                                )
                            )
                        )
                    )
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Evaluation::useService()->createTask($Form, $Task))
                        )
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formTask()
    {

        $tblTestTypeAllWhereTask = Evaluation::useService()->getTestTypeAllWhereTask();
        $tblYearAllByNow = Term::useService()->getYearByNow();
        $periodSelect[-1] = 'Gesamtes Schuljahr';
        if ($tblYearAllByNow) {
            foreach ($tblYearAllByNow as $tblYear) {
                $tblPeriodAllByYear = Term::useService()->getPeriodAllByYear($tblYear);
                if ($tblPeriodAllByYear) {
                    foreach ($tblPeriodAllByYear as $tblPeriod) {
                        $periodSelect[$tblPeriod->getId()] = $tblPeriod->getName() .
                            ($tblPeriod->getDescription() !== '' ? ' - ' . $tblPeriod->getDescription() : '');
                    }
                }
            }
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Task[Type]', 'Kategorie', array('Name' => $tblTestTypeAllWhereTask)), 3
                ),
                new FormColumn(
                    new TextField('Task[Name]', '', 'Name'), 9
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Task[Period]', 'Noten-Zeitraum', $periodSelect), 3
                ),
                new FormColumn(
                    new DatePicker('Task[Date]', '', 'Stichtag', new Calendar()), 3
                ),
                new FormColumn(
                    new DatePicker('Task[FromDate]', '', 'Bearbeitungszeitraum von', new Calendar()), 3
                ),
                new FormColumn(
                    new DatePicker('Task[ToDate]', '', 'Bearbeitungszeitraum bis', new Calendar()), 3
                ),
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $Task
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskEdit($Id = null, $Task = null)
    {

        $Stage = new Stage('Notenauftrag', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Headmaster/Task',
                new ChevronLeft())
        );

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Task']['Type'] = $tblTask->getTblTestType()->getId();
                $Global->POST['Task']['Name'] = $tblTask->getName();
                $Global->POST['Task']['Date'] = $tblTask->getDate();
                $Global->POST['Task']['FromDate'] = $tblTask->getFromDate();
                $Global->POST['Task']['ToDate'] = $tblTask->getToDate();
                $period = $tblTask->getServiceTblPeriod();
                if ($period) {
                    $period = $period->getId();
                } else {
                    $period = -1;
                }
                $Global->POST['Task']['Period'] = $period;
                $Global->savePost();
            }

            $Form = ($this->formTask());
            $Form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    $tblTask->getTblTestType()->getName(),
                                    $tblTask->getName() . ' ' . $tblTask->getDate()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Evaluation::useService()->updateTask($Form, $tblTask->getId(), $Task))
                            )
                        ))
                    ), new Title(new Edit() . ' Bearbeiten')),
                ))
            );
        } else {
            $Stage .= new Danger(new Ban() . ' Stichtagsauftrag nicht gefunden.')
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskDivision($Id = null, $Data = null)
    {

        $Stage = new Stage('Notenauftrag', 'Klassen zuordnen');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Headmaster/Task',
                new ChevronLeft())
        );

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {

            if ($tblTask->getTblTestType()->getIdentifier() == 'BEHAVIOR_TASK') {
                $isBehaviorTask = true;
            } else {
                $isBehaviorTask = false;
            }

            $tblTestAllByTest = Evaluation::useService()->getTestAllByTask($tblTask);
            if ($tblTestAllByTest) {
                $Global = $this->getGlobal();
                if (!$Global->POST) {
                    foreach ($tblTestAllByTest as $tblTest) {
                        $tblDivision = $tblTest->getServiceTblDivision();
                        if ($tblDivision) {
                            $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                        }
                        if ($isBehaviorTask) {
                            $tblGradeType = $tblTest->getServiceTblGradeType();
                            if ($tblGradeType) {
                                $Global->POST['Data']['GradeType'][$tblGradeType->getId()] = 1;
                            }
                        }
                    }
                    $Global->savePost();
                }
            }

            $schoolTypeList = array();
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblYear);
                    if ($tblDivisionAllByYear) {
                        foreach ($tblDivisionAllByYear as $tblDivision) {
                            $type = $tblDivision->getTblLevel()->getServiceTblType();
                            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                            if ($type && $tblDivisionSubjectList) {
                                $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                            }
                        }
                    }
                }
            }

            $gradeTypeColumnList = array();
            if ($isBehaviorTask) {
                $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType(
                    Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR')
                );
                if ($tblGradeTypeList) {
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        $gradeTypeColumnList[] = new FormColumn(
                            new CheckBox('Data[GradeType][' . $tblGradeType->getId() . ']', $tblGradeType->getName(),
                                1), 1
                        );
                    }
                }
            }

            $columnList = array();
            if (!empty($schoolTypeList)) {
                foreach ($schoolTypeList as $typeId => $divisionList) {
                    $type = Type::useService()->getTypeById($typeId);
                    if ($type && is_array($divisionList)) {

                        asort($divisionList, SORT_NATURAL);

                        $checkBoxList = array();
                        foreach ($divisionList as $key => $value) {
                            $checkBoxList[] = new CheckBox('Data[Division][' . $key . ']', $value, 1);
                        }

                        $panel = new Panel($type->getName(), $checkBoxList, Panel::PANEL_TYPE_DEFAULT);
                        $columnList[] = new FormColumn($panel, 3);
                    }
                }
            }

            $form = new Form(array(
                $isBehaviorTask
                    ? new FormGroup(array(
                    new FormRow(
                        $gradeTypeColumnList
                    )
                ),
                    new \SPHERE\Common\Frontend\Form\Repository\Title('Kopfnoten')
                )
                    : null,
                new FormGroup(
                    new FormRow(
                        $columnList
                    )
                    , new \SPHERE\Common\Frontend\Form\Repository\Title('<br> Klassen'))
            ));
            $form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    $tblTask->getTblTestType()->getName(),
                                    $tblTask->getName() . ' ' . $tblTask->getDate()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )
                        ))
                    )),
                ))
                . new Well(Evaluation::useService()->updateDivisionTasks($form, $tblTask->getId(), $Data))
            );
        } else {
            $Stage .= new Danger('Notenauftrag nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @return Stage|string
     */
    public function frontendHeadmasterTaskGrades($Id = null)
    {
        $Stage = new Stage('Notenauftrag', 'Zensurenübersicht');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Headmaster/Task',
                new ChevronLeft())
        );

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {

            $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);

            $divisionList = array();
            if ($tblTestAllByTask) {
                foreach ($tblTestAllByTask as $tblTest) {
                    $tblDivision = $tblTest->getServiceTblDivision();
                    if ($tblDivision) {
                        $divisionList[$tblDivision->getId()][$tblTest->getId()] = $tblTest;
                    }
                }
            }

            $tableList = array();
            $studentList = array();
            $tableHeaderList = array();
            if (!empty($divisionList)) {

                $tableList = $this->setGradeOverviewForTask($tblTask, $divisionList, $tableHeaderList, $studentList,
                    $tableList);
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    $tblTask->getTblTestType()->getName(),
                                    $tblTask->getName() . ' ' . $tblTask->getDate()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )
                        ))
                    )),
                ))
                . new Layout($tableList)
            );
        } else {
            $Stage .= new Danger(' Notenauftrag nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblPerson $tblPerson
     * @param $studentList
     * @return $studentList
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $studentList
    ) {
        $studentList[$tblDivision->getId()][$tblPerson->getId()]['Name'] =
            $tblPerson->getLastFirstName();
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();
        $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject($tblDivision,
            $tblSubject);
        if ($tblScoreRuleDivisionSubject) {
            $tblScoreRule = $tblScoreRuleDivisionSubject->getTblScoreRule();
        } else {
            $tblScoreRule = false;
        }
        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson,
            $tblDivision,
            $tblSubject,
            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null
        );
        if (is_array($average)) {
//            $errorRowList = $average;
            $average = ' ';
        } else {
            $posStart = strpos($average, '(');
            if ($posStart !== false) {
                $average = substr($average, 0, $posStart);
            }
        }

        if ($tblGrade) {
            $gradeValue = $tblGrade->getGrade();
            $trend = $tblGrade->getTrend();

            $isGradeInRange = true;
            if ($average !== ' ' && $average && $gradeValue !== null) {
                if (is_numeric($gradeValue)) {
                    $gradeFloat = floatval($gradeValue);
                    if (($gradeFloat - 0.5) <= $average && ($gradeFloat + 0.5) >= $average) {
                        $isGradeInRange = true;
                    } else {
                        $isGradeInRange = false;
                    }
                }
            }

            if (TblGrade::VALUE_TREND_PLUS === $trend) {
                $gradeValue .= '+';
            } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                $gradeValue .= '-';
            }

            if ($isGradeInRange) {
                $gradeValue = new Success($gradeValue);
            } else {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Danger($gradeValue);
            }

            $studentList[$tblDivision->getId()][$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] = ($tblGrade->getGrade() !== null ?
                    $gradeValue : '') . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        } else {
            $studentList[$tblDivision->getId()][$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] =
                new Warning('fehlt')
                . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param $grades
     * @return array
     */
    private function setTableContentForBehaviourTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblPerson $tblPerson,
        $studentList,
        $grades
    ) {
        $studentList[$tblDivision->getId()][$tblPerson->getId()]['Name'] =
            $tblPerson->getLastFirstName();
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        if ($tblTest->getServiceTblGradeType() && $tblTest->getServiceTblSubject()) {
            $gradeTypeId = $tblTest->getServiceTblGradeType()->getId();
            $tblSubject = $tblTest->getServiceTblSubject();
            if ($tblGrade) {
                $gradeText = $tblSubject->getAcronym() . ': ' . ($tblGrade->getGrade() !== null ?
                        $tblGrade->getGrade() : '');
                if (is_numeric($tblGrade->getGrade())) {
                    if (isset($grades[$tblPerson->getId()][$gradeTypeId]['Count'])) {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Count']++;
                    } else {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Count'] = 1;
                    }
                    if (isset($grades[$tblPerson->getId()][$gradeTypeId]['Sum'])) {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Sum']
                            = floatval($grades[$tblPerson->getId()][$gradeTypeId]['Sum']) + floatval($tblGrade->getGrade());
                    } else {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Sum'] = floatval($tblGrade->getGrade());
                    }
                }
            } else {
                $gradeText = $tblSubject->getAcronym() . ': ' .
                    new Warning('f');
            }

            if (!isset($studentList[$tblDivision->getId()][$tblPerson->getId()]['Type' . $gradeTypeId])) {
                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                ['Type' . $gradeTypeId] = new Small(new Small($gradeText));
                return array($studentList, $grades);
            } else {
                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                ['Type' . $gradeTypeId] .= new Small(new Small(' | ' . $gradeText));
                return array($studentList, $grades);
            }
        }
        return array($studentList, $grades);
    }

    /**
     * @return Stage
     */
    public function frontendDivisionTeacherTask()
    {

        $Stage = new Stage('Notenaufträge', 'Übersicht');
        $Stage->setMessage(
            'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebener Zensuren),
            wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.'
        );

        $taskList = array();

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }
        if ($tblPerson) {
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherAllByTeacher) {
                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                    $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
                    $tblTestList = Evaluation::useService()->getTestAllByTestTypeAndDivision(
                        $tblTestType,
                        $tblDivisionTeacher->getTblDivision()
                    );
                    if ($tblTestList) {
                        foreach ($tblTestList as $tblTest) {
                            $taskList[$tblTest->getTblTask()->getId()] = $tblTest->getTblTask();
                        }
                    }
                    $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
                    $tblTestList = Evaluation::useService()->getTestAllByTestTypeAndDivision(
                        $tblTestType,
                        $tblDivisionTeacher->getTblDivision()
                    );
                    if ($tblTestList) {
                        foreach ($tblTestList as $tblTest) {
                            $taskList[$tblTest->getTblTask()->getId()] = $tblTest->getTblTask();
                        }
                    }
                }
            }
        }


        if (!empty($taskList)) {
            /** @var TblTask $tblTask */
            foreach ($taskList as $tblTask) {
                $tblTask->Type = $tblTask->getTblTestType()->getName();
                $tblTask->EditPeriod = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                $tblTask->Period = $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod()->getName() : 'Gesamtes Schuljahr';
                $tblTask->Option =
                    (new Standard('',
                        '/Education/Graduation/Evaluation/DivisionTeacher/Task/Grades',
                        new Equalizer(),
                        array('Id' => $tblTask->getId()),
                        'Zensuren ansehen')
                    );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new TableData(
                                    $taskList, null, array(
                                        'Date' => 'Stichtag',
                                        'Type' => 'Kategorie',
                                        'Name' => 'Name',
                                        'Period' => 'Noten-Zeitraum',
                                        'EditPeriod' => 'Bearbeitungszeitraum',
                                        'Option' => '',
                                    )
                                )
                            )
                        )
                    )
                ), new Title(new ListingTable() . ' Übersicht')),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @return Stage|string
     */
    public function frontendDivisionTeacherTaskGrades($Id = null)
    {
        $Stage = new Stage('Notenauftrag', 'Zensurenübersicht');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/DivisionTeacher/Task',
                new ChevronLeft())
        );

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {

            $tblPerson = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAllByAccount) {
                    $tblPerson = $tblPersonAllByAccount[0];
                }
            }
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);

            $divisionList = array();
            if ($tblTestAllByTask) {
                foreach ($tblTestAllByTask as $tblTest) {
                    $tblDivision = $tblTest->getServiceTblDivision();
                    if ($tblDivision) {
                        if ($tblDivisionTeacherAllByTeacher) {
                            foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                                if ($tblDivision->getId() == $tblDivisionTeacher->getTblDivision()->getId()) {
                                    $divisionList[$tblDivision->getId()][$tblTest->getId()] = $tblTest;
                                }
                            }
                        }
                    }
                }
            }

            $tableList = array();
            $studentList = array();
            $tableHeaderList = array();
            if (!empty($divisionList)) {

                $tableList = $this->setGradeOverviewForTask($tblTask, $divisionList, $tableHeaderList, $studentList,
                    $tableList);
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    $tblTask->getTblTestType()->getName(),
                                    $tblTask->getName() . ' ' . $tblTask->getDate()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )
                        ))
                    )),
                ))
                . new Layout($tableList)
            );
        } else {
            $Stage .= new Danger(' Notenauftrag nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param TblTask $tblTask
     * @param $divisionList
     * @param $tableHeaderList
     * @param $studentList
     * @param $tableList
     * @return array
     */
    private function setGradeOverviewForTask(
        TblTask $tblTask,
        $divisionList,
        $tableHeaderList,
        $studentList,
        $tableList
    ) {
        foreach ($divisionList as $divisionId => $testList) {
            $tblDivision = Division::useService()->getDivisionById($divisionId);

            // Stichtagsnote
            if ($tblTask->getTblTestType()->getId() == Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')) {
                if (!empty($testList)) {
                    /** @var TblTest $tblTest */
                    foreach ($testList as $tblTest) {
                        $tblSubject = $tblTest->getServiceTblSubject();
                        if ($tblSubject && $tblTest->getServiceTblDivision()) {
                            $tableHeaderList[$tblDivision->getId()]['Name'] = 'Schüler';
                            $tableHeaderList[$tblDivision->getId()]['Subject' . $tblSubject->getId()] = $tblSubject->getAcronym();

                            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                $tblTest->getServiceTblDivision(),
                                $tblSubject,
                                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                            );

                            if ($tblDivisionSubject->getTblSubjectGroup()) {
                                $tblSubjectStudentAllByDivisionSubject =
                                    Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                if ($tblSubjectStudentAllByDivisionSubject) {
                                    foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                        $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                        if ($tblPerson) {
                                            $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                $tblTest, $tblSubject, $tblPerson, $studentList);
                                        }
                                    }
                                }
                            } else {
                                $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
                                if ($tblDivisionStudentAll) {
                                    foreach ($tblDivisionStudentAll as $tblPerson) {

                                        $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                            $tblTest, $tblSubject, $tblPerson, $studentList);
                                    }
                                }
                            }
                        }
                    }
                }

                // Bug Schüler ist nicht in der Gruppe, wenn nicht alle Schüler in einer Gruppe sind, z.B. bei Ethik
                if (!empty($studentList)) {
                    foreach ($studentList as $divisionListId => $students) {
                        if (is_array($students)) {
                            foreach ($students as $studentId => $student) {
                                foreach ($tableHeaderList[$divisionListId] as $key => $value) {
                                    if (!isset($student[$key])) {
                                        $studentList[$divisionId][$studentId][$key] = "";
                                    }
                                }
                            }
                        }
                    }
                }

            } else {

                // Kopfnoten
                $tableHeaderList[$tblDivision->getId()]['Name'] = 'Schüler';
                $grades = array();

                if (!empty($testList)) {
                    /** @var TblTest $tblTest */
                    foreach ($testList as $tblTest) {
                        $tblGradeType = $tblTest->getServiceTblGradeType();
                        if ($tblGradeType && $tblTest->getServiceTblDivision() && $tblTest->getServiceTblSubject()) {

                            $tableHeaderList[$tblDivision->getId()]['Type' . $tblGradeType->getId()]
                                = $tblGradeType->getCode() . ' (' . $tblGradeType->getName() . ')';

                            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                $tblTest->getServiceTblDivision(),
                                $tblTest->getServiceTblSubject(),
                                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                            );

                            if ($tblDivisionSubject->getTblSubjectGroup()) {
                                $tblSubjectStudentAllByDivisionSubject =
                                    Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                if ($tblSubjectStudentAllByDivisionSubject) {
                                    foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                        $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                        if ($tblPerson) {
                                            list($studentList, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                                $tblTest, $tblPerson, $studentList, $grades);
                                        }
                                    }
                                }
                            } else {
                                $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
                                if ($tblDivisionStudentAll) {
                                    foreach ($tblDivisionStudentAll as $tblPerson) {

                                        list($studentList, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                            $tblTest, $tblPerson, $studentList, $grades);
                                    }
                                }
                            }
                        }
                    }

                    // calc Average
                    foreach ($studentList[$tblDivision->getId()] as $personId => $studentListByDivision) {
                        $tblPerson = Person::useService()->getPersonById($personId);
                        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
                        $tblGradeTypeAllWhereBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                        if ($tblPerson && $tblGradeTypeAllWhereBehavior) {
                            foreach ($tblGradeTypeAllWhereBehavior as $tblGradeType) {
                                $gradeTypeId = $tblGradeType->getId();
                                if (isset($grades[$personId][$gradeTypeId]) && $grades[$personId][$gradeTypeId]['Count'] > 0) {
                                    $studentList[$tblDivision->getId()][$personId]['Type' . $gradeTypeId] =
                                        new Bold('&#216; ' .
                                            round(floatval($grades[$personId][$gradeTypeId]['Sum']) / floatval($grades[$personId][$gradeTypeId]['Count']),
                                                2) . ' | ') . $studentListByDivision['Type' . $gradeTypeId];
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($tableHeaderList)) {
            foreach ($tableHeaderList as $divisionId => $tableHeader) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                $tableList[] =
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Title('Klasse', $tblDivision->getDisplayName()),
                                new TableData(
                                    isset($studentList[$tblDivision->getId()]) ? $studentList[$tblDivision->getId()] : array(),
                                    null,
                                    $tableHeader,
                                    null
                                )
                            ))
                        )
                    );
            }
            return $tableList;
        }
        return $tableList;
    }

}
