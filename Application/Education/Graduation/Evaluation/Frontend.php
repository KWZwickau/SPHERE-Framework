<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:39
 */

namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
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
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
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
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
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
                                'Type' => $tblDivision->getTblLevel()->getServiceTblType()->getName(),
                                'Division' => $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
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
                            'Type' => $tblDivision->getTblLevel()->getServiceTblType()->getName(),
                            'Division' => $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
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
                                'Type' => $tblDivision->getTblLevel()->getServiceTblType()->getName(),
                                'Division' => $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
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
                            'Type' => $tblDivision->getTblLevel()->getServiceTblType()->getName(),
                            'Division' => $tblDivision->getTblLevel()->getName() . $tblDivision->getName(),
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
     * @param null $DivisionSubjectId
     * @param null $Test
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
     * @param $DivisionSubjectId
     * @param $Test
     * @param Stage $Stage
     * @param string $BasicRoute
     */
    private function contentTestSelected($DivisionSubjectId, $Test, Stage $Stage, $BasicRoute)
    {
        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft()));

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
        $tblDivision = $tblDivisionSubject->getTblDivision();
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblTestType,
            $tblDivision,
            $tblDivisionSubject->getServiceTblSubject(),
            null,
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
        );
        if ($tblTestList) {
            array_walk($tblTestList, function (TblTest &$tblTest) use (&$BasicRoute) {
                $tblDivision = $tblTest->getServiceTblDivision();
                if ($tblDivision) {
                    $tblTest->Division = $tblDivision->getServiceTblYear()->getName() . ' - ' .
                        $tblDivision->getTblLevel()->getServiceTblType()->getName() . ' - ' .
                        $tblDivision->getTblLevel()->getName() . $tblDivision->getName();
                } else {
                    $tblTest->Division = '';
                }
                $tblTest->Subject = $tblTest->getServiceTblSubject()->getName();
                $tblTest->Period = $tblTest->getServiceTblPeriod()->getName();
                $tblTest->GradeType = $tblTest->getServiceTblGradeType()->getName();
                $tblTest->Option = (new Standard('', $BasicRoute . '/Edit', new Edit(),
                        array('Id' => $tblTest->getId()), 'Bearbeiten'))
                    . (new Standard('', $BasicRoute . '/Grade/Edit', new Listing(),
                        array('Id' => $tblTest->getId()), 'Zensuren bearbeiten'));
            });
        } else {
            $tblTestList = array();
        }

        $Form = $this->formTest()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Fach-Klasse',
                                'Klasse ' . $tblDivision->getTblLevel()->getName() . $tblDivision->getName() . ' - ' .
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
                                'Period' => 'Zeitraum',
                                'GradeType' => 'Zensuren-Typ',
                                'Description' => 'Beschreibung',
                                'Date' => 'Datum',
                                'CorrectionDate' => 'Korrekturdatum',
                                'ReturnDate' => 'R&uuml;ckgabedatum',
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
     * @return Form
     */
    private function formTest()
    {
        $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllWhereTest();
        $tblPeriodList = Term::useService()->getPeriodAll();

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
     * @param Stage $Stage
     * @param $Id
     * @param $Test
     * @param string $BasicRoute
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
                                    'Klasse ' . $tblDivision->getTblLevel()->getName() . $tblDivision->getName() . ' - ' .
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
     * @param Stage $Stage
     * @param TblTest $tblTest
     * @param $Grade
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

                    if ($grade->getGrade() === null){
                        $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Attendance'] = 1;
                    } else {
                        $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Grade'] = $grade->getGrade();
                    }
                    $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Comment'] = $grade->getComment();

                    switch ($grade->getGrade()) {
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

        $tblDivision = $tblTest->getServiceTblDivision();
        $student = array();

        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblSubjectStudentAllByDivisionSubject = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
            if ($tblSubjectStudentAllByDivisionSubject) {
                foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                    $tblPerson = $tblSubjectStudent->getServiceTblPerson();

                    $student[$tblPerson->getId()]['Name']
                        = $tblPerson->getFirstName() . ' '
                        . $tblPerson->getLastName();
                    $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                        $tblPerson);

                    $student = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $student);
                }
            }
        } else {
            $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentAll) {
                foreach ($tblDivisionStudentAll as $tblPerson) {

                    $student[$tblPerson->getId()]['Name']
                        = $tblPerson->getFirstName() . ' '
                        . $tblPerson->getLastName();
                    $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                        $tblPerson);

                    $student = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $student);
                }
            }
        }

        $Stage->setContent(
            new Layout (array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Fach-Klasse',
                                'Klasse ' . $tblDivision->getTblLevel()->getName() . $tblDivision->getName() . ' - ' .
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
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Notenspiegel',
                                $gradeMirror
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
                                                new TableData(
                                                    $student, null, array(
                                                    'Name' => 'Schüler',
                                                    'Grade' => 'Zensur',
                                                    'Comment' => 'Kommentar',
                                                    'Attendance' => 'Nicht teilgenommen'
                                                ), null)
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
     * @param $tblGrade
     * @param $IsEdit
     * @param $student
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
     * @param null $Task
     * @return Stage
     */
    public function frontendHeadmasterTaskAppointedDate($Task = null)
    {
        $Stage = new Stage('Stichtagsnotenaufträge', 'Übersicht');

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTEDDATETASK');
        $tblTaskAll = Evaluation::useService()->getTestAllByTestType($tblTestType);

        $Form = new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('Test[Description]', '', 'Name'), 12
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

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new TableData(
                                    $tblTaskAll, null, array(
                                        'Description' => 'Name',
                                        'CorrectionDate' => 'von',
                                        'ReturnDate' => 'bis',
                                    )
                                )
                            )
                        )
                    )
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Evaluation::useService()->createAppointedDateTask($Form, $Task))
                        )
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

}