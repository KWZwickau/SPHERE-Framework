<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable;

use SPHERE\Application\Api\Education\ClassRegister\ApiTimetable;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\Timetable
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendTimetable(): Stage
    {
        $stage = new Stage('Stundenplan', 'Übersicht');
        $hasRightHeadmaster = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting');

        $stage->setContent(
            ApiTimetable::receiverModal()
            . ($hasRightHeadmaster
                ? (new Primary(new Plus() . ' Stundenplan hinzufügen', ApiTimetable::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiTimetable::pipelineOpenCreateTimetableModal())
                : ''
            )
            . ApiTimetable::receiverBlock($this->loadTimetable(), 'Timetable')
        );

        return $stage;
    }

    /**
     * @return string
     */
    public function loadTimetable(): string
    {
        $hasRightHeadmaster = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting');
        $dataList = array();
        if (($tblTimeTableList = Timetable::useService()->getTimetableAll())) {
            foreach ($tblTimeTableList as $tblTimetable) {
                $dataList[] = array(
                    'Name' => $tblTimetable->getName(),
                    'Description' => $tblTimetable->getDescription(),
                    'DateFrom' => $tblTimetable->getDateFrom(),
                    'DateTo' => $tblTimetable->getDateTo(),
                    'Option' =>
                        (new Standard('', '/Education/ClassRegister/Digital/Timetable/Select', new EyeOpen(), array('TimetableId' => $tblTimetable->getId()),
                            'Inhalt des Stundenplans bearbeiten'))
                        . ($hasRightHeadmaster
                            ? (new Standard('', ApiTimetable::getEndpoint(), new Edit(), array(), 'Grunddaten des Stundenplans bearbeiten'))
                                ->ajaxPipelineOnClick(ApiTimetable::pipelineOpenEditTimetableModal($tblTimetable->getId()))
                                . (new Standard('', ApiTimetable::getEndpoint(), new Remove(), array(), 'Stundenplan löschen'))
                                    ->ajaxPipelineOnClick(ApiTimetable::pipelineOpenDeleteTimetableModal($tblTimetable->getId()))
                            : ''
                        )
                );
            }
        }

        return new TableData(
            $dataList,
            null,
            array(
                'Name' => 'Name',
                'Description' => 'Beschreibung',
                'DateFrom' => 'Gültig ab',
                'DateTo' => 'Gültig bis',
                'Option' => '',
            ),
            array(
                'columnDefs' => array(array('width' => '100px', "targets" => -1),
            ),
        ));
    }

    /**
     * @param $TimeTableId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formTimetable($TimeTableId = null, bool $setPost = false): Form
    {
        if ($TimeTableId && ($tblTimeTable = TimeTable::useService()->getTimeTableById($TimeTableId))) {
            // beim Checken, der Input-Feldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Name'] = $tblTimeTable->getName();
                $Global->POST['Data']['Description'] = $tblTimeTable->getDescription();
                $Global->POST['Data']['DateFrom'] = $tblTimeTable->getDateFrom();
                $Global->POST['Data']['DateTo'] = $tblTimeTable->getDateTo();

                $Global->savePost();
            }
        }

        if ($TimeTableId) {
            $saveButton = (new Primary('Speichern', ApiTimetable::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiTimeTable::pipelineEditTimetableSave($TimeTableId));
        } else {
            $saveButton = (new Primary('Speichern', ApiTimetable::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiTimetable::pipelineCreateTimetableSave());
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Data[Name]', '', 'Name'))->setRequired()
                    , 4),
                    new FormColumn(array(
                        (new DatePicker('Data[DateFrom]', '', 'Gültig ab', new Clock()))->setRequired()
                    ), 4),
                    new FormColumn(array(
                        (new DatePicker('Data[DateTo]', '', 'Gültig bis', new Clock()))->setRequired()
                    ), 4),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Description]', '', 'Beschreibung')
                    )
                )),
                new FormRow(array(
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param $TimetableId
     *
     * @return string
     */
    public function frontendSelectDivisionCourse($TimetableId = null): string
    {
        $stage = new Stage('Stundenplan', 'Kurs auswählen');
        $stage->addButton((new Standard('Zurück', '/Education/ClassRegister/Digital/Timetable', new ChevronLeft())));

        $hasRightHeadmaster = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting');
        $tblPerson = Account::useService()->getPersonByLogin();

        if (($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            $array[] = $tblTimetable->getName();
            if ($tblTimetable->getDescription()) {
                $array[] = $tblTimetable->getDescription();
            }

            $tblDivisionCourseList = array();
            $dataList = array();
            if (($tblYearList = Term::useService()->getYearAllByDate($tblTimetable->getDateFrom(true)))) {
                foreach ($tblYearList as $tblYear) {
                    // Schulleitung
                    if ($hasRightHeadmaster) {
                        if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                            TblDivisionCourseType::TYPE_DIVISION))) {
                            $tblDivisionCourseList = $tblDivisionCourseListDivision;
                        }
                        if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                            TblDivisionCourseType::TYPE_CORE_GROUP))) {
                            $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
                        }
                    // Klassenlehrer
                    } else {
                        if ($tblPerson
                            && ($tblTempList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))
                        ) {
                            foreach ($tblTempList as $tblTemp) {
                                if ($tblTemp->getIsDivisionOrCoreGroup()) {
                                    $tblDivisionCourseList[] = $tblTemp;
                                }
                            }
                        }
                    }
                }
            }

            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                $count = 0;
                if (($tblTimetableNodeList = Timetable::useService()->getTimetableNodeListByTimetableAndDivisionCourse($tblTimetable, $tblDivisionCourse))) {
                    $count = count($tblTimetableNodeList);
                }

                $dataList[] = array(
                    'Year' => $tblDivisionCourse->getYearName(),
                    'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                    'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                    'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                    'Count' => $count,
                    'Option' => new Standard(
                        '',
                        '/Education/ClassRegister/Digital/Timetable/Show',
                        new Select(),
                        array(
                            'TimetableId' => $tblTimetable->getId(),
                            'DivisionCourseId' => $tblDivisionCourse->getId(),
                        ),
                        'Auswählen'
                    )
                );
            }

            $table = new TableData($dataList, null, array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'DivisionCourseType' => 'Kurs-Typ',
                'SchoolTypes' => 'Schularten',
                'Count' => 'Anzahl Einträge',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            ));

            $stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Stundenplan', $array, Panel::PANEL_TYPE_INFO)
                        , 6),
                        new LayoutColumn(
                            new Panel('Gültigkeit', $tblTimetable->getDateFrom() . ' - ' . $tblTimetable->getDateTo(), Panel::PANEL_TYPE_INFO)
                        , 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $table
                        )
                    ))
                )))
            );

            return $stage;
        } else {
            return $stage . (new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation()));
        }
    }

    /**
     * @param null $TimetableId
     * @param null $DivisionCourseId
     *
     * @return string
     */
    public function frontendShowTimetable($TimetableId = null, $DivisionCourseId = null): string
    {
        $stage = new Stage('Stundenplan', 'Bearbeiten');
        $stage->addButton((new Standard('Zurück', '/Education/ClassRegister/Digital/Timetable/Select', new ChevronLeft(), array('TimetableId' => $TimetableId))));

        if (!($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            return $stage . new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $stage . new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        $array[] = $tblTimetable->getName();
        if ($tblTimetable->getDescription()) {
            $array[] = $tblTimetable->getDescription();
            $array[] = 'Gültigkeit: ' . $tblTimetable->getDateFrom() . ' - ' . $tblTimetable->getDateTo();
        }

        $maxLesson = 12;
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }

        $dayNames = array(
            '0' => 'Sonntag',
            '1' => 'Montag',
            '2' => 'Dienstag',
            '3' => 'Mittwoch',
            '4' => 'Donnerstag',
            '5' => 'Freitag',
            '6' => 'Samstag',
        );

        if ($tblDivisionCourse->getHasSaturdayLessons()) {
            $daysInWeek = 6;
            $widthLesson =  '4%';
            $widthDay = '16%';
        } else {
            $daysInWeek = 5;
            $widthLesson =  '5%';
            $widthDay = '19%';
        }

        $headerList = array();
        $headerList['Lesson'] = Digital::useFrontend()->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), $widthLesson);
        $bodyList = array();

        for ($day = 1; $day <= $daysInWeek; $day++) {
            $contentHeader = ' ' . (new Link($dayNames[$day] . ' ' . new Pen(), '/Education/ClassRegister/Digital/Timetable/Edit', null, array(
                    'TimetableId' => $tblTimetable->getId(),
                    'DivisionCourseId' => $tblDivisionCourse->getId(),
                    'DayNumber' => $day
                )));

            $headerList[$day] = Digital::useFrontend()->getTableHeadColumn($contentHeader, $widthDay);

            if (($tblTimetableNodeList = Timetable::useService()->getTimetableNodeListByTimetableAndDivisionCourseAndDay($tblTimetable, $tblDivisionCourse, $day))) {
                foreach ($tblTimetableNodeList as $tblTimetableNode) {
                    $bodyList[$tblTimetableNode->getHour()][$day]
                        = (isset($bodyList[$tblTimetableNode->getHour()][$day]) ? $bodyList[$tblTimetableNode->getHour()][$day] . ', ' : '')
                            . (($tblSubject = $tblTimetableNode->getServiceTblSubject()) ? $tblSubject->getAcronym() : '');
                }
            }
        }
        $tableHead = new TableHead(new TableRow($headerList));

        $rows = array();
        for ($i = $minLesson; $i <= $maxLesson; $i++) {
            $columns = array();
            $columns[] = (new TableColumn(new Center($i)))
                ->setVerticalAlign('middle')
                ->setMinHeight('30px')
                ->setPadding('3');
            for ($j = 1; $j <= $daysInWeek; $j++) {
                $cell = '&nbsp;';
                if (isset($bodyList[$i][$j])) {
                    $cell = $bodyList[$i][$j];
                }

                $columns[] = (new TableColumn($cell))
                    ->setVerticalAlign('middle')
                    ->setMinHeight('30px')
                    ->setPadding('3');
            }
            $rows[] = new TableRow($columns);
        }

        $tableBody = new TableBody($rows);
