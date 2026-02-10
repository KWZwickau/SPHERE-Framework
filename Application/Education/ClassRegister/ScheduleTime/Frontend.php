<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime;

use SPHERE\Application\Api\Education\ClassRegister\ApiScheduleTime;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity\TblScheduleTime;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendScheduleTime(): Stage
    {
        $stage = new Stage('Zeitplan', 'Übersicht');
        $stage->setContent(
            ApiScheduleTime::receiverModal()
            . (new Primary(new Plus() . ' Zeitplan hinzufügen', ApiScheduleTime::getEndpoint()))
                ->ajaxPipelineOnClick(ApiScheduleTime::pipelineOpenCreateScheduleTimeModal())
            . ApiScheduleTime::receiverBlock($this->loadScheduleTime(), 'ScheduleTime')
        );

        return $stage;
    }

    /**
     * @return string
     */
    public function loadScheduleTime(): string
    {
        $dataList = array();
        if (($tblScheduleTimeList = ScheduleTime::useService()->getScheduleTimeAll())) {
            foreach ($tblScheduleTimeList as $tblScheduleTime) {
                $dataList[] = array(
                    'Name' => $tblScheduleTime->getName(),
                    'SchoolTypes' => $tblScheduleTime->getDisplaySchoolTypes(),
                    'SecondaryLevel' => $tblScheduleTime->getDisplaySecondaryLevel(),
                    'Option' =>
                        (new Standard('', ApiScheduleTime::getEndpoint(), new Edit(), array(), 'Grunddaten des Stundenplans bearbeiten'))
                            ->ajaxPipelineOnClick(ApiScheduleTime::pipelineOpenEditScheduleTimeModal($tblScheduleTime->getId()))
                        . (new Standard('', ApiScheduleTime::getEndpoint(), new Remove(), array(), 'Stundenplan löschen'))
                            ->ajaxPipelineOnClick(ApiScheduleTime::pipelineOpenDeleteScheduleTimeModal($tblScheduleTime->getId()))
                );
            }
        }

        return new TableData(
            $dataList,
            null,
            array(
                'Name' => 'Name',
                'SchoolTypes' => 'Schularten',
                'SecondaryLevel' => 'Sekundarstufe',
                'Option' => '',
            ),
            array(
                'order' => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('width' => '60px', "targets" => -1),
                ),
            )
        );
    }

    /**
     * @param null $ScheduleTimeId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formScheduleTime($ScheduleTimeId = null, bool $setPost = false): Form
    {
        $maxLesson = 12;
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }

//        $times = [];
//        for($hour = 0; $hour <24; $hour++){
//            for($minute = 0; $minute < 60; $minute++){
//                $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT);
//                $times[$time] = $time;
//            }
//        }

        if ($ScheduleTimeId && ($tblScheduleTime = ScheduleTime::useService()->getScheduleTimeById($ScheduleTimeId))) {
            // beim Checken, der Input-Feldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();

                $Global->POST['Data']['Name'] = $tblScheduleTime->getName();
                $Global->POST['Data']['SecondaryLevel'] = $tblScheduleTime->getSecondaryLevel();

                foreach (ScheduleTime::useService()->getSchoolTypesByScheduleTime($tblScheduleTime) as $tblSchoolType) {
                    $Global->POST['Data']['SchoolTypes'][$tblSchoolType->getId()] = 1;
                }

                foreach (ScheduleTime::useService()->getScheduleTimeSlotsByScheduleTime($tblScheduleTime) as $tblScheduleTimeSlot) {
                    $Global->POST['Data']['Times'][$tblScheduleTimeSlot->getLesson()]['StartTime'] = $tblScheduleTimeSlot->getStartTime();
                    $Global->POST['Data']['Times'][$tblScheduleTimeSlot->getLesson()]['EndTime'] = $tblScheduleTimeSlot->getEndTime();
                }

                $Global->savePost();
            }
        } elseif ($setPost) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['SecondaryLevel'] = TblScheduleTime::SECONDARY_LEVEL_ALL;

            $Global->savePost();
        }

        if ($ScheduleTimeId) {
            $saveButton = (new Primary('Speichern', ApiScheduleTime::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiScheduleTime::pipelineEditScheduleTimeSave($ScheduleTimeId));
        } else {
            $saveButton = (new Primary('Speichern', ApiScheduleTime::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiScheduleTime::pipelineCreateScheduleTimeSave());
        }

        $formRowsSchoolTypes = [];
        if (($tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll())) {
            $columns = [];
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                $columns[] = new FormColumn(new CheckBox('Data[SchoolTypes][' . $tblSchoolType->getId() . ']', new Bold($tblSchoolType->getName()), 1), 3);
            }
            $formRowsSchoolTypes[] = new FormRow($columns);
        }
        $secondaryLevels = [];
        $secondaryLevels[] = new SelectBoxItem(TblScheduleTime::SECONDARY_LEVEL_ALL,
            TblScheduleTime::getDisplaySecondaryLevelBySecondaryLevel(TblScheduleTime::SECONDARY_LEVEL_ALL));
        $secondaryLevels[] = new SelectBoxItem(TblScheduleTime::SECONDARY_LEVEL_ONLY_FIRST,
            TblScheduleTime::getDisplaySecondaryLevelBySecondaryLevel(TblScheduleTime::SECONDARY_LEVEL_ONLY_FIRST));
        $secondaryLevels[] = new SelectBoxItem(TblScheduleTime::SECONDARY_LEVEL_ONLY_SECOND,
            TblScheduleTime::getDisplaySecondaryLevelBySecondaryLevel(TblScheduleTime::SECONDARY_LEVEL_ONLY_SECOND));
        $formRowsSchoolTypes[] = new FormRow(new FormColumn(
           new SelectBox('Data[SecondaryLevel]', 'Sekundarstufe', array('{{ Name }}' => $secondaryLevels))
        ));

        $formRows = [];
        for ($i = $minLesson; $i <= $maxLesson; $i++) {
        $formRows[] = new FormRow(array(
            new FormColumn(
                (new Container(new Bold($i . '. UE')))->setStyle([
                    'padding-top: 5px;',
                    'padding-left: 30px;',
                ])
                , 2),
            new FormColumn(
                new TextField('Data[Times][' . $i . '][StartTime]', '', '')
                , 5),
            new FormColumn(
                new TextField('Data[Times][' . $i . '][EndTime]', '', '')
                , 5),
            ));
        }
        $formRows[] = new FormRow(new FormColumn($saveButton));

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Data[Name]', '', 'Name'))
                            ->setRequired()
                            ->setAutoFocus()
                    ),
                ))
            )),
            new FormGroup($formRowsSchoolTypes), //, new Title('Schularten')),
            new FormGroup($formRows, new Title(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        (new Container(new Bold('UE')))->setStyle(['padding-left: 30px;'])
                    , 2),
                    new LayoutColumn(
                        new Bold('Startzeit') . ' (HH:mm)'
                    , 5),
                    new LayoutColumn(
                        new Bold('Endzeit') . ' (HH:mm)'
                    , 5)
                ))))
            )),
        )))->disableSubmitAction();
    }
}