<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
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
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $GradeType
     * @return Stage
     */
    public function frontendGradeType($GradeType = null)
    {

        $Stage = new Stage('Zensuren-Typ', 'Übersicht');

        $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAllWhereTest();
        if ($tblGradeTypeAll) {
            foreach ($tblGradeTypeAll as $tblGradeType) {
                $tblGradeType->DisplayName = $tblGradeType->getIsHighlighted()
                    ? new Bold($tblGradeType->getName()) : $tblGradeType->getName();
                $tblGradeType->DisplayCode = $tblGradeType->getIsHighlighted()
                    ? new Bold($tblGradeType->getCode()) : $tblGradeType->getCode();
                $tblGradeType->Option = new Standard('', '/Education/Graduation/Gradebook/GradeType/Edit',
                    new Edit(),
                    array(
                        'Id' => $tblGradeType->getId()
                    ),
                    'Zensuren-Typ bearbeiten'
                );
            }
        }

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblGradeTypeAll, null, array(
                                'DisplayName' => 'Name',
                                'DisplayCode' => 'Abk&uuml;rzung',
                                'Description' => 'Beschreibung',
                                'Option' => ''
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Gradebook::useService()->createGradeTypeWhereTest($Form, $GradeType))
                        )
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param $GradeType
     * @return Stage
     */
    public function frontendEditGradeType($Id = null, $GradeType = null)
    {
        $Stage = new Stage('Zensuren-Typ', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/GradeType', new ChevronLeft())
        );

        $tblGradeType = Gradebook::useService()->getGradeTypeById($Id);
        if ($tblGradeType) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['GradeType']['Name'] = $tblGradeType->getName();
                $Global->POST['GradeType']['Code'] = $tblGradeType->getCode();
                $Global->POST['GradeType']['IsHighlighted'] = $tblGradeType->getIsHighlighted();
                $Global->POST['GradeType']['Description'] = $tblGradeType->getDescription();
                $Global->savePost();
            }

            $Form = $this->formGradeType()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Zensuren-Typ',
                                    $tblGradeType->getName() . ' (' . $tblGradeType->getCode() . ')' .
                                    ($tblGradeType->getDescription() !== '' ? '&nbsp;&nbsp;'
                                        . new Muted(new Small(new Small($tblGradeType->getDescription()))) : ''),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateGradeType($Form, $Id, $GradeType))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return new Stage('Zensuren-Typ nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', 2);
        }
    }

    private function formGradeType()
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Name]', 'Leistungskontrolle', 'Name'), 9
                ),
                new FormColumn(
                    new TextField('GradeType[Code]', 'LK', 'Abk&uuml;rzung'), 3
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Description]', '', 'Beschreibung'), 12
                ),
                new FormColumn(
                    new CheckBox('GradeType[IsHighlighted]', 'Fett markiert', 1), 2
                )
            )),
        )));
    }

    /**
     * @return Stage
     */
    public function frontendGradeBook()
    {
        $Stage = new Stage('Notenbuch', 'Auswahl');

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
                                    '', '/Education/Graduation/Gradebook/Gradebook/Selected', new Select(), array(
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
                                '', '/Education/Graduation/Gradebook/Gradebook/Selected', new Select(), array(
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
    public function frontendHeadmasterGradeBook()
    {
        $Stage = new Stage('Notenbuch (Leitung)', 'Auswahl');

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
                                    '', '/Education/Graduation/Gradebook/Headmaster/Gradebook/Selected', new Select(),
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
                                '', '/Education/Graduation/Gradebook/Headmaster/Gradebook/Selected', new Select(),
                                array(
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
     * @param null $ScoreConditionId
     * @param null $Select
     * @return Stage|string
     */
    public function frontendSelectedGradeBook($DivisionSubjectId = null, $ScoreConditionId = null, $Select = null)
    {
        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Warning('Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook',
                2);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject, $ScoreConditionId, $Select,
            '/Education/Graduation/Gradebook/Gradebook');

        return $Stage;
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $ScoreConditionId
     * @param null $Select
     * @return Stage|string
     */
    public function frontendHeadmasterSelectedGradeBook(
        $DivisionSubjectId = null,
        $ScoreConditionId = null,
        $Select = null
    ) {
        $Stage = new Stage('Notenbuch (Leitung)', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Warning('Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Headmaster/Gradebook',
                2);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject, $ScoreConditionId, $Select,
            '/Education/Graduation/Gradebook/Headmaster/Gradebook');

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param TblDivisionSubject $tblDivisionSubject
     * @param $ScoreConditionId
     * @param $Select
     * @param string $BasicRoute
     */
    private function contentSelectedGradeBook(
        Stage $Stage,
        TblDivisionSubject $tblDivisionSubject,
        $ScoreConditionId,
        $Select,
        $BasicRoute
    ) {
        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft()));

        $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();

        $tblDivision = $tblDivisionSubject->getTblDivision();
        $tblScoreCondition = new TblScoreCondition();
        $grades = array();
        $rowList = array();
        if ($ScoreConditionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['ScoreCondition'] = $ScoreConditionId;
                $Global->savePost();
            }

            $tblScoreCondition = Gradebook::useService()->getScoreConditionById($ScoreConditionId);
            $tblYear = $tblDivision->getServiceTblYear();
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

            if ($tblDivisionSubject->getTblSubjectGroup()) {
                $tblStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                if ($tblStudentList) {
                    foreach ($tblStudentList as $tblSubjectStudent) {
                        $grades[$tblSubjectStudent->getServiceTblPerson()->getId()] = Gradebook::useService()->getGradesByStudent(
                            $tblSubjectStudent->getServiceTblPerson(),
                            $tblDivision,
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblTestType
                        );
                    }
                }
            } else {
                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    foreach ($tblStudentList as $tblPerson) {
                        $grades[$tblPerson->getId()] = Gradebook::useService()->getGradesByStudent(
                            $tblPerson,
                            $tblDivision,
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblTestType
                        );
                    }
                }
            }

            $gradePositions = array();
            $columnList[] = new LayoutColumn(new Title(new Bold('Schüler')), 2);
            if ($tblPeriodList) {
                $width = floor(10 / count($tblPeriodList));
                foreach ($tblPeriodList as $tblPeriod) {
                    $columnList[] = new LayoutColumn(
                        new Title(new Bold($tblPeriod->getName()))
                        , $width
                    );
                }
                $rowList[] = new LayoutRow($columnList);
                $columnList = array();
                $columnList[] = new LayoutColumn(new Header(' '), 2);
                $columnSecondList[] = new LayoutColumn(new Header(' '), 2);
                foreach ($tblPeriodList as $tblPeriod) {
                    $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                        $tblTestType,
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        $tblPeriod,
                        $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                    );
                    if ($tblTestList) {
                        $columnSubList = array();
                        $columnSecondSubList = array();
                        $pos = 0;
                        foreach ($tblTestList as $tblTest) {
                            $gradePositions[$tblPeriod->getId()][$pos++] = $tblTest->getId();
                            $columnSubList[] = new LayoutColumn(
                                new Header(
                                    $tblTest->getServiceTblGradeType()->getIsHighlighted()
                                        ? new Bold($tblTest->getServiceTblGradeType()->getCode()) : $tblTest->getServiceTblGradeType()->getCode())
                                , 1);
                            $date = $tblTest->getDate();
                            if (strlen($date) > 6) {
                                $date = substr($date, 0, 6);
                            }
                            $columnSecondSubList[] = new LayoutColumn(
                                new Header(
                                    $tblTest->getServiceTblGradeType()->getIsHighlighted()
                                        ? new Bold($date) : $date)
                                , 1);
                        }
                        $columnSubList[] = new LayoutColumn(new Header(new Bold('&#216;')), 1);
                        $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),
                            $width);
                        $columnSecondList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSecondSubList))),
                            $width);
                    } else {
                        $columnList[] = new LayoutColumn(new Header(' '), $width);
                        $columnSecondList[] = new LayoutColumn(new Header(' '), $width);
                    }
                }
                $rowList[] = new LayoutRow($columnSecondList);
                $rowList[] = new LayoutRow($columnList);

                if (!empty($grades)) {
                    foreach ($grades as $personId => $gradeList) {
                        $tblPerson = Person::useService()->getPersonById($personId);
                        $columnList = array();
                        $totalAverage = Gradebook::useService()->calcStudentGrade(
                            $tblPerson,
                            $tblDivision,
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblTestType,
                            $tblScoreCondition,
                            null,
                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                        );
                        $columnList[] = new LayoutColumn(
                            new Container($tblPerson->getFirstName() . ' ' . $tblPerson->getLastName()
                                . ' ' . new Bold('&#216; ' . $totalAverage))
                            , 2);
                        foreach ($tblPeriodList as $tblPeriod) {
                            $columnSubList = array();
                            if (isset($gradePositions[$tblPeriod->getId()])) {
                                foreach ($gradePositions[$tblPeriod->getId()] as $pos => $testId) {
                                    $hasFound = false;
                                    /** @var TblGrade $grade */
                                    if ($gradeList) {
                                        foreach ($gradeList as $grade) {
                                            if ($testId === $grade->getServiceTblTest()->getId()) {
                                                $columnSubList[] = new LayoutColumn(
                                                    new Container($grade->getTblGradeType()->getIsHighlighted()
                                                        ? new Bold($grade->getGrade()) : $grade->getGrade())
                                                    , 1);
                                                $hasFound = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!$hasFound) {
                                        $columnSubList[] = new LayoutColumn(
                                            new Container(' '), 1
                                        );
                                    }
                                }
                            } else {
                                $columnSubList[] = new LayoutColumn(
                                    new Container(' '), 12
                                );
                            }

                            /*
                             * Calc Average
                             */
                            $average = Gradebook::useService()->calcStudentGrade(
                                $tblPerson,
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType,
                                $tblScoreCondition,
                                $tblPeriod,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            $columnSubList[] = new LayoutColumn(new Container(new Bold($average)), 1);

                            $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),
                                $width);
                        }
                        $rowList[] = new LayoutRow($columnList);
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
                                'Fach-Klasse',
                                'Klasse ' . $tblDivision->getTblLevel()->getName() . $tblDivision->getName() . ' - ' .
                                $tblDivisionSubject->getServiceTblSubject()->getName() .
                                ($tblDivisionSubject->getTblSubjectGroup() ? new Small(
                                    ' (Gruppe: ' . $tblDivisionSubject->getTblSubjectGroup()->getName() . ')') : ''),
                                Panel::PANEL_TYPE_INFO
                            )
                        ), $ScoreConditionId !== null ? 6 : 12),
                        ($ScoreConditionId !== null ? new LayoutColumn(new Panel(
                            'Berechnungsvorschrift',
                            $tblScoreCondition->getName(),
                            Panel::PANEL_TYPE_INFO
                        ), 6) : null),
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->getGradeBook(
                                new Form(new FormGroup(array(
                                    new FormRow(array(
                                        new FormColumn(
                                            new SelectBox('Select[ScoreCondition]', 'Berechnungsvorschrift',
                                                array(
                                                    '{{ Name }}' => $tblScoreConditionAll
                                                )),
                                            12
                                        ),
                                    )),
                                )), new Primary('Auswählen', new Select()))
                                , $tblDivisionSubject->getId(), $Select, $BasicRoute))
                        )),
                    )),
                ))
            ))
            . ($ScoreConditionId !== null ? new Layout(new LayoutGroup($rowList)) : '')
        );
    }

    /**
     * @param null $ScoreCondition
     * @return Stage
     */
    public function frontendScore(
        $ScoreCondition = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Übersicht');
        $Stage->addButton(
            new Standard('Zensuren-Gruppe', '/Education/Graduation/Gradebook/Score/Group', new ListingTable(), null,
                'Erstellen/Berarbeiten')
        );

        $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
        if ($tblScoreConditionAll) {
            foreach ($tblScoreConditionAll as &$tblScoreCondition) {
                $scoreGroups = '';
                $tblScoreGroups = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                if ($tblScoreGroups) {
                    foreach ($tblScoreGroups as $tblScoreGroup) {
                        $scoreGroups .= $tblScoreGroup->getTblScoreGroup()->getName() . ', ';
                    }
                }
                if (($length = strlen($scoreGroups)) > 2) {
                    $scoreGroups = substr($scoreGroups, 0, $length - 2);
                }
                $tblScoreCondition->ScoreGroups = $scoreGroups;
                $tblScoreCondition->Option =
//                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Edit', new Pencil(),
//                        array('Id' => $tblScoreCondition->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Group/Select', new Listing(),
                        array('Id' => $tblScoreCondition->getId()), 'Zensuren-Gruppen auswählen'));
            }
        }


        $Form = $this->formScoreCondition()
            ->appendFormButton(new Primary('Hinzufügen', new Plus()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblScoreConditionAll, null, array(
                                'Name' => 'Name',
                                'ScoreGroups' => 'Zensuren-Gruppen',
                                'Priority' => 'Priorität',
                                'Round' => 'Runden',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreCondition($Form, $ScoreCondition))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    private function formScoreCondition()
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreCondition[Name]', 'Klassenarbeit 60% : Rest 40%', 'Name'), 8
                ),
                new FormColumn(
                    new TextField('ScoreCondition[Round]', '', 'Rundung'), 2
                ),
                new FormColumn(
                    new NumberField('ScoreCondition[Priority]', '1', 'Priorität'), 2
                )
            ))
        )));
    }

    /**
     * @param null $ScoreGroup
     * @return Stage
     */
    public function frontendScoreGroup(
        $ScoreGroup = null
    ) {

        $Stage = new Stage('Zensuren-Gruppe', 'Übersicht');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/Score', new ChevronLeft())
        );

        $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
        if ($tblScoreGroupAll) {
            foreach ($tblScoreGroupAll as &$tblScoreGroup) {
                $gradeTypes = '';
                $tblScoreGroupGradeTypes = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                if ($tblScoreGroupGradeTypes) {
                    foreach ($tblScoreGroupGradeTypes as $tblScoreGroupGradeType) {
                        $gradeTypes .= $tblScoreGroupGradeType->getTblGradeType()->getName() . ', ';
                    }
                }
                if (($length = strlen($gradeTypes)) > 2) {
                    $gradeTypes = substr($gradeTypes, 0, $length - 2);
                }
                $tblScoreGroup->GradeTypes = $gradeTypes;
                $tblScoreGroup->Option =
//                    (new Standard('', '/Education/Graduation/Gradebook/Score/Group/Edit', new Pencil(),
//                        array('Id' => $tblScoreGroup->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Group/GradeType/Select', new Listing(),
                        array('Id' => $tblScoreGroup->getId()), 'Zensuren-Typen auswählen'));
            }
        }


        $Form = $this->formScoreGroup()
            ->appendFormButton(new Primary('Hinzufügen', new Plus()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblScoreGroupAll, null, array(
                                'Name' => 'Name',
                                'GradeTypes' => 'Zensuren-Typen',
                                'Multiplier' => 'Faktor',
                                'Round' => 'Runden',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreGroup($Form, $ScoreGroup))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    private function formScoreGroup()
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreGroup[Name]', 'Rest', 'Name'), 8
                ),
                new FormColumn(
                    new TextField('ScoreGroup[Round]', '', 'Rundung'), 2
                ),
                new FormColumn(
                    new TextField('ScoreGroup[Multiplier]', 'z.B. 40 für 40%', 'Faktor'), 2
                )
            ))
        )));
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeSelect(
        $Id = null
    ) {

        $Stage = new Stage('Zensuren-Gruppe', 'Zensuren-Typen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Group', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreGroup = Gradebook::useService()->getScoreGroupById($Id);
            if (empty($tblScoreGroup)) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreGroupGradeTypeListByGroup = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAllWhereTest();
                $tblGradeTypeAllByGroup = array();
                if ($tblScoreGroupGradeTypeListByGroup) {
                    /** @var TblScoreGroupGradeTypeList $tblScoreGroupGradeType */
                    foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeType) {
                        $tblGradeTypeAllByGroup[] = $tblScoreGroupGradeType->getTblGradeType();
                    }
                }

                if (!empty($tblGradeTypeAllByGroup) && $tblGradeTypeAll) {
                    $tblGradeTypeAll = array_udiff($tblGradeTypeAll, $tblGradeTypeAllByGroup,
                        function (TblGradeType $ObjectA, TblGradeType $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreGroupGradeTypeListByGroup) {
                    foreach ($tblScoreGroupGradeTypeListByGroup as &$tblScoreGroupGradeTypeList) {
                        $tblScoreGroupGradeTypeList->Name = $tblScoreGroupGradeTypeList->getTblGradeType()->getName();
                        $tblScoreGroupGradeTypeList->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Gradebook/Score/Group/GradeType/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreGroupGradeTypeList->getId()
                            )))->__toString();
                    }
                }

                if ($tblGradeTypeAll) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        $tblGradeType->Option =
                            (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new TextField('GradeType[Multiplier]', 'Faktor', '', new Quantity()
                                            )
                                            , 7),
                                        new FormColumn(
                                            new Primary('Hinzufügen',
                                                new Plus())
                                            , 5)
                                    ))
                                ), null,
                                '/Education/Graduation/Gradebook/Score/Group/GradeType/Add', array(
                                    'tblScoreGroupId' => $tblScoreGroup->getId(),
                                    'tblGradeTypeId' => $tblGradeType->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Zensuren-Gruppe', $tblScoreGroup->getName(), Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Typen'),
                                    new TableData($tblScoreGroupGradeTypeListByGroup, null,
                                        array(
                                            'Name' => 'Name',
                                            'Multiplier' => 'Faktor',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Typen'),
                                    new TableData($tblGradeTypeAll, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Faktor'
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreGroupId
     * @param null $tblGradeTypeId
     * @param null $GradeType
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeAdd(
        $tblScoreGroupId = null,
        $tblGradeTypeId = null,
        $GradeType = null
    ) {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ einer Zenuseren-Gruppe hinzufügen');

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblGradeType = Gradebook::useService()->getGradeTypeById($tblGradeTypeId);

        if ($GradeType['Multiplier'] == '') {
            $multiplier = 1;
        } else {
            $multiplier = $GradeType['Multiplier'];
        }

        if ($tblScoreGroup && $tblGradeType) {
            $Stage->setContent(Gradebook::useService()->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup,
                $multiplier));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeRemove(
        $Id
    ) {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ von einer Zenuseren-Gruppe entfernen');

        $tblScoreGroupGradeTypeList = Gradebook::useService()->getScoreGroupGradeTypeListById($Id);
        if ($tblScoreGroupGradeTypeList) {
            $Stage->setContent(Gradebook::useService()->removeScoreGroupGradeTypeList($tblScoreGroupGradeTypeList));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupSelect(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Zensuren-Gruppen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {

            $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Id);
            if (!$tblScoreCondition) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreConditionGroupListByCondition = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
                $tblScoreGroupAllByCondition = array();
                if ($tblScoreConditionGroupListByCondition) {
                    /** @var TblScoreConditionGroupList $tblScoreConditionGroup */
                    foreach ($tblScoreConditionGroupListByCondition as $tblScoreConditionGroup) {
                        $tblScoreGroupAllByCondition[] = $tblScoreConditionGroup->getTblScoreGroup();
                    }
                }

                if (!empty($tblScoreGroupAllByCondition) && $tblScoreGroupAll) {
                    $tblScoreGroupAll = array_udiff($tblScoreGroupAll, $tblScoreGroupAllByCondition,
                        function (TblScoreGroup $ObjectA, TblScoreGroup $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreConditionGroupListByCondition) {
                    foreach ($tblScoreConditionGroupListByCondition as &$tblScoreConditionGroupList) {
                        $tblScoreConditionGroupList->Name = $tblScoreConditionGroupList->getTblScoreGroup()->getName();
                        $tblScoreConditionGroupList->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Gradebook/Score/Group/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreConditionGroupList->getId()
                            )))->__toString();
                    }
                }

                if ($tblScoreGroupAll) {
                    foreach ($tblScoreGroupAll as $tblScoreGroup) {
                        $tblScoreGroup->Option =
                            (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new Primary('Hinzufügen',
                                                new Plus())
                                            , 5)
                                    ))
                                ), null,
                                '/Education/Graduation/Gradebook/Score/Group/Add', array(
                                    'tblScoreGroupId' => $tblScoreGroup->getId(),
                                    'tblScoreConditionId' => $tblScoreCondition->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvorschrift', $tblScoreCondition->getName(),
                                        Panel::PANEL_TYPE_INFO), 12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreConditionGroupListByCondition, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreGroupAll, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreGroupId
     * @param null $tblScoreConditionId
     *
     * @return Stage
     */
    public function frontendScoreGroupAdd(
        $tblScoreGroupId = null,
        $tblScoreConditionId = null
    ) {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Gruppe einer Berechnungsvorschrift hinzufügen');

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreGroup && $tblScoreCondition) {
            $Stage->setContent(Gradebook::useService()->addScoreConditionGroupList($tblScoreCondition,
                $tblScoreGroup));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupRemove(
        $Id
    ) {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Gruppe von einer Berechnungsvorschrift entfernen');

        $tblScoreConditionGroupList = Gradebook::useService()->getScoreConditionGroupListById($Id);
        if ($tblScoreConditionGroupList) {
            $Stage->setContent(Gradebook::useService()->removeScoreConditionGroupList($tblScoreConditionGroupList));
        }

        return $Stage;
    }
}