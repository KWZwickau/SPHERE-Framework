<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiTask;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
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
use SPHERE\Common\Frontend\Icon\Repository\Download;
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
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class FrontendTask extends FrontendStudentOverview
{
    private function getHasHeadmasterRole(): bool
    {
//        return false;
        return Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Headmaster');
    }

    /**
     * @return Stage
     */
    public function frontendTask(): Stage
    {
        $tblYear = false;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            /** @var TblYear $tblYear */
            $tblYear = current($tblYearList);
            $global = $this->getGlobal();
            $global->POST['Data']['Year'] = $tblYear->getId();
            $global->savePost();
        }

        $form = (new Form(new FormGroup(new FormRow(new FormColumn(
            (new SelectBox('Data[Year]', '', array("{{ DisplayName }}" => Term::useService()->getYearAll())))
                ->ajaxPipelineOnChange(ApiTask::pipelineLoadViewTaskList($tblYear ? $tblYear->getId() : null))
        )))))->disableSubmitAction();

        $stage = new Stage(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Notenaufträge' , 10),
                new LayoutColumn(new PullClear($form) , 2),
            ))))
        );
        if ($this->getHasHeadmasterRole()) {
            $message = 'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebenen Zensuren).';
        } else {
            $message = 'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebener Zensuren), wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.';
        }
        $stage->setMessage($message);

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
        $hasHeadmasterRole = $this->getHasHeadmasterRole();

