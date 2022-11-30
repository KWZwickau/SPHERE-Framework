<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
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
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class FrontendTask extends FrontendGradeType
{
    /**
     * @return Stage
     */
    public function frontendTask(): Stage
    {
        $stage = new Stage();

        // todo Klassenlehrer

        // todo Schuljahr auswählen
        $tblYear = false;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            /** @var TblYear $tblYear */
            $tblYear = current($tblYearList);
        }

        $stage->setContent(
            ApiTask::receiverBlock($this->loadViewTaskList($tblYear ? $tblYear->getId() : 0), 'Content')
        );

        return $stage;
    }

    /**
     * @param $YearId
     *
     * @return string
     */
    public function loadViewTaskList($YearId): string
    {
        // todo Message abhängig ob Klassenlehrer oder Schulleitung
        // 'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebener Zensuren),
        //            wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.'

        $title = '<h3>Notenaufträge ' . new Muted(new Small('Übersicht')) . '</h3>'
            . new Container(new Muted('Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebenen Zensuren).'))
            . new Ruler();

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $dataList = array();
            if (($tblTaskList = Grade::useService()->getTaskListByYear($tblYear))) {
                foreach ($tblTaskList as $tblTask) {
                    $hasEdit = true;

                    $dataList[] = array(
                        'Date' => $tblTask->getDateString(),
                        'Type' => $tblTask->getTypeName(),
                        'Name' => $tblTask->getName(),
                        'SchoolTypes' => $tblTask->getSchoolTypes(true),
                        'EditPeriod' => $tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString(),
                        'Option' => ($hasEdit ? (new Standard('', ApiTask::getEndpoint(), new Edit(), array(), 'Bearbeiten'))
                                ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskEditContent($YearId, $tblTask->getId()))
                            : '')
                            . (new Standard('', ApiTask::getEndpoint(), new Equalizer(), array(), 'Zensurenübersicht'))
                                ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskGradeContent($tblTask->getId()))
                            . ($hasEdit && $tblTask->getHasTaskGrades()
                                ? null
                                : (new Standard('', ApiTask::getEndpoint(), new Remove(), array(), 'Löschen'))
                                    ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskDelete($tblTask->getId())))
                    );
                }
            }

            // todo option breite abhängig von buttons anzahl 1-3

            return $title . (new Primary('Notenauftrag anlegen', ApiTask::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskEditContent($YearId))
                . new TableData($dataList, null,
                    array(
                        'Date' => 'Stichtag',
                        'Type' => 'Kategorie',
                        'Name' => 'Name',
                        'SchoolTypes' => 'Schul&shy;arten',
                        'EditPeriod' => 'Bearbeitungszeitraum',
                        'Option' => '',
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc')
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0),
                            array('orderable' => false, 'targets' => -1),
                        ),
                    )
                );
        } else {
            return $title . new Warning('Schuljahr wurde nicht gefunden.', new Exclamation());
        }
    }

    /**
     * @param $YearId
     *
     * @return Standard
     */
    private function getBackButton($YearId): Standard
    {
        return (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
            ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskList($YearId));
    }

    /**
     * @param $form
     * @param $YearId
     * @param $TaskId
     *
     * @return string
     */
    public function getTaskEdit($form, $YearId, $TaskId = null): string
    {
        if ($TaskId && ($tblTask = Grade::useService()->getTaskById($TaskId))) {
            $title =  new Edit() . ($tblTask->getIsTypeBehavior() ? ' Kopfnotenauftrag' : ' Stichtagsnotenauftrag') . ' bearbeiten';
        } else {
            $title = new Plus() . ' Notenauftrag hinzufügen';
        }

        return new Title($this->getBackButton($YearId) . "&nbsp;&nbsp;&nbsp;&nbsp; $title")
            . new Well($form);
    }

    /**
     * @param $YearId
     * @param null $TaskId
     * @param bool $setPost
     * @param null $Data
     *
     * @return Form
     */
    public function formTask($YearId, $TaskId = null, bool $setPost = false, $Data = null): Form
    {
        $tblTask = Grade::useService()->getTaskById($TaskId);
        if ($setPost && $tblTask) {
            $global = $this->getGlobal();
            $global->POST['Data']['Name'] = $tblTask->getName();
            $global->POST['Data']['Date'] = $tblTask->getDateString();
            $global->POST['Data']['FromDate'] = $tblTask->getFromDateString();
            $global->POST['Data']['ToDate'] = $tblTask->getToDateString();
            $global->POST['Data']['ScoreType'] = $tblTask->getTblScoreType() ? $tblTask->getTblScoreType()->getId() : 0;
            $global->POST['Data']['IsAllYears'] = $tblTask->getIsAllYears();

            if (($tblGradeTypeList = $tblTask->getGradeTypes())) {
                foreach ($tblGradeTypeList as $tblGradeType) {
                    $global->POST['Data']['GradeTypes'][$tblGradeType->getId()] = 1;
                }
            }

            if (($tblDivisionCourseList = $tblTask->getDivisionCourses())) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    $global->POST['Data']['DivisionCourses'][$tblDivisionCourse->getId()] = 1;
                }
            }

            $global->savePost();
        }

        $typeList[1] = new SelectBoxItem(1, 'Leistungsüberprüfung');
        $typeList[2] = new SelectBoxItem(2, 'Kopfnote');

        $tblScoreTypeList = Grade::useService()->getScoreTypeAll();

        $columnsTop = array();
        if (!$TaskId) {
            $columnsTop[] =  new FormColumn(
                (new SelectBox('Data[Type]', 'Kategorie', array('Name' => $typeList)))
                    ->setRequired()
                    ->ajaxPipelineOnChange(ApiTask::pipelineLoadTaskGradeTypes())
                , 4
            );
        }
        $columnsTop[] = new FormColumn(
            (new TextField('Data[Name]', '', 'Name'))->setRequired()
        , $TaskId ? 12 : 8);

        $columnsBotton[] = new FormColumn(new SelectBox('Data[ScoreType]', 'Bewertungssystem überschreiben', array('Name' => $tblScoreTypeList)), 4);
        if (School::useService()->hasConsumerTechnicalSchool()) {
            $columnsBotton[] = new FormColumn(new CheckBox('Data[IsAllYears]', 'Notenauftrag über alle Schuljahre', 1), 8);
        }

        return new Form(new FormGroup(array(
            new FormRow($columnsTop),
            new FormRow(array(
                new FormColumn(
                    (new DatePicker('Data[Date]', '', 'Stichtag', new Calendar()))->setRequired(), 4
                ),
                new FormColumn(
                    (new DatePicker('Data[FromDate]', '', 'Bearbeitungszeitraum von', new Calendar()))->setRequired(), 4
                ),
                new FormColumn(
                    (new DatePicker('Data[ToDate]', '', 'Bearbeitungszeitraum bis', new Calendar()))->setRequired(), 4
                ),
            )),
            new FormRow($columnsBotton),
            new FormRow(new FormColumn(
                ApiTask::receiverBlock($this->loadTaskGradeTypes($Data, $tblTask ?: null), 'TaskGradeTypesContent')
            )),
            new FormRow(new FormColumn($this->getDivisionCoursesSelectForTaskContent($YearId))),
            new FormRow(new FormColumn(array(
                (new Primary('Speichern', ApiTask::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiTask::pipelineSaveTaskEdit($YearId, $TaskId)),
                (new Standard('Abbrechen', ApiTask::getEndpoint(), new Disable()))
                    ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskList($YearId))
            )))
        )));
    }

    /**
     * @param null $Data
     * @param TblTask|null $tblTask
     *
     * @return string
     */
    public function loadTaskGradeTypes($Data = null, TblTask $tblTask = null): string
    {
        if ((($tblTask && $tblTask->getIsTypeBehavior()) || (isset($Data['Type']) && $Data['Type'] == 2))
            && ($tblGradeTypeList = Grade::useService()->getGradeTypeList(true))
        ) {
            // bei neuen Kopfnotenaufträgen alle Kopfnoten-Zensuren-Typen auswählen
            if (!$tblTask) {
                $global = $this->getGlobal();
                foreach ($tblGradeTypeList as $tblGradeType) {
                    $global->POST['Data']['GradeTypes'][$tblGradeType->getId()] = 1;
                }
                $global->savePost();
            }

            $size = 3;
            $columnList = array();
            foreach ($tblGradeTypeList as $tblGradeType) {
                $columnList[] = new LayoutColumn(new CheckBox('Data[GradeTypes][' . $tblGradeType->getId() . ']', $tblGradeType->getName(), 1), $size);
            }

            return '<br />' . new Layout(new LayoutGroup(
                Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                new Title('Kopfnoten')
            )) . '<br />';
        }

        return '';
    }

    /**
     * @param $YearId
     *
     * @return Layout|Warning
     */
    public function getDivisionCoursesSelectForTaskContent($YearId)
    {
        if (!($tblYear = Term::useService()->getYearById($YearId))) {
            return new Warning('Schuljahr wurde nicht gefunden.', new Exclamation());
        }

        $layoutGroups = array();
        if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
            $layoutGroups[] = $temp;
        }
        if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))) {
            $layoutGroups[] = $temp;
        }
        if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_TEACHING_GROUP))) {
            $layoutGroups[] = $temp;
        }

        if (empty($layoutGroups)) {
            return new Warning('Keine entsprechenden Kurse gefunden.', new Exclamation());
        } else {
            return new Layout($layoutGroups);
        }
    }

    /**
     * @param TblYear $tblYear
     * @param string $TypeIdentifier
     *
     * @return false|LayoutGroup
     */
    private function getLayoutGroupForDivisionCoursesSelectByTypeIdentifier(TblYear $tblYear, string $TypeIdentifier)
    {
        $size = 3;
        $columnList = array();
        $contentPanelList = array();
        $toggleList = array();

        $tblDivisionCourseType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier($TypeIdentifier);
        $this->setContentPanelListForDivisionCourseType($contentPanelList, $toggleList, $tblYear, $TypeIdentifier);
        if (!empty($contentPanelList)) {
            ksort($contentPanelList);
            foreach ($contentPanelList as $schoolTypeId => $content) {
                if (($tblSchoolType = Type::useService()->getTypeById($schoolTypeId))) {
                    if (isset($toggleList[$tblSchoolType->getId()])) {
                        array_unshift($content, new ToggleSelective('Alle wählen/abwählen', $toggleList[$tblSchoolType->getId()]));
                    }
                    $columnList[] = new LayoutColumn(new Panel($tblSchoolType->getName(), $content, Panel::PANEL_TYPE_INFO), $size);
                }
            }

            return new LayoutGroup(
                Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                new Title($tblDivisionCourseType->getName() . 'n')
            );
        }

        return false;
    }

    /**
     * @param array $contentPanelList
     * @param array $toggleList
     * @param TblYear $tblYear
     * @param string $TypeIdentifier
     *
     * @return void
     */
    private function setContentPanelListForDivisionCourseType(array &$contentPanelList, array &$toggleList, TblYear $tblYear, string $TypeIdentifier)
    {
        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, $TypeIdentifier))) {
            $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('Name', new StringNaturalOrderSorter());
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())) {
                    foreach ($tblSchoolTypeList as $tblSchoolType) {
                        $name = "Data[DivisionCourses][{$tblDivisionCourse->getId()}]";
                        $toggleList[$tblSchoolType->getId()][$tblDivisionCourse->getId()] = $name;
                        $contentPanelList[$tblSchoolType->getId()][$tblDivisionCourse->getId()] = new CheckBox($name, $tblDivisionCourse->getDisplayName(), 1);
                        // erstmal die Kurse bei mehreren Schularten nur einmal anzeigen
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param $TaskId
     *
     * @return string
     */
    public function loadViewTaskDelete($TaskId): string
    {
        if (!($tblTask = Grade::useService()->getTaskById($TaskId))) {
            return new Danger('Der Notenauftrag wurde nicht gefunden', new Exclamation());
        }

        $YearId = ($tblYear = $tblTask->getServiceTblYear()) ? $tblYear->getId() : null;
        $countDivisionCourses = 0;
        if (($divisionCourses = $tblTask->getDivisionCourses())) {
            $countDivisionCourses = count($divisionCourses);
        }

        $type = $tblTask->getTypeName();

        return new Title($this->getBackButton($YearId) . "&nbsp;&nbsp;&nbsp;&nbsp;" . new Remove() . " $type löschen")
            . new Well(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . " Diesen $type wirklich löschen?",
                                array(
                                    'Schuljahr: ' . new Bold($tblTask->getYearName()),
                                    'Name: ' . new Bold($tblTask->getName()),
                                    'Stichtag: ' . $tblTask->getDateString(),
                                    'Bearbeitungszeitraum: ' . $tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString(),
                                    'Kurse: ' . ($countDivisionCourses ? new \SPHERE\Common\Frontend\Text\Repository\Danger($countDivisionCourses) : '0'),
                                    'Schularten: ' . $tblTask->getSchoolTypes(true)
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new  DangerLink('Ja', ApiTask::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(ApiTask::pipelineSaveTaskDelete($TaskId))
                            . (new Standard('Nein', ApiTask::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskList($YearId))
                        )
                    )
                )
            ));
    }

    /**
     * @param $TaskId
     *
     * @return string
     */
    public function getViewTaskGradeContent($TaskId): string
    {
        if (!($tblTask = Grade::useService()->getTaskById($TaskId))) {
            return new Danger('Der Notenauftrag wurde nicht gefunden', new Exclamation());
        }

        $typeName = $tblTask->getTypeName();
        $YearId = ($tblYear = $tblTask->getServiceTblYear()) ? $tblYear->getId() : 0;

        $content = new Panel(
            $typeName,
            $tblTask->getName() . ' ' . $tblTask->getDateString()
                . '&nbsp;&nbsp;' . new Muted(new Small(new Small('Bearbeitungszeitraum '.$tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString()))),
            Panel::PANEL_TYPE_INFO
        );
        if (($tblDivisionCourseList = $tblTask->getDivisionCourses())) {
            // todo Kurs auswahl: Buttons oder selectBox
            $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById(42);
            if ($tblTask->getIsTypeBehavior()) {
                $content = $this->getTaskGradeViewByBehaviorTask($tblTask, $tblDivisionCourse);
            }
        } else {
            $content .= new Warning("Es sind keine Kurse zu diesem $typeName zugeordnet.", new Exclamation());
        }

        return new Title($this->getBackButton($YearId) . "&nbsp;&nbsp;&nbsp;&nbsp;" . new Equalizer() . " $typeName - Zensurenübersicht")
            . $content;
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    private function getTaskGradeViewByBehaviorTask(TblTask $tblTask, TblDivisionCourse $tblDivisionCourse): string
    {
        // todo Kopfnotenvorschlag
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation',
            'ShowProposalBehaviorGrade'))
        ) {
            $showProposalBehaviorGrade = $tblSetting->getValue();
        } else {
            $showProposalBehaviorGrade = false;
        }

        $tblGradeTypeList = $tblTask->getGradeTypes();
        $headerList['Number'] = $this->getTableColumnHead('#');
        $headerList['Person'] = $this->getTableColumnHead('Schüler');
        if ($tblGradeTypeList) {
            $tblGradeTypeList = $this->getSorter($tblGradeTypeList)->sortObjectBy('Name');
            foreach ($tblGradeTypeList as $tblGradeType) {
                $headerList[$tblGradeType->getId()] = $this->getTableColumnHead($tblGradeType->getName());
            }
        }

        $bodyList = array();
        $count = 0;
        if (($tblYear = $tblTask->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                $bodyList[$tblPerson->getId()]['Number'] = $this->getTableColumnBody(++$count);
                $bodyList[$tblPerson->getId()]['Person'] = $this->getTableColumnBody($tblPerson->getLastFirstNameWithCallNameUnderline());

                if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYear))) {
                    $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('Name');
                }
                $tblTaskGradeList = array();
                if (($tempList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson)))
                {
                    foreach($tempList as $tblTaskGrade) {
                        if (($tblSubject = $tblTaskGrade->getServiceTblSubject())
                            && ($tblGradeType = $tblTaskGrade->getTblGradeType())
                        ) {
                            $tblTaskGradeList[$tblSubject->getId()][$tblGradeType->getId()] = $tblTaskGrade->getGrade();
                        }
                    }
                }
                if ($tblGradeTypeList) {
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        $contentList = array();
                        $sum = 0;
                        $countGrades = 0;
                        if ($tblSubjectList) {
                            /** @var TblSubject $tblSubject */
                            foreach ($tblSubjectList as $tblSubject) {
                                if (($gradeDisplay = $tblTaskGradeList[$tblSubject->getId()][$tblGradeType->getId()] ?? null)) {
                                    $gradeValue = str_replace('+', '', $gradeDisplay);
                                    $gradeValue = str_replace('-', '', $gradeValue);
                                    $gradeValue = str_replace(',', '.', $gradeValue);
                                    $sum += $gradeValue;
                                    $countGrades++;
                                } else {
                                    $gradeDisplay = new WarningText('f');
                                }
                                $contentList[] = $tblSubject->getAcronym() . ': ' . $gradeDisplay;
                            }
                        }
                        $average = ($countGrades > 0 ? new Bold('&#216; ' . str_replace('.', ',', round(floatval($sum) / $countGrades, 2))) . ' | ' : '');
                        $bodyList[$tblPerson->getId()][$tblGradeType->getId()] = $average . new Small(implode(' | ', $contentList));
                    }
                }
            }
        }

        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $columnList) {
            $rows[] = new TableRow($columnList);
        }
        $tableBody = new TableBody($rows);

        return new Table($tableHead, $tableBody, null, false, null, 'TableCustom');
    }
}