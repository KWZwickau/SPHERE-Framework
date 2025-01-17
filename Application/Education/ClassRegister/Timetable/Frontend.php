<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable;

use DateInterval;
use SPHERE\Application\Api\Education\ClassRegister\ApiTimetable;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
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
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
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
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Sup;
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
            . new Warning(
                'Ablauf Stundenplan Eingabe:'
                . new Container('1. Die Schulleitung legt einen neuen Stundenplan an')
                . new Container('2. Die Schulleitung legt fest wann A-Woche bzw. B-Woche ist (falls erforderlich)')
                . new Container('3. SekI: Klassenlehrer/Tutoren können den Basis-Stundenplan für Ihre Klassen/Stammgruppen eingeben')
                . new Container('&nbsp;&nbsp;&nbsp;&nbsp;SekII: Fachlehrer (Lehrauftrag) können den Basis-Stundenplan für Ihre SekII-Kurse eingeben')
            )
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
                            ? (new Standard('', '/Education/ClassRegister/Digital/Timetable/Week', new Calendar(), array('TimetableId' => $tblTimetable->getId()),
                                'Wochen des Stundenplans bearbeiten'))
                                . (new Standard('', ApiTimetable::getEndpoint(), new Edit(), array(), 'Grunddaten des Stundenplans bearbeiten'))
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
                'order' => array(
                    array('2', 'desc'),
                ),
                'columnDefs' => array(
                    array('width' => '140px', "targets" => -1),
                    array('type' => 'de_date', 'targets' => array(2, 3)),
                ),
            )
        );
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

        $formRows[] = new FormRow(array(
            new FormColumn(
                (new TextField('Data[Name]', '', 'Name'))->setRequired()
                , 4),
            new FormColumn(array(
                (new DatePicker('Data[DateFrom]', '', 'Gültig ab', new Clock()))->setRequired()
            ), 4),
            new FormColumn(array(
                (new DatePicker('Data[DateTo]', '', 'Gültig bis', new Clock()))->setRequired()
            ), 4),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Description]', '', 'Beschreibung')
            )
        ));
        if ($TimeTableId == null && ($tblTimeTableList = Timetable::useService()->getTimetableAll())) {
            $timeTableList[] = '';
            foreach ($tblTimeTableList as $tblTemp) {
                $timeTableList[] = $tblTemp->getName() . ' (' . $tblTemp->getDateFrom() . ' - ' . $tblTemp->getDateTo() . ')';
            }
            $formRows[] = new FormRow(array(
                new FormColumn(
                    new SelectBox('Data[CopyTimeTable]', 'Alle Einträge aus einem alten Stundenplan in neuen Stundenplan kopieren', $timeTableList)
                )
            ));
        }
        $formRows[] = new FormRow(array(
            new FormColumn(
                $saveButton
            )
        ));

        return (new Form(new FormGroup($formRows)))->disableSubmitAction();
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
                        if (($tblDivisionCourseListAdvancedCourse = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                            TblDivisionCourseType::TYPE_ADVANCED_COURSE))) {
                            $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListAdvancedCourse);
                        }
                        if (($tblDivisionCourseListBasicCourse = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                            TblDivisionCourseType::TYPE_BASIC_COURSE))) {
                            $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListBasicCourse);
                        }
                        // Klassenlehrer oder SekII-Kurse mit Lehrauftrag
                    } elseif ($tblPerson) {
                        // Klassenlehrer
                        if (($tblTempList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))) {
                            foreach ($tblTempList as $tblTemp) {
                                if ($tblTemp->getIsDivisionOrCoreGroup()) {
                                    $tblDivisionCourseList[] = $tblTemp;
                                }

                            }
                        }
                        // SekII-Kurse -> Fachlehrer
                        if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson))) {
                            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                                if (($tblDivisionCourseTemp = $tblTeacherLectureship->getTblDivisionCourse())
                                    && $tblDivisionCourseTemp->getType()->getIsCourseSystem()
                                ) {
                                    $tblDivisionCourseList[] = $tblDivisionCourseTemp;
                                }
                            }
                        }
                    }
                }
            }

            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                // Klassen und Stammgruppen der SekII überspringen
                if ($tblDivisionCourse->getIsDivisionOrCoreGroup()
                    && DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
                ) {
                    continue;
                }

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
                        $tblDivisionCourse->getIsDivisionOrCoreGroup() ? '/Education/ClassRegister/Digital/Timetable/Show' : '/Education/ClassRegister/Digital/Timetable/Edit',
                        new Select(),
                        array(
                            'TimetableId' => $tblTimetable->getId(),
                            'DivisionCourseId' => $tblDivisionCourse->getId(),
                        ),
                        'Auswählen'
                    )
                );
            }

            if (!$hasRightHeadmaster && empty($dataList)) {
                $content = new Warning('Es können nur Klassenlehrer/Tutoren und Fachlehrer von SekII-Kursen den Stundenplan einsehen und bearbeiten.' , new Exclamation());
            } else {
                $content = new TableData($dataList, null, array(
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
            }

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
                            $content
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
                            . (($tblSubject = $tblTimetableNode->getServiceTblSubject()) ? $tblSubject->getAcronym() : '')
                            . ($tblTimetableNode->getWeek() ? ' (' . $tblTimetableNode->getWeek() . ')' : '');
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

        // SekI
        if ($tblDivisionCourse->getIsDivisionOrCoreGroup()) {
            $stage->addButton((new Standard(
                'Zurück', '/Education/ClassRegister/Digital/Timetable/Show', new ChevronLeft(),
                array('TimetableId' => $TimetableId, 'DivisionCourseId' => $DivisionCourseId))
            ));

            $contentCoursePanel = $tblDivisionCourse->getDisplayName();

            $dayNames = array(
                '0' => 'Sonntag',
                '1' => 'Montag',
                '2' => 'Dienstag',
                '3' => 'Mittwoch',
                '4' => 'Donnerstag',
                '5' => 'Freitag',
                '6' => 'Samstag',
            );
            $content = new Panel(
                new Edit() . ' ' . $dayNames[$DayNumber] . ' bearbeiten',
                new Title(
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(new Bold('UE'), 1),
                        new LayoutColumn(new Bold('Fach') . new Sup(new \SPHERE\Common\Frontend\Text\Repository\Danger('*')), 3),
                        new LayoutColumn(new Bold('Lehrer'), 3),
                        new LayoutColumn(new Bold('Raum'), 2),
                        new LayoutColumn(new Bold('Woche'), 2),
                        new LayoutColumn(new Bold(''), 1),
                    ))))
                )
                . ApiTimetable::receiverBlock($this->getTimeTableDayForm($tblTimetable, $tblDivisionCourse, $DayNumber), 'TimetableForm'),
                Panel::PANEL_TYPE_PRIMARY
            );
        // SekII
        } else {
            $stage->addButton((new Standard(
                'Zurück', '/Education/ClassRegister/Digital/Timetable/Select', new ChevronLeft(),
                array('TimetableId' => $TimetableId))
            ));

            $contentCoursePanel[] = $tblDivisionCourse->getDisplayName();
            $contentCoursePanel[] = ($tblSubject = $tblDivisionCourse->getServiceTblSubject()) ? $tblSubject->getDisplayName() : '';

            $content = new Title(new Well(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(new Bold('Tag') . new Sup(new \SPHERE\Common\Frontend\Text\Repository\Danger('*')), 2),
                    new LayoutColumn(new Bold('UE') . new Sup(new \SPHERE\Common\Frontend\Text\Repository\Danger('*')), 2),
                    new LayoutColumn(new Bold('Lehrer'), 3),
                    new LayoutColumn(new Bold('Raum'), 2),
                    new LayoutColumn(new Bold('Woche'), 2),
                    new LayoutColumn(new Bold(''), 1),
                ))))
                . ApiTimetable::receiverBlock($this->getTimeTableCourseSystemForm($tblTimetable, $tblDivisionCourse), 'TimetableCourseSystemForm')
            ));
        }

        $stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel($tblDivisionCourse->getTypeName(), $contentCoursePanel, Panel::PANEL_TYPE_INFO)
                        , 6),
                    new LayoutColumn(
                        new Panel('Stundenplan', $array, Panel::PANEL_TYPE_INFO)
                        , 6)
                )),
                new LayoutRow(array(
                    new LayoutColumn($content)
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
        $tblTeacherList[] = '';
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
        $weekNameList = array();
        if (($tblTimetableWeekList = Timetable::useService()->getTimetableWeekListByTimetable($tblTimetable))) {
            foreach ($tblTimetableWeekList as $tblTimetableWeek) {
                $weekNameList[$tblTimetableWeek->getWeek()] = new SelectBoxItem($tblTimetableWeek->getWeek(), $tblTimetableWeek->getWeek());
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
                        new AutoCompleter('Data[' . $index . '][Week]', '', 'Woche', array('Name' => $weekNameList))
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

    /**
     * @param TblTimetable $tblTimetable
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $AddKey
     * @param $SubKey
     * @param $Data
     *
     * @return Form
     */
    public function getTimeTableCourseSystemForm(
        TblTimetable $tblTimetable, TblDivisionCourse $tblDivisionCourse, $AddKey = null, $SubKey = null, $Data = null
    ): Form {
        $maxLesson = 12;
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }

        $tblTeacherList[] = '';
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
        $weekNameList = array();
        if (($tblTimetableWeekList = Timetable::useService()->getTimetableWeekListByTimetable($tblTimetable))) {
            foreach ($tblTimetableWeekList as $tblTimetableWeek) {
                $weekNameList[$tblTimetableWeek->getWeek()] = $tblTimetableWeek->getWeek();
            }
        }
        $lessonList = array();
        for ($i = $minLesson; $i <= $maxLesson; $i++) {
            $lessonList[$i] = $i . '.UE';
        }
        $dayNameList = array();
        $dayNameList['1'] = 'Montag';
        $dayNameList['2'] = 'Dienstag';
        $dayNameList['3'] = 'Mittwoch';
        $dayNameList['4'] = 'Donnerstag';
        $dayNameList['5'] = 'Freitag';
        $dayNameList['6'] = 'Samstag';

        if ($AddKey) {
            $Data[$AddKey] = array();
        }
        if ($SubKey) {
            unset($Data[$SubKey]);
            if ($SubKey % 10 == 0) {
                $Data[$SubKey] = array();
            }
        }

        if ($Data == null) {
            $i = 1;
            if (($tblTimetableNodeList = Timetable::useService()->getTimetableNodeListByTimetableAndDivisionCourse($tblTimetable, $tblDivisionCourse))) {
                $key = $i * 10;
                foreach ($tblTimetableNodeList as $tblTimetableNode) {
                    $Data[$key] = array(
                        'Day' => $tblTimetableNode->getDay(),
                        'Hour' => $tblTimetableNode->getHour(),
                        'serviceTblPerson' => ($tblPerson = $tblTimetableNode->getServiceTblPerson()) ? $tblPerson->getId() : 0,
                        'Room' => $tblTimetableNode->getRoom(),
                        'Week' => $tblTimetableNode->getWeek()
                    );
                    $key += 10;
                    $i++;
                }
            }

            for (; $i <= 10; $i++) {
                $key = $i * 10;
                $Data[$key] = array();
            }
        }

        ksort($Data);

        $formRows = array();
        if ($Data) {
            $global = $this->getGlobal();
            foreach ($Data as $index => $list) {
                $global->POST['Data'][$index]['Day'] = $list['Day'] ?? 0;
                $global->POST['Data'][$index]['Hour'] = $list['Hour'] ?? 0;
                $global->POST['Data'][$index]['serviceTblPerson'] = $list['serviceTblPerson'] ?? 0;
                $global->POST['Data'][$index]['Room'] = $list['Room'] ?? '';
                $global->POST['Data'][$index]['Week'] = $list['Week'] ?? '';
                $global->savePost();

                $formRows[] = new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[' . $index . '][Day]', '', $dayNameList, null, false, null))
                        , 2),
                    new FormColumn(
                        new SelectBox('Data[' . $index . '][Hour]', '', $lessonList)
                        , 2),
                    new FormColumn(
                        new SelectBox('Data[' . $index . '][serviceTblPerson]', '', $tblTeacherList)
                        , 3),
                    new FormColumn(
                        new TextField('Data[' . $index . '][Room]', 'Raum')
                        , 2),
                    new FormColumn(
                        new AutoCompleter('Data[' . $index . '][Week]', '', 'Woche', $weekNameList)
                        , 2),
                    new FormColumn(
                        (new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            (new Standard('', ApiTimetable::getEndpoint(), new Plus()))
                                ->ajaxPipelineOnClick(ApiTimetable::pipelineLoadTimetableCourseSystemForm(
                                    $tblTimetable->getId(), $tblDivisionCourse->getId(), $index + 1, null, $Data
                                ))
                            . (new Standard('', ApiTimetable::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(ApiTimetable::pipelineLoadTimetableCourseSystemForm(
                                    $tblTimetable->getId(), $tblDivisionCourse->getId(), null, $index, $Data
                                ))
                        )))))
                        , 1),
                ));
            }
        }
        $formRows[] = new FormRow(new FormColumn(array(
            (new Primary('Speichern', ApiTimetable::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiTimetable::pipelineSaveTimetableCourseSystemForm($tblTimetable->getId(), $tblDivisionCourse->getId())),
            (new Standard('Abbrechen', '/Education/ClassRegister/Digital/Timetable/Select', new Remove(),
                array('TimetableId' => $tblTimetable->getId())))
        )));

        return new Form(new FormGroup($formRows));
    }

    /**
     * @param null $TimetableId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendTimetableWeek($TimetableId = null, $Data = null)
    {
        $stage = new Stage('Stundenplan', 'Wochen bearbeiten');
        $stage->addButton((new Standard('Zurück', '/Education/ClassRegister/Digital/Timetable', new ChevronLeft())));

        if (($tblTimetable = Timetable::useService()->getTimetableById($TimetableId))) {
            if ($Data == null
                && ($tblTimetableWeekList = Timetable::useService()->getTimetableWeekListByTimetable($tblTimetable))
            ) {
                $global = $this->getGlobal();
                foreach ($tblTimetableWeekList as $tblTimetableWeek) {
                    $global->POST['Data'][$tblTimetableWeek->getDate()] = $tblTimetableWeek->getWeek();
                }

                $global->savePost();
            }

            $array[] = $tblTimetable->getName();
            if ($tblTimetable->getDescription()) {
                $array[] = $tblTimetable->getDescription();
                $array[] = 'Gültigkeit: ' . $tblTimetable->getDateFrom() . ' - ' . $tblTimetable->getDateTo();
            }

            $columns = array();
            if (($fromDateTime = new \DateTime($tblTimetable->getDateFrom())) && ($toDateTime = $tblTimetable->getDateTo(true))
                && $toDateTime > $fromDateTime
            ) {
                while ($fromDateTime <= $toDateTime) {
                    // montag
                    $startDate = Timetable::useService()->getStartDateOfWeek($fromDateTime);
                    $columns[] = new FormColumn(
                       new Layout(new LayoutGroup(new LayoutRow(array(
                           new LayoutColumn($startDate->format('d.m.Y'), 4),
                           new LayoutColumn(new TextField('Data[' . $startDate->format('d.m.Y') . ']'), 8),
                       ))))
                    , 3);

                    $fromDateTime->add(new DateInterval('P7D'));
                }
            }

            $form = new Form(new FormGroup(array(
                new FormRow($columns),
                new FormRow(array(
                    new FormColumn(array(
                        new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()),
                        new Standard('Abbrechen', '/Education/ClassRegister/Digital/Timetable', new Remove())
                    ))
                ))
            )));

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
                        new LayoutColumn(new Well(
                            Timetable::useService()->updateTimetableWeek($form, $tblTimetable, $Data)
                        ))
                    )),
                )))
            );

            return $stage;
        } else {
            return $stage . (new Danger('Der Stundenplan wurde nicht gefunden', new Exclamation()));
        }
    }
}