//        $table = new Table($tableHead, $tableBody, null, false, null, 'TableData');

        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel($tblDivisionCourse->getTypeName(), $tblDivisionCourse->getDisplayName(), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new LayoutColumn(
                        new Panel('Stundenplan', $array, Panel::PANEL_TYPE_INFO)
                        , 6)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        $table
                    )
                ))
            )))

        );

        return $stage;
    }

    /**
     * @param null $TimetableId
     * @param null $DivisionCourseId
     * @param null $DayNumber
     *
     * @return string
     */
    public function frontendEditTimetable($TimetableId = null, $DivisionCourseId = null, $DayNumber = null): string
    {
        $stage = new Stage('Stundenplan', 'Bearbeiten');
        $stage->addButton((new Standard(
            'Zurück', '/Education/ClassRegister/Digital/Timetable/Show', new ChevronLeft(),
            array('TimetableId' => $TimetableId, 'DivisionCourseId' => $DivisionCourseId))
        ));

        if (!($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            return $stage . new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $stage . new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        $array[] = $tblTimetable->getName();
        if ($tblTimetable->getDescription()) {
            $array[] = $tblTimetable->getDescription();
            $array[] = 'Gültigkeit: ' . $tblTimetable->getDateFrom() . ' - ' . $tblTimetable->getDateTo();
        }

        $dayNames = array(
            '0' => 'Sonntag',
            '1' => 'Montag',
            '2' => 'Dienstag',
            '3' => 'Mittwoch',
            '4' => 'Donnerstag',
            '5' => 'Freitag',
            '6' => 'Samstag',
        );

        $stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel($tblDivisionCourse->getTypeName(), $tblDivisionCourse->getDisplayName(), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new LayoutColumn(
                        new Panel('Stundenplan', $array, Panel::PANEL_TYPE_INFO)
                        , 6)
                )),
                new LayoutRow(array(
                    new LayoutColumn(new Panel(
                        new Edit() . ' ' . $dayNames[$DayNumber] . ' bearbeiten',
                        new Title(
                            new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(new Bold('UE'), 1),
                                new LayoutColumn(new Bold('Fach'), 3),
                                new LayoutColumn(new Bold('Lehrer'), 3),
                                new LayoutColumn(new Bold('Raum'), 2),
                                new LayoutColumn(new Bold('Woche'), 2),
                                new LayoutColumn(new Bold(''), 1),
                            ))))
                        )
                        . ApiTimetable::receiverBlock($this->getTimeTableDayForm($tblTimetable, $tblDivisionCourse, $DayNumber), 'TimetableForm'),
                        Panel::PANEL_TYPE_PRIMARY
                    ))
                ))
            )))

        );

        return $stage;
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $DayNumber
     * @param null $AddKey
     * @param null $SubKey
     * @param null $Data
     *
     * @return Form
     */
    public function getTimeTableDayForm(
        TblTimetable $tblTimetable, TblDivisionCourse $tblDivisionCourse, $DayNumber, $AddKey = null, $SubKey = null, $Data = null
    ): Form {
        $maxLesson = 12;
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }

        $tblSubjectList = Subject::useService()->getSubjectAll();
        $tblTeacherList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
            && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
                    && ($acronym = $tblTeacher->getAcronym())
                ) {

                } else {
                    $acronym = '-NA-';
                }

                $tblTeacherList[$tblPerson->getId()] = $acronym . ' - ' . $tblPerson->getFullName();
            }
        }

        if ($AddKey) {
            $Data[$AddKey] = array();
        }
        if ($SubKey) {
            unset($Data[$SubKey]);
            if ($SubKey % 100 == 0) {
                $Data[$SubKey] = array();
            }
        }

        if ($Data == null) {
            for ($i = $minLesson; $i <= $maxLesson; $i++) {
                $key = $i * 100;

                if (($tblTimetableNodeList = Timetable::useService()->getTimetableNodeListByTimetableAndDivisionCourseAndDay(
                    $tblTimetable, $tblDivisionCourse, $DayNumber, $i
                ))) {
                    foreach ($tblTimetableNodeList as $tblTimetableNode) {
                        $Data[$key] = array(
                            'serviceTblSubject' => ($tblSubject = $tblTimetableNode->getServiceTblSubject()) ? $tblSubject->getId() : 0,
                            'serviceTblPerson' => ($tblPerson = $tblTimetableNode->getServiceTblPerson()) ? $tblPerson->getId() : 0,
                            'Room' => $tblTimetableNode->getRoom(),
                            'Week' => $tblTimetableNode->getWeek()
                        );
                        $key += 10;
                    }
                } else {
                    $Data[$key] = array();
                }
            }
        }

        ksort($Data);
        $formRows = array();
        if ($Data) {
            $global = $this->getGlobal();
            foreach ($Data as $index => $list) {
                $global->POST['Data'][$index]['serviceTblSubject'] = $list['serviceTblSubject'] ?? 0;
                $global->POST['Data'][$index]['serviceTblPerson'] = $list['serviceTblPerson'] ?? 0;
                $global->POST['Data'][$index]['Room'] = $list['Room'] ?? '';
                $global->POST['Data'][$index]['Week'] = $list['Week'] ?? '';
                $global->savePost();

                $formRows[] = new FormRow(array(
                    new FormColumn(
                        (new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold(intval($index / 100) . '. UE'))))))
                    , 1),
                    new FormColumn(
                        (new SelectBox('Data[' . $index . '][serviceTblSubject]', '', array('{{ DisplayName }}' => $tblSubjectList)))->setRequired()
                    , 3),
                    new FormColumn(
                        new SelectBox('Data[' . $index . '][serviceTblPerson]', '', $tblTeacherList)
                    , 3),
                    new FormColumn(
                        new TextField('Data[' . $index . '][Room]', 'Raum')
                    , 2),
                    new FormColumn(
                        new TextField('Data[' . $index . '][Week]', 'Woche')
                    , 2),
                    new FormColumn(
                        (new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            (new Standard('', ApiTimetable::getEndpoint(), new Plus()))
                                ->ajaxPipelineOnClick(ApiTimetable::pipelineLoadTimetableForm(
                                    $tblTimetable->getId(), $tblDivisionCourse->getId(), $DayNumber, $index + 1, null, $Data
                                ))
                            . (new Standard('', ApiTimetable::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(ApiTimetable::pipelineLoadTimetableForm(
                                    $tblTimetable->getId(), $tblDivisionCourse->getId(), $DayNumber, null, $index, $Data
                                ))
                        )))))
                    , 1),
                ));
            }
        }
        $formRows[] = new FormRow(new FormColumn(array(
            (new Primary('Speichern', ApiTimetable::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiTimetable::pipelineSaveTimetableForm($tblTimetable->getId(), $tblDivisionCourse->getId(), $DayNumber)),
            (new Standard('Abbrechen', '/Education/ClassRegister/Digital/Timetable/Show', new Remove(),
                array('TimetableId' => $tblTimetable->getId(), 'DivisionCourseId' => $tblDivisionCourse->getId())))
        )));

        return new Form(new FormGroup($formRows));
    }
}
