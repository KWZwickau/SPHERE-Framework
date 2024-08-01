<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
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
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

class FrontendMinimumGradeCount extends FrontendGradeType
{
    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendMinimumGradeCount($Data = null): Stage
    {
        if(!isset($_POST['Data']['Period'])){
            $_POST['Data']['Period'] = SelectBoxItem::PERIOD_FULL_YEAR;
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
                                        array('type' => 'natural', 'targets' => 4),
                                    ),
                                )
                            )
                        ))
                    )),
                ), new Title(new Listing() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Grade::useService()->createMinimumGradeCount($Form, $Data))
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
        if (($tblSchoolTypeListFromConsumer = School::useService()->getConsumerSchoolTypeAll())) {
            foreach ($tblSchoolTypeListFromConsumer as $tblSchoolType) {
                $minLevel = $tblSchoolType->getMinLevel();
                $maxLevel = $tblSchoolType->getMaxLevel();
                for ($level = $minLevel; $level <= $maxLevel; $level++) {
                    $schoolTypeList[$tblSchoolType->getId()][$level]
                        = new CheckBox('Data[Levels][' . $tblSchoolType->getId() . '][' . $level . ']', $level, 1);
                }
            }
        }

        $levelColumns = array();
        foreach ($schoolTypeList as $typeId => $levels) {
            if (($tblTypeItem = Type::useService()->getTypeById($typeId))) {
//                ksort($levels);
                // für Sortierung
                if ($tblTypeItem->getName() == 'Grundschule') {
                    $key = 1;
                } elseif ($tblTypeItem->getName() == 'Mittelschule / Oberschule') {
                    $key = 2;
                } elseif ($tblTypeItem->getName() == 'Gymnasium') {
                    $key = 3;
                } else {
                    $key = 10 + $typeId;
                }
                // erstes Element wird ignoriert, deswegen wird ein "inhaltsleeres" vorn angefügt.
                array_unshift($levels, 'null');
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
                    new CheckBox('Data[Subjects][' . $tblSubject->getId() . ']', $tblSubject->getDisplayName(), 1), 3
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
                    new SelectBox('Data[GradeType]', 'Zensuren-Typ', array('{{ Code }} - {{ Name }}' => $tblGradeTypeList)), 3
                ),
                new FormColumn(
                    new SelectBox('Data[Period]', 'Zeitraum', array('{{ Name }}' => $periodList)), 3
                ),
                new FormColumn(
                    new SelectBox('Data[Course]', 'SEKII - Kurs', array('{{ Name }}' => $courseList)), 3
                ),
                new FormColumn(
                    (new NumberField('Data[Count]', '', 'Anzahl ', new Quantity()))->setRequired(), 3
                ),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new DangerText(new Primary('Speichern', new Save()) . ' * Pflichtfeld')
                )),
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditMinimumGradeCount($Id = null, $Data = null)
    {
        $Stage = new Stage('Mindestnotenanzahl', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Grade/MinimumGradeCount', new ChevronLeft())
        );

        if ($tblMinimumGradeCount = Grade::useService()->getMinimumGradeCountById($Id)) {
            $tblGradeType = $tblMinimumGradeCount->getTblGradeType();

            $global = $this->getGlobal();
            if (!$global->POST) {
                $global->POST['Data']['GradeType'] = $tblGradeType ? $tblGradeType : -$tblMinimumGradeCount->getHighlighted();
                $global->POST['Data']['Period'] = $tblMinimumGradeCount->getPeriod();
                $global->POST['Data']['Course'] = $tblMinimumGradeCount->getCourse();
                $global->POST['Data']['Count'] = $tblMinimumGradeCount->getCount();

                if (($levelList = Grade::useService()->getMinimumGradeCountLevelLinkByMinimumGradeCount($tblMinimumGradeCount))) {
                    foreach ($levelList as $levelItem) {
                        if (($tblSchoolType = $levelItem->getServiceTblSchoolType())) {
                            $global->POST['Data']['Levels'][$tblSchoolType->getId()][$levelItem->getLevel()] = 1;
                        }
                    }
                }
                if (($subjectList = Grade::useService()->getMinimumGradeCountSubjectLinkByMinimumGradeCount($tblMinimumGradeCount))) {
                    foreach ($subjectList as $subjectItem) {
                        if (($tblSubject = $subjectItem->getServiceTblSubject())) {
                            $global->POST['Data']['Subjects'][$tblSubject->getId()] = 1;
                        }
                    }
                }
                $global->savePost();
            }

            $Form = $this->formMinimumGradeCount()
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Grade::useService()->updateMinimumGradeCount($Form, $Data, $tblMinimumGradeCount))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Mindestnotenanzahl nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/MinimumGradeCount', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyMinimumGradeCount($Id = null, $Confirm = false)
    {
        $Stage = new Stage('Mindesnotenanzahl', 'Löschen');

        if ($tblMinimumGradeCount = Grade::useService()->getMinimumGradeCountById($Id)) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Grade/MinimumGradeCount', new ChevronLeft())
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new Question() . ' Diese Mindestnotenanzahl wirklich löschen?',
                            array(
                                'Zensuren-Typ: ' . $tblMinimumGradeCount->getGradeTypeDisplayName(),
                                'Zeitraum: ' . $tblMinimumGradeCount->getPeriodDisplayName(),
                                'SEKII - Kurs: ' . $tblMinimumGradeCount->getCourseDisplayName(),
                                'Anzahl: ' . $tblMinimumGradeCount->getCount(),
                                'Klassenstufen: ' . $tblMinimumGradeCount->getLevelListDisplayName(),
                                'Fächer: ' . $tblMinimumGradeCount->getSubjectListDisplayName()
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Graduation/Grade/MinimumGradeCount/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/Education/Graduation/Grade/MinimumGradeCount', new Disable())
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Grade::useService()->removeMinimumGradeCount($tblMinimumGradeCount)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Die Mindestnotenanzahl wurde gelöscht')
                                : new Danger(new Ban() . ' Die Mindestnotenanzahl konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Graduation/Grade/MinimumGradeCount', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }

            return $Stage;
        }

        return $Stage . new Danger('Mindestnotenanzahl nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Grade/MinimumGradeCount', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadViewMinimumGradeCountReportingContent($Data = null): string
    {
        if ($Data == null) {
            $global = $this->getGlobal();

            $global->POST['Data']['Period'] = SelectBoxItem::PERIOD_FULL_YEAR;

            $global->savePost();
        }

        $typeSelectBox = new SelectBox('Data[Type]', 'Schulart', array('Name' => Type::useService()->getTypeAll()));
        if (Grade::useService()->getRole() !== 'Teacher') {
            $typeSelectBox->setRequired();
        }
        $divisionTextField = new TextField('Data[DivisionName]', '', 'Klasse/Stammgruppe');

        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FULL_YEAR, '-Gesamtes Schuljahr-');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FIRST_PERIOD, '1. Halbjahr');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_SECOND_PERIOD, '2. Halbjahr');

        $periodSelectBox = new SelectBox('Data[Period]', 'Zeitraum', array('Name' => $periodList));

        $button = (new \SPHERE\Common\Frontend\Link\Repository\Primary('Filtern', '', new Filter()))
            ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewMinimumGradeCountReportingContent());

        $form = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel(
                    'Filter',
                    new Layout (new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                $typeSelectBox, 4
                            ),
                            new LayoutColumn(
                                $divisionTextField, 4
                            ),
                            new LayoutColumn(
                                $periodSelectBox, 4
                            ),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                $button
                            ),
                        )),
                    ))),
                    Panel::PANEL_TYPE_INFO
                )
            )
        )))))->disableSubmitAction();

        return new Title('Mindestnotenanzahl', 'Auswertung')
            . $form
            . Grade::useService()->loadMinimumGradeCountReporting($Data);
    }
}