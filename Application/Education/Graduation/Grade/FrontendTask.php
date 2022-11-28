<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTask;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
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
            $global->POST['GradeType']['Type'] = $tblTask->getIsTypeBehavior() ? 2 : 1;
            $global->POST['Task']['Name'] = $tblTask->getName();
            $global->POST['Task']['Date'] = $tblTask->getDate();
            $global->POST['Task']['FromDate'] = $tblTask->getFromDate();
            $global->POST['Task']['ToDate'] = $tblTask->getToDate();
            $global->POST['Task']['ScoreType'] = $tblTask->getTblScoreType() ? $tblTask->getTblScoreType()->getId() : 0;
            // todo post setzen für kurse und zensuren-typen

            $global->savePost();
        }

        $typeList[1] = new SelectBoxItem(1, 'Leistungsüberprüfung');
        $typeList[2] = new SelectBoxItem(2, 'Kopfnote');

        $tblScoreTypeList = Grade::useService()->getScoreTypeAll();

        $columnsTop = array();
        // todo Kopfnoten bei Kopfnotenauftrag
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

        // todo auch direkt die Kurse laden, Schularten?

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
                // todo edit
                ApiTask::receiverBlock('', 'TaskGradeTypesContent')
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
     * @param $Data
     *
     * @return string
     */
    public function loadTaskGradeTypes($Data = null): string
    {
        // todo bei neu alle setzen

        if (isset($Data['Type']) && $Data['Type'] == 2
            && ($tblGradeTypeList = Grade::useService()->getGradeTypeList(true))
        ) {
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

    public function getDivisionCoursesSelectForTaskContent($YearId)
    {
        if (!($tblYear = Term::useService()->getYearById($YearId))) {
            return new Warning('Schuljahr wurde nicht gefunden.', new Exclamation());
        }

        $size = 3;
        $columnList = array();
        $contentPanelList = array();

        $this->setContentPanelListForDivisionCourseType($contentPanelList, $tblYear, TblDivisionCourseType::TYPE_DIVISION);
        $this->setContentPanelListForDivisionCourseType($contentPanelList, $tblYear, TblDivisionCourseType::TYPE_CORE_GROUP);
        $this->setContentPanelListForDivisionCourseType($contentPanelList, $tblYear, TblDivisionCourseType::TYPE_TEACHING_GROUP);

        if (!empty($contentPanelList)) {
            ksort($contentPanelList);
            foreach ($contentPanelList as $typeId => $content) {
                if (($tblDivisionCourseType = DivisionCourse::useService()->getDivisionCourseTypeById($typeId))) {
                    $columnList[] = new LayoutColumn(new Panel($tblDivisionCourseType->getName(), $content, Panel::PANEL_TYPE_INFO), $size);
                }
            }

            return new Layout(new LayoutGroup(
                Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                new Title("Kurs-Auswahl")
            ));
        } else {
            return new Warning('Keine entsprechenden Lehraufträge gefunden.', new Exclamation());
        }
    }

    private function setContentPanelListForDivisionCourseType(array &$contentPanelList, TblYear $tblYear, string $TypeIdentifier)
    {
        // todo nur edit wenn es noch keine Zensuren gibt für den Kurs, wobei brauchen wir wahrscheinlich nicht da die Zensuren nicht gelöscht werden
        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, $TypeIdentifier))) {
            $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('Name', new StringNaturalOrderSorter());
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                // todo alle verfügbaren über die eine Toggle Checkbox
                // todo Trennung nach Schularte

                $contentPanelList[$tblDivisionCourse->getType()->getId()][$tblDivisionCourse->getId()]
                    = new CheckBox("Data[DivisionCourses][{$tblDivisionCourse->getId()}]", $tblDivisionCourse->getDisplayName(), 1);
            }
        }
    }
}