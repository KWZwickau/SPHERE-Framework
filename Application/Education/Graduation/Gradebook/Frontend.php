<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeStudentSubjectLink;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
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
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
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

        $Stage->setContent(
            Gradebook::useService()->getGradeBook(
                new Form(new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            new SelectBox('Select[Division]', 'Klasse',
                                array('{{serviceTblYear.Name}} - {{tblLevel.serviceTblType.Name}} - {{Name}}' => $tblDivisionAll)),
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
                        new Panel('Klasse:', $tblDivision->getName(),
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
                $tblTest->Division = $tblTest->getServiceTblDivision()->getName();
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
                    new SelectBox('Test[Division]', 'Klasse', array('Name' => $tblDivisionAll)), 6
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

            $Stage->setContent(
                new Layout (new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Klasse:', $tblTest->getServiceTblDivision()->getName(),
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
                /** @var TblGradeStudentSubjectLink $grade */
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

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Klasse:', $tblTest->getServiceTblDivision()->getName(),
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
}