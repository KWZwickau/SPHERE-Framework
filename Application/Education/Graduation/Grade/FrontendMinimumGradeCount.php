<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;

class FrontendMinimumGradeCount extends FrontendGradeType
{
    /**
     * @param null $MinimumGradeCount
     *
     * @return Stage
     */
    public function frontendMinimumGradeCount($MinimumGradeCount = null): Stage
    {
        if(!isset($_POST['MinimumGradeCount']['Period'])){
            $_POST['MinimumGradeCount']['Period'] = SelectBoxItem::PERIOD_FULL_YEAR;
        }

        $Stage = new Stage('Mindestnotenanzahl', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Mindestnotenanzahlen verwaltet.' . '<br>' .
            'Es muss mindestens eine Klassenstufe vergeben werden für diese dann die Mindestnotenanzahl berücksichtigt wird.' . '<br>' .
            'Optional kann die Mindestnotenanzahl auf ein Fach und/oder ein Zensuren-Typ beschränkt werden.'
        );

        $TableContent = array();
        if (($tblMinimumGradeCountAll = Grade::useService()->getMinimumGradeCountAll())) {
            foreach ($tblMinimumGradeCountAll as $tblMinimumGradeCount) {
                $TableContent[] = array(
                    'GradeType' => $tblMinimumGradeCount->getGradeTypeDisplayName(),
                    'Period' => $tblMinimumGradeCount->getPeriodDisplayName(),
                    'Course' => $tblMinimumGradeCount->getCourseDisplayName(),
                    'Count' => $tblMinimumGradeCount->getCount(),
                    'Levels' => $tblMinimumGradeCount->getLevelListDisplayName(),
                    'Subjects' => $tblMinimumGradeCount->getSubjectListDisplayName(),
                    'Option' => (new Standard('', '/Education/Graduation/Grade/MinimumGradeCount/Edit', new Edit(),
                            array('Id' => $tblMinimumGradeCount->getId()), 'Bearbeiten'))
                        . (new Standard('', '/Education/Graduation/Grade/MinimumGradeCount/Destroy', new Remove(),
                            array('Id' => $tblMinimumGradeCount->getId()), 'Löschen'))
                );
            }
        }

        $Form = $this->formMinimumGradeCount()->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null,
                                array(
                                    'GradeType' => 'Zensuren-Typ',
                                    'Period' => 'Zeitraum',
                                    'Course' => 'SEKII - Kurs',
                                    'Count' => 'Anzahl',
                                    'Levels' => 'Klassenstufen',
                                    'Subjects' => 'Fächer',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array('0', 'asc'),
                                        array('1', 'asc'),
                                        array('2', 'asc'),
                                        array('3', 'asc')
                                    ),
                                    'columnDefs' => array(
                                        array('orderable' => false, 'targets' => -1),
                                    ),
                                )
                            )
                        ))
                    )),
                ), new Title(new Listing() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            // todo service
//                            new Well(Grade::useService()->updateMinimumGradeCount($Form, $MinimumGradeCount))
                            $Form
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
    private function formMinimumGradeCount(): Form
    {
        $tblGradeTypeList = Grade::useService()->getGradeTypeList(false);

        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_ALL, 'Alle Zensuren-Typen');
        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED, 'Nur große Zensuren-Typen (Fett markiert)');
        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED, 'Nur kleine Zensuren-Typen (nicht Fett markiert)');

        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FULL_YEAR, '-Gesamtes Schuljahr-');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FIRST_PERIOD, '1. Halbjahr');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_SECOND_PERIOD, '2. Halbjahr');

        $courseList[] = new SelectBoxItem(SelectBoxItem::COURSE_NONE, '-[ nicht ausgewählt ]-');
        $courseList[] = new SelectBoxItem(SelectBoxItem::COURSE_ADVANCED, 'Leistungskurs');
        $courseList[] = new SelectBoxItem(SelectBoxItem::COURSE_BASIC, 'Grundkurs');

        $schoolTypeList = array();
        if (($tblSchoolTypeListFromConsumer = School::useService()->getConsumerSchoolTypeCommonAll())) {
            foreach ($tblSchoolTypeListFromConsumer as $tblSchoolType) {
                $minLevel = $tblSchoolType->getMinLevel();
                $maxLevel = $tblSchoolType->getMaxLevel();
                for ($level = $minLevel; $level <= $maxLevel; $level++) {
                    $schoolTypeList[$tblSchoolType->getId()][$level]
                        = new CheckBox('MinimumGradeCount[' . $tblSchoolType->getId() . '][Levels][' . $level . ']', $level, 1);
                }
            }
        }

        $levelColumns = array();
        foreach ($schoolTypeList as $typeId => $levels) {
            if (($tblTypeItem = Type::useService()->getTypeById($typeId))) {
                ksort($levels);
                // für Sortierung
                if ($tblTypeItem->getName() == 'Grundschule') {
                    $key = 1;
                } elseif ($tblTypeItem->getName() == 'Mittelschule / Oberschule') {
                    $key = 2;
                } elseif ($tblTypeItem->getName() == 'Gymnasium') {
                    $key = 3;
                } else {
                    $key = 10  + $typeId;
                }

                $levelColumns[$key] = new LayoutColumn(
                    new Panel($tblTypeItem->getName(), $levels), 3
                );
            }
        }
        ksort($levelColumns);

        if (($tblSubjectAll = Subject::useService()->getSubjectAll())) {
            $tblSubjectAll = $this->getSorter($tblSubjectAll)->sortObjectBy('Name');
            $layoutColumns = array();
            /** @var TblSubject $tblSubject */
            foreach ($tblSubjectAll as $tblSubject) {
                $layoutColumns[] = new LayoutColumn(
                    new CheckBox('MinimumGradeCount[Subjects][' . $tblSubject->getId() . ']', $tblSubject->getDisplayName(), 1), 3
                );
            }

            $layoutSubjects = new Layout(new LayoutGroup(new LayoutRow($layoutColumns)));
        } else {
            $layoutSubjects = new Warning('Es sind keine Fächer vorhanden') . new Exclamation();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel(
                        'Fächer',
                        $layoutSubjects,
                        Panel::PANEL_TYPE_INFO
                    )
                ))
            )),
            new FormRow(array(
                new FormColumn(array(
                    new Panel(
                        'Klassenstufen'  . new DangerText('*'),
                        new Layout(new LayoutGroup(new LayoutRow($levelColumns))),
                        Panel::PANEL_TYPE_INFO
                    )
                ))
            )),
            new FormRow(array(
                new FormColumn(
                    new SelectBox('MinimumGradeCount[GradeType]', 'Zensuren-Typ', array('{{ Code }} - {{ Name }}' => $tblGradeTypeList)), 3
                ),
                new FormColumn(
                    new SelectBox('MinimumGradeCount[Period]', 'Zeitraum', array('{{ Name }}' => $periodList)), 3
                ),
                new FormColumn(
                    new SelectBox('MinimumGradeCount[Course]', 'SEKII - Kurs', array('{{ Name }}' => $courseList)), 3
                ),
                new FormColumn(
                    (new NumberField('MinimumGradeCount[Count]', '', 'Anzahl ', new Quantity()))->setRequired(), 3
                ),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new DangerText(new Primary('Speichern', new Save()) . ' * Pflichtfeld')
                )),
            ))
        )));
    }
}