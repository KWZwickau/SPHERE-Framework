<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
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
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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
     * @return Stage
     */
    public function frontendGradeType()
    {

        $Stage = new Stage('Zensuren', 'Zensuren-Typen');
        $Stage->addButton(
            new Standard('Zensuren-Typ anlegen', '/Education/Graduation/Gradebook/GradeType/Create', new Plus())
        );

        $tblGradeType = Gradebook::useService()->getGradeTypeAll();

        $Stage->setContent(
            new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            new TableData($tblGradeType, null, array(
                                'Name' => 'Name',
                                'Code' => 'Abk&uuml;rzung',
                                'Description' => 'Beschreibung',
                            ))
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $GradeType
     * @return Stage
     */
    public function frontendCreateGradeType($GradeType)
    {
        $Stage = new Stage('Zensuren', 'Zensuren-Typ anlegen');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/GradeType', new ChevronLeft())
        );

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        $Stage->setContent(Gradebook::useService()->createGradeType($Form, $GradeType));

        return $Stage;
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
                )
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Description]', '', 'Beschreibung'), 12
                ),
                new FormColumn(
                    new CheckBox('GradeType[IsHighlighted]', 'Fett markiert', 1), 3
                )
            ))
        )));
    }

    /**
     * @param $DivisionId
     * @param $SubjectId
     * @param $Select
     * @return string
     */
    public function frontendSelectedGradeBook($DivisionId, $SubjectId, $Select)
    {
        $Stage = new Stage('Zensuren', 'Notenbuch');

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblSubjectAll = Subject::useService()->getSubjectAll();

        $tblDivision = new TblDivision();
        $tblSubject = new TblSubject();
        $rowList = array();
        if ($DivisionId !== null && $SubjectId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->POST['Select']['Subject'] = $SubjectId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            $tblSubject = Subject::useService()->getSubjectById($SubjectId);
            $tblYear = $tblDivision->getServiceTblYear();
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);

            $columnList[] = new LayoutColumn(new Title(new Bold('Sch&uuml;ler')), 2);
            if ($tblPeriodList) {
                $width = floor(10 / count($tblPeriodList));
                $count = 1;
                foreach ($tblPeriodList as $tblPeriod) {
                    $columnList[] = new LayoutColumn(
                        new Title(new Bold($tblPeriod->getName()))
                        , $width
                    );
                }
                $rowList[] = new LayoutRow($columnList);
                $columnList = array();
                $columnList[] = new LayoutColumn(new Header(' '), 2);
                foreach ($tblPeriodList as $tblPeriod) {
                    if ($tblStudentList) {
                        $gradeList = Gradebook::useService()->getGradesByStudentAndSubjectAndPeriod($tblStudentList[0],
                            $tblSubject, $tblPeriod);
                        if ($gradeList) {
                            $columnSubList = array();
                            foreach ($gradeList as $grade) {
                                $columnSubList[] = new LayoutColumn(
                                    new Header(
                                        $grade->getTblGradeType()->getIsHighlighted()
                                            ? new Bold($grade->getTblGradeType()->getCode()) : $grade->getTblGradeType()->getCode())
                                    , 1);
                                $count++;
                            }
                            $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),
                                $width);
                        } else {
                            $columnList[] = new LayoutColumn(new Header(' '), $width);
                        }
                    }
                }
                $rowList[] = new LayoutRow($columnList);

                if ($tblStudentList) {
                    foreach ($tblStudentList as $tblPerson) {
                        $count = 1;
                        $columnList = array();
                        $columnList[] = new LayoutColumn(new Container($tblPerson->getFullName()), 2);
                        foreach ($tblPeriodList as $tblPeriod) {
                            $gradeList = Gradebook::useService()->getGradesByStudentAndSubjectAndPeriod($tblPerson,
                                $tblSubject, $tblPeriod);
                            if ($gradeList) {
                                $columnSubList = array();
                                foreach ($gradeList as $grade) {
                                    $columnSubList[] = new LayoutColumn(
                                        new Container($grade->getTblGradeType()->getIsHighlighted()
                                            ? new Bold($grade->getGrade()) : $grade->getGrade())
                                        , 1);
                                    $count++;
                                }
                                $columnList[] = new LayoutColumn(new Layout(new LayoutGroup(new LayoutRow($columnSubList))),
                                    $width);
                            } else {
                                $columnList[] = new LayoutColumn(new Header(' '), $width);
                            }
                        }
                        $rowList[] = new LayoutRow($columnList);
                    }
                }
            }
        }

        $division = '';
        if ($DivisionId !== null && $tblDivision) {
            $division = $tblDivision->getServiceTblYear()->getName() . ' - ' .
                $tblDivision->getTblLevel()->getServiceTblType()->getName() . ' - ' .
                $tblDivision->getTblLevel()->getName() . $tblDivision->getName();
        }

        $Stage->setContent(
            Gradebook::useService()->getGradeBook(
                new Form(new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            new SelectBox('Select[Division]', 'Klasse',
                                array(
                                    '{{ serviceTblYear.Name }} - {{ tblLevel.serviceTblType.Name }}
                                                     - {{ tblLevel.Name }}{{ Name }}' => $tblDivisionAll
                                )),
                            6
                        ),
                        new FormColumn(
                            new SelectBox('Select[Subject]', 'Fach', array('Name' => $tblSubjectAll)), 6
                        )
                    )),
                )), new Primary('Auswählen', new Select()))
                , $Select)
            .
            ($DivisionId !== null && $SubjectId !== null ?
                (new Layout (new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Klasse:', $division,
                            Panel::PANEL_TYPE_SUCCESS), 6
                    ),
                    new LayoutColumn(
                        new Panel('Fach:', $tblSubject->getName(),
                            Panel::PANEL_TYPE_SUCCESS), 6
                    )
                )))))
                . new Layout(new LayoutGroup($rowList)) : '')
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendTest()
    {

        $Stage = new Stage('Zensuren', 'Test-Übersicht');
        $Stage->addButton(
            new Standard('Test anlegen', '/Education/Graduation/Gradebook/Test/Create', new Plus())
        );

        $tblTestList = Gradebook::useService()->getTestAll();
        if ($tblTestList) {
            array_walk($tblTestList, function (TblTest &$tblTest) {
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
                $tblTest->GradeType = $tblTest->getTblGradeType()->getName();
                $tblTest->Option = (new Standard('', '/Education/Graduation/Gradebook/Test/Edit', new Pencil(),
                        array('Id' => $tblTest->getId()), 'Test bearbeiten'))
                    . (new Standard('', '/Education/Graduation/Gradebook/Test/Grade/Edit', new Listing(),
                        array('Id' => $tblTest->getId()), 'Noten bearbeiten'));
            });
        }

        $Stage->setContent(
            new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            new TableData($tblTestList, null, array(
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'Period' => 'Zeitraum',
                                'GradeType' => 'Zensuren-Typ',
                                'Description' => 'Beschreibung',
                                'Date' => 'Datum',
                                'CorrectionDate' => 'Korrekturdatum',
                                'ReturnDate' => 'R&uuml;ckgabedatum',
                                'Option' => 'Option'
                            ))
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Test
     * @return Stage
     */
    public function frontendCreateTest($Test)
    {
        $Stage = new Stage('Zensuren', 'Test anlegen');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Test', new ChevronLeft())
        );

        $Form = $this->formTest()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        $Stage->setContent(Gradebook::useService()->createTest($Form, $Test));

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formTest()
    {
        $tblGradeTypeList = Gradebook::useService()->getGradeTypeAll();
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        $tblPeriodList = Term::useService()->getPeriodAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Test[Division]', 'Klasse',
                        array(
                            '{{ serviceTblYear.Name }} - {{ tblLevel.serviceTblType.Name }}
                                                     - {{ tblLevel.Name }}{{ Name }}' => $tblDivisionAll
                        )), 6
                ),
                new FormColumn(
                    new SelectBox('Test[Subject]', 'Fach', array('Name' => $tblSubjectAll)), 6
                )
            )),
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
    public function frontendEditTest($Id, $Test)
    {
        $Stage = new Stage('Zensuren', 'Test bearbeiten');

        $tblTest = Gradebook::useService()->getTestById($Id);
        if ($tblTest) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Test']['Description'] = $tblTest->getDescription();
                $Global->POST['Test']['Date'] = $tblTest->getDate();
                $Global->POST['Test']['CorrectionDate'] = $tblTest->getCorrectionDate();
                $Global->POST['Test']['ReturnDate'] = $tblTest->getReturnDate();
                $Global->savePost();
            }

            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Test', new ChevronLeft())
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
            $division = '';
            if ($tblDivision) {
                $division = $tblDivision->getServiceTblYear()->getName() . ' - ' .
                    $tblDivision->getTblLevel()->getServiceTblType()->getName() . ' - ' .
                    $tblDivision->getTblLevel()->getName() . $tblDivision->getName();
            }

            $Stage->setContent(
                new Layout (new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Klasse:', $division,
                                Panel::PANEL_TYPE_SUCCESS), 6
                        ),
                        new LayoutColumn(
                            new Panel('Fach:', $tblTest->getServiceTblSubject()->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        )
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Zeitraum:', $tblTest->getServiceTblPeriod()->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        ),
                        new LayoutColumn(
                            new Panel('Zensuren-Typ:', $tblTest->getTblGradeType()->getName(),
                                Panel::PANEL_TYPE_SUCCESS), 6
                        )
                    ))
                )))
                . Gradebook::useService()->updateTest($Form, $tblTest->getId(), $Test)
            );

            return $Stage;
        } else {

            return new Warning('Test nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Test', 2);
        }
    }

    /**
     * @param $Id
     * @param $Grade
     *
     * @return Stage|string
     */
    public function frontendEditTestGrade($Id, $Grade)
    {

        $Stage = new Stage('Zensuren', 'Zensuren eines Tests bearbeiten');

        $tblTest = Gradebook::useService()->getTestById($Id);
        if ($tblTest) {

            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Test', new ChevronLeft())
            );

            $gradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
            if ($gradeList) {
                $Global = $this->getGlobal();
                /** @var TblGrade $grade */
                foreach ($gradeList as $grade) {
                    $grade->Student = $grade->getServiceTblPerson()->getFullName();
                    $grade->Grades = new TextField('Grade[' . $grade->getId() . '][Grade]', '', '');
                    $grade->Comments = new TextField('Grade[' . $grade->getId() . '][Comment]', '', '');

                    if (empty($Grade)) {
                        $Global->POST['Grade'][$grade->getId()]['Grade'] = $grade->getGrade();
                        $Global->POST['Grade'][$grade->getId()]['Comment'] = $grade->getComment();
                    }
                }
                $Global->savePost();

                $division = '';
                $tblDivision = $tblTest->getServiceTblDivision();
                if ($tblDivision) {
                    $division = $tblDivision->getServiceTblYear()->getName() . ' - ' .
                        $tblDivision->getTblLevel()->getServiceTblType()->getName() . ' - ' .
                        $tblDivision->getTblLevel()->getName() . $tblDivision->getName();
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Klasse:', $division,
                                        Panel::PANEL_TYPE_SUCCESS), 6
                                ),
                                new LayoutColumn(
                                    new Panel('Fach:', $tblTest->getServiceTblSubject()->getName(),
                                        Panel::PANEL_TYPE_SUCCESS), 6
                                )
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Zeitraum:', $tblTest->getServiceTblPeriod()->getName(),
                                        Panel::PANEL_TYPE_SUCCESS), 6
                                ),
                                new LayoutColumn(
                                    new Panel('Zensuren-Typ:', $tblTest->getTblGradeType()->getName(),
                                        Panel::PANEL_TYPE_SUCCESS), 6
                                )
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Beschreibung:', $tblTest->getDescription(),
                                        Panel::PANEL_TYPE_SUCCESS), 12
                                )
                            )),
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
                                                            $gradeList, null, array(
                                                            'Student' => 'Schüler',
                                                            'Grades' => 'Zensur',
                                                            'Comments' => 'Kommentar'
                                                        ), false)
                                                    )
                                                ),
                                            ))
                                            , new Primary('Speichern', new Save()))
                                        , $Grade
                                    )
                                )
                            ))
                        )),
                    ))
                );

                return $Stage;
            }
        } else {

            return new Warning('Test nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Test', 2);
        }

        return $Stage;
    }

    /**
     * @param null $ScoreCondition
     * @return Stage
     */
    public function frontendScore($ScoreCondition = null)
    {

        $Stage = new Stage('Zensuren-Berechnung', 'Berechnungsvorschriften');
        $Stage->addButton(
            new Standard('Zensuren-Gruppen', '/Education/Graduation/Gradebook/Score/Group', null, null,
                'Erstellen/Berarbeiten')
        );

        $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
        if ($tblScoreConditionAll) {
            foreach ($tblScoreConditionAll as &$tblScoreCondition) {
                $scoreGroups = '';
                $tblScoreGroups = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                if($tblScoreGroups) {
                    foreach ($tblScoreGroups as $tblScoreGroup){
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
                        array('Id' => $tblScoreCondition->getId()), 'Zensuren-Typen-Gruppen bearbeiten'));
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
                                'Option' => 'Optionen',
                            ))
                        ))
                    ))
                ), new Title('Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Gradebook::useService()->createScoreCondition($Form, $ScoreCondition)
                        ))
                    ))
                ), new Title('Hinzufügen'))
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
    public function frontendScoreGroup($ScoreGroup = null)
    {

        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Gruppen');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/Score', new ChevronLeft())
        );

        $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
        if ($tblScoreGroupAll) {
            foreach ($tblScoreGroupAll as &$tblScoreGroup) {
                $gradeTypes = '';
                $tblScoreGroupGradeTypes = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                if($tblScoreGroupGradeTypes) {
                    foreach ($tblScoreGroupGradeTypes as $tblScoreGroupGradeType){
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
                        array('Id' => $tblScoreGroup->getId()), 'Zensuren-Typen bearbeiten'));
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
                                'Option' => 'Optionen',
                            ))
                        ))
                    ))
                ), new Title('Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Gradebook::useService()->createScoreGroup($Form, $ScoreGroup)
                        ))
                    ))
                ), new Title('Hinzufügen'))
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
    public function frontendScoreGroupGradeTypeSelect($Id = null)
    {

        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typen einer Zensuren-Gruppe zuordnen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Group', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreGroup = Gradebook::useService()->getScoreGroupById($Id);
            if (empty($tblScoreGroup)) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreGroupGradeTypeListByGroup = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAll();
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
                            (new Danger('Entfernen', '/Education/Graduation/Gradebook/Score/Group/GradeType/Remove',
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
                                    new Panel('Zensuren-Gruppe', $tblScoreGroup->getName(), Panel::PANEL_TYPE_SUCCESS), 12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tblScoreGroupGradeTypeListByGroup, null,
                                        array(
                                            'Name' => 'Name',
                                            'Multiplier' => 'Faktor',
                                            'Option' => 'Option'
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new TableData($tblGradeTypeAll, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Option'
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
    public function frontendScoreGroupGradeTypeAdd($tblScoreGroupId = null, $tblGradeTypeId = null, $GradeType = null)
    {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ einer Zenuseren-Gruppe hinzufügen');

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblGradeType = Gradebook::useService()->getGradeTypeById($tblGradeTypeId);

        if ($GradeType['Multiplier'] == ''){
            $multiplier = 1;
        } else {
            $multiplier = $GradeType['Multiplier'];
        }

        if ($tblScoreGroup && $tblGradeType) {
            $Stage->setContent(Gradebook::useService()->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup, $multiplier));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeRemove($Id)
    {
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
    public function frontendScoreGroupSelect($Id = null)
    {

        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Gruppen einer Berechnungsvorschrift zuordnen');

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
                            (new Danger('Entfernen', '/Education/Graduation/Gradebook/Score/Group/Remove',
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
                                    new Panel('Berechnungsvorschrift', $tblScoreCondition->getName(), Panel::PANEL_TYPE_SUCCESS), 12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tblScoreConditionGroupListByCondition, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Option'
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new TableData($tblScoreGroupAll, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Option'
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
    public function frontendScoreGroupAdd($tblScoreGroupId = null, $tblScoreConditionId = null)
    {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Gruppe einer Berechnungsvorschrift hinzufügen');

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreGroup && $tblScoreCondition) {
            $Stage->setContent(Gradebook::useService()->addScoreConditionGroupList($tblScoreCondition, $tblScoreGroup));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupRemove($Id)
    {
        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Gruppe von einer Berechnungsvorschrift entfernen');

        $tblScoreConditionGroupList = Gradebook::useService()->getScoreConditionGroupListById($Id);
        if ($tblScoreConditionGroupList) {
            $Stage->setContent(Gradebook::useService()->removeScoreConditionGroupList($tblScoreConditionGroupList));
        }

        return $Stage;
    }
}