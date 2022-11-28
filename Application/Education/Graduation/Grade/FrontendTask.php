<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
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
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class FrontendTask extends FrontendGradeType
{
    /**
     * @return Stage
     */
    public function frontendTask(): Stage
    {
        $stage = new Stage('Notenaufträge', 'Übersicht');
        $stage->setMessage(
            'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebenen Zensuren).'
        );

        // todo Klassenlehrer

        // todo Schuljahr auswählen
        $tblYear = false;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            /** @var TblYear $tblYear */
            $tblYear = current($tblYearList);
        }

        $stage->setContent(ApiTask::receiverBlock($this->loadViewTaskList($tblYear ? $tblYear->getId() : null), 'Content'));

        return $stage;
    }

    /**
     * @param $YearId
     *
     * @return string
     */
    public function loadViewTaskList($YearId): string
    {
        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $dataList = array();
            if (($tblTaskList = Grade::useService()->getTaskListByYear($tblYear))) {
                foreach ($tblTaskList as $tblTask) {
                    $hasEdit = true;

                    $dataList[] = array(
                        'Date' => $tblTask->getDateString(),
                        'Type' => $tblTask->getTypeName(),
                        'Name' => $tblTask->getName(),
                        'EditPeriod' => $tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString(),
                        'Option' => ($hasEdit ? (new Standard('', ApiTask::getEndpoint(), new Edit(), array(), 'Bearbeiten'))
                                ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskEditContent($YearId, $tblTask->getId()))
                            : '')
//                            . ($tblTask->isLocked() ? null : new Standard('',
//                                '/Education/Graduation/Evaluation/Task/Headmaster/Destroy', new Remove(),
//                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
//                                'Löschen'))
//                            . (new Standard('',
//                                '/Education/Graduation/Evaluation/Task/Headmaster/Division',
//                                new Listing(),
//                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
//                                'Klassen zuordnen')
//                            )
//                            . (new Standard('',
//                                '/Education/Graduation/Evaluation/Task/Headmaster/Grades',
//                                new Equalizer(),
//                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
//                                'Zensurenübersicht')
//                            ),
                    );
                }
            }

            return (new Primary('Notenauftrag anlegen', ApiTask::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskEditContent($YearId))
                . new TableData($dataList, null,
                    array(
                        'Date' => 'Stichtag',
                        'Type' => 'Kategorie',
                        'Name' => 'Name',
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
            return new Warning('Schuljahr wurde nicht gefunden.', new Exclamation());
        }
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

        return new Title(
                (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiTask::pipelineLoadViewTaskList($YearId))
                . "&nbsp;&nbsp;&nbsp;&nbsp; $title"
            )
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
}