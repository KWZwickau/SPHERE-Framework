<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:39
 */

namespace SPHERE\Application\Education\Graduation\Evaluation;

use DateTime;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
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
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
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
                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                        [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                            = $tblDivisionSubject->getId();
                    } else {
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

            // Klassenlehrer
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherAllByTeacher) {
                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                    $tblDivisionSubjectAllByDivision
                        = Division::useService()->getDivisionSubjectByDivision($tblDivisionTeacher->getTblDivision());
                    if ($tblDivisionSubjectAllByDivision) {
                        foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
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
                                'Year' => $tblDivision->getServiceTblYear()->getName(),
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
                            'Year' => $tblDivision->getServiceTblYear()->getName(),
                            'Type' => $tblDivision->getDisplayName(),
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
            new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
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
                ))
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

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as $tblDivision) {
                $tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                if ($tblDivisionSubjectAllByDivision) {
                    foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
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

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                foreach ($subjectList as $subjectId => $value) {
                    $tblSubject = Subject::useService()->getSubjectById($subjectId);
                    if (is_array($value)) {
                        foreach ($value as $subjectGroupId => $subValue) {
                            $item = Division::useService()->getSubjectGroupById($subjectGroupId);
                            $divisionSubjectTable[] = array(
                                'Year' => $tblDivision->getServiceTblYear()->getName(),
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
                            'Year' => $tblDivision->getServiceTblYear()->getName(),
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
            new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
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
                ))
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

        $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision,
            $tblDivisionSubject->getServiceTblSubject(),
            null,
            null,
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
        );
        if ($tblTestList) {
            array_walk($tblTestList, function (TblTest &$tblTest) use (&$BasicRoute) {

                $tblDivision = $tblTest->getServiceTblDivision();
                if ($tblDivision) {
                    $tblTest->Division = $tblDivision->getDisplayName();
                } else {
                    $tblTest->Division = '';
                }
                $tblTask = $tblTest->getTblTask();
                $tblTest->Subject = $tblTest->getServiceTblSubject()->getName();
                $tblTest->Period = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getName() : '';
                if ($tblTest->getServiceTblGradeType()) {
                    $tblTest->GradeType = $tblTest->getServiceTblGradeType()->getName();
                } elseif ($tblTask) {
                    $tblTest->GradeType = new Bold('Stichtagsnote');
                } else {
                    $tblTest->GradeType = '';
                }
                if ($tblTask) {
                    $tblTest->DisplayDescription = $tblTask->getName();
                    $tblTest->DisplayPeriod = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                } else {
                    $tblTest->DisplayDescription = $tblTest->getDescription();
                    $tblTest->DisplayPeriod = $tblTest->getServiceTblPeriod()->getName();
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
                    new \SPHERE\Common\Frontend\Text\Repository\Warning($countGrades . ' von ' . $countStudent);

            });
        } else {
            $tblTestList = array();
        }

        $Form = $this->formTest($tblDivision->getServiceTblYear())
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Fach-Klasse',
                                'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                $tblDivisionSubject->getServiceTblSubject()->getName() .
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
                            new Well(Evaluation::useService()->createTest($Form, $tblDivisionSubject->getId(),
                                $Test, $BasicRoute))
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

        $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllWhereTest();
        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);

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
                                    $tblTest->getServiceTblSubject()->getName() .
                                    ($tblTest->getServiceTblSubjectGroup() ? new Small(
                                        ' (Gruppe: ' . $tblTest->getServiceTblSubjectGroup()->getName() . ')') : ''),
                                    Panel::PANEL_TYPE_INFO
                                ), 6
                            ),
                            new LayoutColumn(
                                new Panel('Zeitraum:', $tblTest->getServiceTblPeriod()->getName(),
                                    Panel::PANEL_TYPE_INFO), 3
                            ),
                            new LayoutColumn(
                                new Panel('Zensuren-Typ:', $tblTest->getServiceTblGradeType()->getName(),
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

            return new Warning('Test nicht gefunden')
            . new Redirect($BasicRoute, 2);
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

            return new Warning('Test nicht gefunden')
            . new Redirect('/Education/Graduation/Evaluation/Test', 2);
        }
    }

    /**
     * @param Stage $Stage
     * @param TblTest $tblTest
     * @param         $Grade
     * @param string $BasicRoute
     * @param bool $IsEdit
     */
    private function contentEditTestGrade(Stage $Stage, TblTest $tblTest, $Grade, $BasicRoute, $IsEdit = false)
    {

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );

        $Stage->addButton(
            new Standard('Zur&uuml;ck', $BasicRoute . '/Selected', new ChevronLeft()
                , array('DivisionSubjectId' => $tblDivisionSubject->getId()))
        );

        // ToDo JohK Notenbereich festlegen
        $gradeMirror = array();
        $mirror = array();
        $count = 0;
        for ($i = 1; $i <= 6; $i++) {
            $mirror[$i] = 0;
        }

        $gradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
        if ($gradeList) {
            $Global = $this->getGlobal();
            /** @var TblGrade $grade */
            foreach ($gradeList as $grade) {
                if (empty($Grade)) {

                    if ($grade->getGrade() === null) {
                        $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Attendance'] = 1;
                    } else {
                        $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Grade'] = $grade->getGrade();
                    }
                    $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Comment'] = $grade->getComment();

                    switch ($grade->getGrade() && (
                            $grade->getGrade() === '1' ||
                            $grade->getGrade() === '2' ||
                            $grade->getGrade() === '3' ||
                            $grade->getGrade() === '4' ||
                            $grade->getGrade() === '5' ||
                            $grade->getGrade() === '6'
                        )) {
                        case 1:
                            $mirror[$grade->getGrade()]++;
                            $count++;
                            break;
                        case 2:
                            $mirror[$grade->getGrade()]++;
                            $count++;
                            break;
                        case 3:
                            $mirror[$grade->getGrade()]++;
                            $count++;
                            break;
                        case 4:
                            $mirror[$grade->getGrade()]++;
                            $count++;
                            break;
                        case 5:
                            $mirror[$grade->getGrade()]++;
                            $count++;
                            break;
                        case 6:
                            $mirror[$grade->getGrade()]++;
                            $count++;
                            break;
                    }
                }
            }
            $Global->savePost();
        }

        for ($i = 1; $i <= 6; $i++) {
            if (isset($mirror[$i])) {
                $gradeMirror[] = 'Note ' . new Bold($i) . ': ' . $mirror[$i] .
                    ($count > 0 ? ' (' . (($mirror[$i] / $count) * 100) . '%)' : '');
            }
        }

        // ToDo JohK setzen der richtigen Berechnungsvorschrift
        $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
        if ($tblScoreConditionAll) {
            $tblScoreCondition = $tblScoreConditionAll[0];
        } else {
            $tblScoreCondition = false;
        }

        $tblDivision = $tblTest->getServiceTblDivision();
        $studentList = array();

        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblSubjectStudentAllByDivisionSubject = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
            if ($tblSubjectStudentAllByDivisionSubject) {
                foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                    $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                    $average = Gradebook::useService()->calcStudentGrade(
                        $tblPerson,
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                        $tblScoreCondition
                    );
                    $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName()
                        . ($average && $tblTest->getTblTask() ? new Bold('&nbsp;&nbsp;&#216; ' . round($average,
                                2)) : '');
                    $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                        $tblPerson);

                    $studentList = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $studentList);
                }
            }
        } else {
            $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentAll) {
                foreach ($tblDivisionStudentAll as $tblPerson) {

                    $average = Gradebook::useService()->calcStudentGrade(
                        $tblPerson,
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                        $tblScoreCondition
                    );
                    $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName()
                        . ($average && $tblTest->getTblTask() ? new Bold('&nbsp;&nbsp;&#216; ' . round($average,
                                2)) : '');
                    $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                        $tblPerson);

                    $studentList = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $studentList);
                }
            }
        }

        $tblTask = $tblTest->getTblTask();
        if ($tblTask) {
            $period = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
            $gradeType = 'Stichtagsnote';

            $tableColumns = array(
                'Name' => 'Schüler',
            );

            foreach ($studentList as $personId => $student) {
                $tblPerson = Person::useService()->getPersonById($personId);
                $tblYearAll = Term::useService()->getYearAllByDate(DateTime::createFromFormat('d.m.Y',
                    $tblTask->getDate()));
                if ($tblYearAll) {
                    foreach ($tblYearAll as $tblYear) {
                        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
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
                                        $grade = $tblGrade->getGrade()
                                            ? ($tblGradeType->isHighlighted()
                                                ? new Bold($tblGrade->getGrade() . ' (' . $tblGradeType->getCode() . ')')
                                                : $tblGrade->getGrade() . ' (' . $tblGradeType->getCode() . ')')
                                            : '';

                                        if (isset($studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()])) {
                                            $studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()]
                                                .= '&nbsp;&nbsp;&nbsp;' . $grade;
                                        } else {
                                            $studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()] = $grade;
                                        }
                                    }

                                    if ($tblScoreCondition) {
                                        $average = Gradebook::useService()->calcStudentGrade(
                                            $tblPerson,
                                            $tblDivision,
                                            $tblDivisionSubject->getServiceTblSubject(),
                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                            $tblScoreCondition,
                                            $tblPeriod
                                        );
                                        if ($average) {
                                            $studentList[$tblPerson->getId()]['Period' . $tblPeriod->getId()]
                                                .= '&nbsp;&nbsp;&nbsp;' . new Bold('&#216; ' . round($average, 2));
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            }

            $tableColumns['Grade'] = 'Zensur';
            $tableColumns['Comment'] = 'Kommentar';
        } else {
            $period = $tblTest->getServiceTblPeriod()->getName();
            $gradeType = $tblTest->getServiceTblGradeType()->getName();

            $tableColumns = array(
                'Name' => 'Schüler',
                'Grade' => 'Zensur',
                'Comment' => 'Kommentar',
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
                                $tblTest->getServiceTblSubject()->getName() .
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
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Notenspiegel',
                                $gradeMirror,
                                Panel::PANEL_TYPE_PRIMARY
                            )
                        )
                    ))
                )),
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
                                , $tblTest->getId(), $Grade, $BasicRoute, $IsEdit
                            )
                        )
                    ))
                )),
            ))
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $tblGrade
     * @param           $IsEdit
     * @param           $student
     *
     * @return mixed
     */
    private function contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $student)
    {

        if (!$IsEdit && $tblGrade) {
            $student[$tblPerson->getId()]['Grade']
                = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]',
                '', ''))->setDisabled();
            $student[$tblPerson->getId()]['Comment']
                = (new TextField('Grade[' . $tblPerson->getId() . '][Comment]',
                '', '', new Comment()))->setDisabled();
            $student[$tblPerson->getId()]['Attendance'] =
                (new CheckBox('Grade[' . $tblPerson->getId() . '][Attendance]',
                    ' ', 1))->setDisabled();;
            return $student;
        } else {
            $student[$tblPerson->getId()]['Grade']
                = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]',
                '', ''));
            $student[$tblPerson->getId()]['Comment']
                = (new TextField('Grade[' . $tblPerson->getId() . '][Comment]',
                '', '', new Comment()));
            $student[$tblPerson->getId()]['Attendance']
                = new CheckBox('Grade[' . $tblPerson->getId() . '][Attendance]',
                ' ', 1);
            return $student;
        }
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

            return new Warning('Test nicht gefunden')
            . new Redirect('/Education/Graduation/Evaluation/Headmaster/Test', 2);
        }
    }

    /**
     * @param null $Task
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskAppointedDate($Task = null)
    {

        $Stage = new Stage('Stichtagsnotenaufträge', 'Übersicht');

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
        $tblTaskAll = Evaluation::useService()->getTaskAllByTestType($tblTestType);

        if ($tblTaskAll) {
            foreach ($tblTaskAll as $tblTask) {
                $tblTask->Period = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                $tblTask->Option =
                    (new Standard('',
                        '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Edit',
                        new Edit(),
                        array('Id' => $tblTask->getId()),
                        'Bearbeiten'))
                    . (new Standard('',
                        '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Division',
                        new Listing(),
                        array('Id' => $tblTask->getId()),
                        'Klassen auswählen')
                    )
                    . (new Standard('',
                        '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Grades',
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
                                        'Name' => 'Name',
                                        'Date' => 'Stichtag',
                                        'Period' => 'Zeitraum',
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

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('Task[Name]', '', 'Name'), 12
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Task[Date]', '', 'Stichtag', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Task[FromDate]', '', 'Zeitraum von', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Task[ToDate]', '', 'Zeitraum bis', new Calendar()), 4
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
    public function frontendHeadmasterTaskAppointedDateEdit($Id = null, $Task = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate',
                new ChevronLeft())
        );

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Task']['Name'] = $tblTask->getName();
                $Global->POST['Task']['Date'] = $tblTask->getDate();
                $Global->POST['Task']['FromDate'] = $tblTask->getFromDate();
                $Global->POST['Task']['ToDate'] = $tblTask->getToDate();
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
                                    'Stichtagsauftrag',
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
            $Stage .= new Warning('Stichtagsauftrag nicht gefunden.')
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate', 3);
        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskAppointedDateDivision($Id = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Klassen zuordnen');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate',
                new ChevronLeft())
        );

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {

            $tblDivisionList = array();
            $tblDivisionAvailableList = array();
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
            $tblTestAllByTestAndAppointedDateTask = Evaluation::useService()->getTestAllByTaskAndTestType($tblTask,
                $tblTestType);
            if ($tblTestAllByTestAndAppointedDateTask) {
                foreach ($tblTestAllByTestAndAppointedDateTask as $tblTest) {
                    $tblDivision = $tblTest->getServiceTblDivision();
                    if ($tblDivision) {
                        $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }

            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblYear);
                    if ($tblDivisionAllByYear) {
                        foreach ($tblDivisionAllByYear as $tblDivision) {
                            $tblDivisionAvailableList[$tblDivision->getId()] = $tblDivision;
                        }
                    }
                }
            } else {
                $tblDivisionAvailableList = false;
            }

            if ($tblDivisionAvailableList) {
                /** @var TblDivision $tblDivision */
                foreach ($tblDivisionAvailableList as $tblDivision) {
                    $tblDivision->DisplayName = $tblDivision->getDisplayName();
                    $tblDivision->Option2 = new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                        '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Division/Add', new Plus(),
                        array('TaskId' => $tblTask->getId(), 'DivisionId' => $tblDivision->getId()));
                }
            }

            if (!empty($tblDivisionList)) {

                if ($tblDivisionAvailableList) {
                    $tblDivisionAvailableList = array_udiff($tblDivisionAvailableList, $tblDivisionList,
                        function (TblDivision $ObjectA, TblDivision $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                /** @var TblDivision $tblDivision */
                foreach ($tblDivisionList as $tblDivision) {
                    $tblDivision->DisplayName = $tblDivision->getDisplayName();
                    $tblDivision->Option1 = new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                        '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Division/Remove', new Minus(),
                        array('TaskId' => $tblTask->getId(), 'DivisionId' => $tblDivision->getId()));
                }
            } else {
                $tblDivisionList = false;
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Stichtagsauftrag',
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
                            new LayoutColumn(array(
                                new Title('Ausgewählte', 'Klassen'),
                                new TableData($tblDivisionList, null,
                                    array(
                                        'DisplayName' => 'Klasse',
                                        'Option1' => ''
                                    ))
                            ), 6),
                            new LayoutColumn(array(
                                new Title('Verfügbare', 'Klassen'),
                                new TableData($tblDivisionAvailableList, null,
                                    array(
                                        'DisplayName' => 'Klasse',
                                        'Option2' => ''
                                    ))
                            ), 6),
                        ))
                    )),
                ))
            );
        } else {
            $Stage .= new Warning('Stichtagsauftrag nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate', 3);
        }

        return $Stage;
    }

    /**
     * @param null $TaskId
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskAppointedDateAddDivision($TaskId = null, $DivisionId = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Klassen zuordnen');

        $tblTask = Evaluation::useService()->getTaskById($TaskId);
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');

        if ($tblTask && $tblDivision) {

            Evaluation::useService()->addDivisionToTask($tblTask, $tblDivision, $tblTestType);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Stichtagsauftrag',
                                    $tblTask->getName() . ' ' . $tblTask->getDate()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )
                        ))
                    ))
                ))
                . new \SPHERE\Common\Frontend\Message\Repository\Success('Klasse erfolgreich hinzugefügt.',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Division', 1, array(
                    'Id' => $TaskId
                ))
            );
        } else {
            $Stage->setContent(
                (!$tblTask ? new Warning('Stichtagsauftrag nicht gefunden.', new Ban()) : '')
                . (!$tblDivision ? new Warning('Klasse nicht gefunden.', new Ban()) : '')
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Division', 3, array(
                    'Id' => $TaskId
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param null $TaskId
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskAppointedDateRemoveDivision($TaskId = null, $DivisionId = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Klassen zuordnen');

        $tblTask = Evaluation::useService()->getTaskById($TaskId);
        $tblDivision = Division::useService()->getDivisionById($DivisionId);

        if ($tblTask && $tblDivision) {

            Evaluation::useService()->removeDivisionFromTask($tblTask, $tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Stichtagsauftrag',
                                    $tblTask->getName() . ' ' . $tblTask->getDate()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )
                        ))
                    ))
                ))
                . new \SPHERE\Common\Frontend\Message\Repository\Success('Klasse erfolgreich entfernt.',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Division', 1, array(
                    'Id' => $TaskId
                ))
            );
        } else {
            $Stage->setContent(
                (!$tblTask ? new Warning('Stichtagsauftrag nicht gefunden.', new Ban()) : '')
                . (!$tblDivision ? new Warning('Klasse nicht gefunden.', new Ban()) : '')
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate/Division', 3, array(
                    'Id' => $TaskId
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @return Stage|string
     */
    public function frontendHeadmasterTaskAppointedDateGrades($Id = null)
    {
        $Stage = new Stage('Stichtagsnotenauftrag', 'Zensurenübersicht');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate',
                new ChevronLeft())
        );

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {

            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
            $tblTestAllByTestAndAppointedDateTask = Evaluation::useService()->getTestAllByTaskAndTestType($tblTask,
                $tblTestType);

            $divisionList = array();
            if ($tblTestAllByTestAndAppointedDateTask) {
                foreach ($tblTestAllByTestAndAppointedDateTask as $tblTest) {
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

                foreach ($divisionList as $divisionId => $testList) {
                    $tblDivision = Division::useService()->getDivisionById($divisionId);
                    if (!empty($testList)) {
                        /** @var TblTest $tblTest */
                        foreach ($testList as $tblTest) {
                            $tblSubject = $tblTest->getServiceTblSubject();
                            if ($tblSubject) {
                                $tableHeaderList[$tblDivision->getId()]['Name'] = 'Schüler';
                                $tableHeaderList[$tblDivision->getId()]['Subject' . $tblSubject->getId()] = $tblSubject->getAcronym();

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

                                            $studentList[$tblDivision->getId()][$tblPerson->getId()]['Name'] =
                                                $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName();
                                            $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                                $tblPerson);
                                            if ($tblGrade) {
                                                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                                                ['Subject' . $tblSubject->getId()] = $tblGrade->getGrade() !== null ?
                                                    $tblGrade->getGrade() : '';
                                            } else {
                                                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                                                ['Subject' . $tblSubject->getId()] =
                                                    new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt');
                                            }
                                        }
                                    }
                                } else {
                                    $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
                                    if ($tblDivisionStudentAll) {
                                        foreach ($tblDivisionStudentAll as $tblPerson) {

                                            $studentList[$tblDivision->getId()][$tblPerson->getId()]['Name'] =
                                                $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName();
                                            $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                                $tblPerson);
                                            if ($tblGrade) {
                                                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                                                ['Subject' . $tblSubject->getId()] = $tblGrade->getGrade() !== null ?
                                                    $tblGrade->getGrade() : '';
                                            } else {
                                                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                                                ['Subject' . $tblSubject->getId()] =
                                                    new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt');
                                            }
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
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Stichtagsauftrag',
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
            $Stage .= new Warning('Stichtagsauftrag nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Headmaster/Task/AppointedDate', 3);
        }

        return $Stage;
    }
}