//        $title = '<h3>Notenaufträge ' . new Muted(new Small('Übersicht')) . '</h3>'
//            . new Container(new Muted($description))
//            . new Ruler();

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $tblTaskList = false;
            if ($hasHeadmasterRole) {
                $tblTaskList = Grade::useService()->getTaskListByYear($tblYear);
            } elseif (($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))
            ) {
                $tblTaskList = array();
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (!$tblDivisionCourse->getServiceTblSubject()
                        && ($tempList = Grade::useService()->getTaskListByDivisionCourse($tblDivisionCourse))
                    ) {
                        $tblTaskList = array_merge($tblTaskList, $tempList);
                    }
                }
                if ($tblTaskList) {
                    $tblTaskList = array_unique($tblTaskList);
                }
            }

            $dataList = array();
            if ($tblTaskList) {
                foreach ($tblTaskList as $tblTask) {
                    $dataList[] = array(
                        'Date' => $tblTask->getDateString(),
                        'Type' => $tblTask->getTypeName(),
                        'Name' => $tblTask->getName(),
                        'SchoolTypes' => $tblTask->getSchoolTypes(true),
                        'EditPeriod' => $tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString(),
                        'Option' =>
                            ($hasHeadmasterRole
                                ? (new Standard('', ApiTask::getEndpoint(), new Edit(), array(), 'Bearbeiten'))
                                    ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskEditContent($YearId, $tblTask->getId()))
                                : '')
                            . (new Standard('', ApiTask::getEndpoint(), new Equalizer(), array(), 'Zensurenübersicht'))
                                ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskGradeContent($tblTask->getId()))
                            . ($hasHeadmasterRole && !$tblTask->getHasTaskGrades()
                                ? (new Standard('', ApiTask::getEndpoint(), new Remove(), array(), 'Löschen'))
                                    ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskDelete($tblTask->getId()))
                                : '')
                    );
                }
            }

            return
                ($hasHeadmasterRole
                    ? (new Primary('Notenauftrag hinzufügen', ApiTask::getEndpoint(), new Plus()))
                        ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskEditContent($YearId))
                    : '')
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
                            array('searchable' => false, 'targets' => -1),
                        ),
                        'destroy' => true
                    )
                );
        } else {
            return new Warning('Schuljahr wurde nicht gefunden.', new Exclamation());
        }
    }

    /**
     * @param $YearId
     *
     * @return Standard
     */
    private function getBackButton($YearId): Standard
    {
        return (new Standard("Zurück", ApiTask::getEndpoint(), new ChevronLeft()))
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

        $typeList[1] = new SelectBoxItem(1, 'Stichtagsnotenauftrag');
        $typeList[2] = new SelectBoxItem(2, 'Kopfnotenauftrag');

//        $tblScoreTypeList = Grade::useService()->getScoreTypeAll();

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

        $columnsBotton = array();
//        $columnsBotton[] = new FormColumn(new SelectBox('Data[ScoreType]', 'Bewertungssystem überschreiben', array('Name' => $tblScoreTypeList)), 4);
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

        if (!($tblYear = $tblTask->getServiceTblYear())) {
            return new Danger('Das Schuljahr wurde nicht gefunden', new Exclamation());
        }

        $typeName = $tblTask->getTypeName();

        // bei Klassenlehrer/Kursleiter Kursauswahl einschränken
        $tblDivisionCourseList = $tblTask->getDivisionCourses();
        if (!$this->getHasHeadmasterRole()) {
            $tempList = array();
            if (($tblPerson = Account::useService()->getPersonByLogin())) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse,
                        DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER), $tblPerson
                    )) {
                        $tempList[] = $tblDivisionCourse;
                    }
                }
            }
            if (empty($tempList)) {
                return new Warning('Keinen entsprechenden Kursleiter gefunden', new Exclamation());
            } else {
                $tblDivisionCourseList = $tempList;
            }
        }

        if ($tblDivisionCourseList && count($tblDivisionCourseList) == 1) {
            $global = $this->getGlobal();
            $global->POST['Data']['DivisionCourse'] = (current($tblDivisionCourseList))->getId();
            $global->savePost();
        }

        $content = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel(
                    'Kurs auswählen',
                    (new Form(new FormGroup(new FormRow(new FormColumn(
                        (new SelectBox('Data[DivisionCourse]', '', array("{{ DisplayName }}" => $tblDivisionCourseList)))
                            ->ajaxPipelineOnChange(ApiTask::pipelineLoadDivisionCourseTaskGradeContent($TaskId))
                    )))))->disableSubmitAction(),
                    Panel::PANEL_TYPE_INFO
                )
            , 6),
            new LayoutColumn(
                new Panel(
                    $typeName,
                    $tblTask->getName() . ' ' . $tblTask->getDateString()
                    . new Container(new Muted('Bearbeitungszeitraum '.$tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString())),
                    Panel::PANEL_TYPE_INFO
                )
            , 6)
        ))));

        if ($tblDivisionCourseList) {
            $content .= ApiTask::receiverBlock('', 'DivisionCourseTaskGradeContent');
        } else {
            $content .= new Warning("Es sind keine Kurse zu diesem $typeName zugeordnet.", new Exclamation());
        }

        return new Title($this->getBackButton($tblYear->getId()) . "&nbsp;&nbsp;&nbsp;&nbsp;" . new Equalizer() . " $typeName - Zensurenübersicht")
            . $content;
    }

    /**
     * @param $TaskId
     * @param $Data
     *
     * @return Warning|string
     */
    public function loadDivisionCourseTaskGradeContent($TaskId, $Data): string
    {
        if (!($tblTask = Grade::useService()->getTaskById($TaskId))) {
            return new Danger('Der Notenauftrag wurde nicht gefunden', new Exclamation());
        }

        if (isset($Data['DivisionCourse']) && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Data['DivisionCourse']))) {
            $button = new Standard(
                'Herunterladen',
                '/Api/Education/Graduation/Grade/TaskGrades/Download',
                new Download(),
                array('TaskId' => $tblTask->getId(), 'DivisionCourseId' => $tblDivisionCourse->getId()),
                'Zensurenübersicht als Excel herunterladen'
            );

            if ($tblTask->getIsTypeBehavior()) {
                return $button . new Container('&nbsp;') . $this->getTaskGradeViewByBehaviorTask($tblTask, $tblDivisionCourse);
            } else {
                return $button . new Container('&nbsp;') . $this->getTaskGradeViewByAppointedDateTask($tblTask, $tblDivisionCourse);
            }
        } else {
            return (new Warning('Bitte wählen Sie zunächst einen Kurs aus.', new Exclamation()));
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    private function getTaskGradeViewByBehaviorTask(TblTask $tblTask, TblDivisionCourse $tblDivisionCourse): string
    {
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation', 'ShowProposalBehaviorGrade'))) {
            $showProposalBehaviorGrade = $tblSetting->getValue();
        } else {
            $showProposalBehaviorGrade = false;
        }

        $hasBehaviorTaskSetting = ($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading'
            ))
            && $tblSetting->getValue();

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

                if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYear, !$hasBehaviorTaskSetting))) {
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
                        $sum = 0.0;
                        $countGrades = 0;
                        if ($tblSubjectList) {
                            /** @var TblSubject $tblSubject */
                            foreach ($tblSubjectList as $tblSubject) {
                                if (($gradeDisplay = $tblTaskGradeList[$tblSubject->getId()][$tblGradeType->getId()] ?? null)) {
                                    if (($gradeValue = Grade::useService()->getGradeNumberValue($gradeDisplay)) !== null) {
                                        $sum += $gradeValue;
                                        $countGrades++;
                                    }
                                } else {
                                    $gradeDisplay = new WarningText('f');
                                }
                                $contentList[] = $tblSubject->getAcronym() . ': ' . $gradeDisplay;
                            }
                        }
                        $gradeListString = implode(' | ', $contentList);

                        // Kopfnotenvorschlag KL
                        if ($showProposalBehaviorGrade) {
                            if (($tblProposalBehaviorGrade = Grade::useService()->getProposalBehaviorGradeByPersonAndTaskAndGradeType(
                                    $tblPerson, $tblTask, $tblGradeType
                                ))
                                && ($proposalGrade = $tblProposalBehaviorGrade->getGrade())
                            ) {
                                $gradeListString .= ' | (KL-Vorschlag:' . $proposalGrade . ')';
                            }
                        }

                        $average = ($countGrades > 0 ? new Bold('&#216; ' . Grade::useService()->getGradeAverage($sum, $countGrades)) . ' | ' : '');
                        $bodyList[$tblPerson->getId()][$tblGradeType->getId()] = $average . new Small($gradeListString);
                    }
                }
            }
        }

        return $this->getTableCustom($headerList, $bodyList);
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     *
     * @return string
     */
    public function getTaskGradeViewByAppointedDateTask(TblTask $tblTask, TblDivisionCourse $tblDivisionCourse,
        TblPrepareCertificate $tblPrepareCertificate = null): string
    {
        $headerList['Number'] = $this->getTableColumnHead('#');
        $headerList['Person'] = $this->getTableColumnHead('Schüler');

        // Fächer der Schüler auch von Unterkursen ermitteln
        $tblSubjectList = array();
        $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
        DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);
        foreach ($tblDivisionCourseList as $temp) {
            if (($tempList = DivisionCourse::useService()->getSubjectListByDivisionCourse($temp))) {
                $tblSubjectList = array_merge($tblSubjectList, $tempList);
            }
        }
        $subjectListSum = array();
        $subjectListGradesCount = array();
        if ($tblSubjectList) {
            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('Name');
            /** @var TblSubject $tblSubject */
            foreach ($tblSubjectList as $tblSubject) {
                $headerList[$tblSubject->getId()] = $this->getTableColumnHead($tblSubject->getAcronym());
                $subjectListSum[$tblSubject->getId()] = 0.0;
                $subjectListGradesCount[$tblSubject->getId()] = 0;
             }
            $headerList['Average'] = $this->getTableColumnHead('&#216;');
        }

        $bodyList = array();
        $count = 0;
        if (($tblYear = $tblTask->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN');
            foreach ($tblPersonList as $tblPerson) {
                $bodyList[$tblPerson->getId()]['Number'] = $this->getTableColumnBody(++$count);
                $bodyList[$tblPerson->getId()]['Person'] = $this->getTableColumnBody($tblPerson->getLastFirstNameWithCallNameUnderline());

                $tblTaskGradeList = array();
                $tblTaskGradeTextList = array();
                if (($tempList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson)))
                {
                    foreach($tempList as $tblTaskGrade) {
                        if (($tblSubject = $tblTaskGrade->getServiceTblSubject())) {
                            if (($tblGradeText = $tblTaskGrade->getTblGradeText())) {
                                $tblTaskGradeTextList[$tblSubject->getId()] = $tblGradeText->getName();
                            } elseif ($tblTaskGrade->getGrade() !== null) {
                                $tblTaskGradeList[$tblSubject->getId()] = $tblTaskGrade->getGrade();
                            }
                        }
                    }
                }
                $sum = 0.0;
                $countGrades = 0;
                if ($tblSubjectList) {
                    list($startDate, $tblPeriod) = Grade::useService()->getStartDateAndPeriodByPerson($tblPerson, $tblYear, $tblTask);
                    foreach ($tblSubjectList as $tblSubject) {
                        // Endnote anzeigen
                        if ($tblPrepareCertificate
                            && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy($tblPrepareCertificate, $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType))
                            && $tblPrepareAdditionalGrade->getGrade() !== ''
                        ) {
                            $average = '';
                            $content = $tblPrepareAdditionalGrade->getGrade();
                        // Stichtagsnote anzeigen
                        } else {
                            $gradeValue = null;
                            if (isset($tblTaskGradeList[$tblSubject->getId()])) {
                                $content = $tblTaskGradeList[$tblSubject->getId()];
                                if (($gradeValue = Grade::useService()->getGradeNumberValue($content)) !== null) {
                                    $sum += $gradeValue;
                                    $countGrades++;

                                    $subjectListSum[$tblSubject->getId()] += $gradeValue;
                                    $subjectListGradesCount[$tblSubject->getId()]++;
                                }
                            } elseif (isset($tblTaskGradeTextList[$tblSubject->getId()])) {
                                $content = $tblTaskGradeTextList[$tblSubject->getId()];
                            } elseif ((DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear,
                                $tblSubject))) {
                                $content = new WarningText(new Bold('f'));
                            } else {
                                $content = '&nbsp;';
                            }

                            $average = Grade::useService()->getAppointedTaskAverage(
                                $tblPerson, $tblYear, $tblDivisionCourse, $tblSubject, $tblTask, $startDate ?: null, $tblPeriod ?: null
                            );
                            if ($average && $gradeValue !== null) {
                                $averageFloat = Grade::useService()->getGradeNumberValue($average);
                                if (($gradeValue - 0.5) <= $averageFloat && ($gradeValue + 0.5) >= $averageFloat) {
                                    $content = new Success(new Bold($content));
                                } else {
                                    $content = new \SPHERE\Common\Frontend\Text\Repository\Danger(new Bold($content));
                                }
                            }
                        }

                        $bodyList[$tblPerson->getId()][$tblSubject->getId()] = $this->getTableColumnBody($content . ' '
                            . ($average ? new Muted(new Small('&nbsp;&nbsp; &#216;' . $average)) : '')
                        );
                    }
                }

                // gesamt-durchschnitt schüler
                $bodyList[$tblPerson->getId()]['Average'] = $this->getTableColumnBody(Grade::useService()->getGradeAverage($sum, $countGrades));
            }
        }

        // Fach-klassen durchschnitt
        array_unshift($bodyList, $this->getBodyItemDivisionCourseSubjectAverage($tblSubjectList, $subjectListSum, $subjectListGradesCount));

        return $this->getTableCustom($headerList, $bodyList);
    }

    /**
     * @param array $tblSubjectList
     * @param array $subjectListSum
     * @param array $subjectListGradesCount
     *
     * @return array
     */
    private function getBodyItemDivisionCourseSubjectAverage(array $tblSubjectList, array $subjectListSum, array $subjectListGradesCount): array
    {
        $item['Number'] = $this->getTableColumnBody('');
        $item['Person'] = $this->getTableColumnBody(new Muted('&#216; Fach-Klasse'));
        if ($tblSubjectList) {
            foreach ($tblSubjectList as $tblSubject) {
                $item[$tblSubject->getId()] = $this->getTableColumnBody(
                    Grade::useService()->getGradeAverage($subjectListSum[$tblSubject->getId()], $subjectListGradesCount[$tblSubject->getId()])
                );
            }
        }
        $item['Average'] = $this->getTableColumnBody('');

        return $item;
    }
}