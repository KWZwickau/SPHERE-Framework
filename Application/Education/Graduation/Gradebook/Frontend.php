<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Frontend
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
                                //'Option' => 'Option'
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
            ->appendFormButton(new Primary('Anlegen'))
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
                    new TextField('GradeType[Description]', '', 'Beschreibung'), 9
                ),
                new FormColumn(
                    new CheckBox('GradeType[IsHighlighted]', 'Fett markiert', 1), 3
                )
            ))
        )));
    }

    /**
     * @param $Select
     * @return Stage
     */
    public function frontendGradeBook($Select)
    {

        $Stage = new Stage('Zensuren', 'Notenbuch');

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblSubjectAll = Subject::useService()->getSubjectAll();

        $Stage->setContent(
            Gradebook::useService()->getGradeBook(
                new Form(new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            new SelectBox('Select[Division]', 'Klasse', array('Name' => $tblDivisionAll)), 6
                        ),
                        new FormColumn(
                            new SelectBox('Select[Subject]', 'Fach', array('Name' => $tblSubjectAll)), 6
                        )
                    )),
                )), new Primary('Ausw&auml;hlen'))
                , $Select)
        );

        return $Stage;
    }

    /**
     * @param $DivisionId
     * @param $SubjectId
     * @param $Data
     * @return string
     */
    public function frontendSelectedGradeBook($DivisionId, $SubjectId, $Data)
    {
        $Stage = new Stage('Zensuren', 'Notenbuch');

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        $tblYear = $tblDivision->getServiceTblYear();
        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
        $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
        $tblGradeTypeList = Gradebook::useService()->getGradeTypeAll();

        $rowList = array();
        $columnList[] = new LayoutColumn(new Title(new Bold('Sch&uuml;ler')), 2);
        if ($tblPeriodList) {
            $width = floor(10 / count($tblPeriodList));
            foreach ($tblPeriodList as $tblPeriod) {
                $columnList[] = new LayoutColumn(
                    new Title(new Bold($tblPeriod->getName()))
//                    . Gradebook::useService()->createGrades(new Form(new FormGroup(new FormRow(new FormColumn(array(
//                        new SelectBox('GradeType', '', array('Name' => $tblGradeTypeList))
//                    )))), new Primary('Hinzuf&uuml;gen', new Plus())
//                    ), $GradeType, $tblStudentList, $tblSubject, $tblPeriod)
                    , $width
                );
            }
            $rowList[] = new LayoutRow($columnList);
            $columnList = array();
            $columnList[] = new LayoutColumn(new Header(' '), 2);
            foreach ($tblPeriodList as $tblPeriod) {
                if (count($tblStudentList) > 0) {
                    $gradeList = Gradebook::useService()->getGradesByStudentAndSubjectAndPeriod($tblStudentList[0],
                        $tblSubject, $tblPeriod);
                    if ($gradeList) {
                        $columnSubList = array();
                        foreach ($gradeList as $grade) {
                            $columnSubList[] = new LayoutColumn(
                                new Header($grade->getTblGradeType()->getIsHighlighted()
                                    ? new Bold($grade->getTblGradeType()->getCode()) : $grade->getTblGradeType()->getCode()),
                                1);
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
                                        ? new Bold($grade->getGrade()) : $grade->getGrade()),
                                    1);
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

        return $Stage
        . new Layout (new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel('Klasse:', $tblDivision->getName(),
                    Panel::PANEL_TYPE_SUCCESS), 6
            ),
            new LayoutColumn(
                new Panel('Fach:', $tblSubject->getName(),
                    Panel::PANEL_TYPE_SUCCESS), 6
            )
        ))))
        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
            Gradebook::useService()->createGrades(new Form(new FormGroup(new FormRow(array(
                    new FormColumn(
                        new SelectBox('Data[Period]', '', array('Name' => $tblPeriodList)), 2
                    ),
                    new FormColumn(
                        new SelectBox('Data[GradeType]', '', array('Name' => $tblGradeTypeList)), 2
                    ),
                    new FormColumn(
                        new Primary('Hinzuf&uuml;gen', new Plus()), 2
                    )
                )))
            ), $Data, $tblStudentList, $tblSubject)
        )), new Title('Neue Zensur hinzuf&uuml;gen')))
        . new Layout(new LayoutGroup($rowList));
    }
}