<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Digital\Frontend\FrontendTabs;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Home;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\AbstractLink;
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
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

class Frontend extends FrontendTabs
{
    /**
     * @return Stage
     */
    public function frontendSelectDivision(): Stage
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }

    /**
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision(bool $IsAllYears = false, $YearId = null): Stage
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Kurs auswählen');

        Digital::useService()->setHeaderButtonList($Stage, View::TEACHER, self::BASE_ROUTE);
        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Teacher', $IsAllYears, $YearId, false, true, $yearFilterList);

        $table = false;
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $dataList = array();
            $tblDivisionCourseList = array();
            $checkedDivisionCourseList = array();
            if ($yearFilterList) {
                foreach ($yearFilterList as $tblYear) {
                    // Klassenlehrer
                    if (($tempList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))) {
                        foreach ($tempList as $temp) {
                            if (!isset($tblDivisionCourseList[$temp->getId()])
                                && $temp->getIsDivisionOrCoreGroup()
                            ) {
                                $tblDivisionCourseList[$temp->getId()] = $temp;
                                $checkedDivisionCourseList[$temp->getId()] = $temp;
                            }
                        }
                    }

                    // Lehraufträge -> dann alle Schüler des Lehrauftrags -> alle Klassen und Stammgruppen der Schüler
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson))) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                                && !isset($tblDivisionCourseList[$tblDivisionCourse->getId()])
                                && !isset($checkedDivisionCourseList[$tblDivisionCourse->getId()])
                                && ($tblDivisionCourseListFromStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))
                            ) {
                                foreach ($tblDivisionCourseListFromStudents as $tblDivisionCourseStudent) {
                                    if ($tblDivisionCourseStudent->getIsDivisionOrCoreGroup()
                                        && !isset($tblDivisionCourseList[$tblDivisionCourseStudent->getId()])
                                    ) {
                                        $tblDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                        $checkedDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                $dataList[] = array(
                    'Year' => $tblDivisionCourse->getYearName(),
                    'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                    'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                    'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                    'Option' => new Standard(
                        '',
                        DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
                            ? self::BASE_ROUTE . '/SelectCourse'
                            : self::BASE_ROUTE . '/LessonContent',
                        new Select(),
                        array(
                            'DivisionCourseId' => $tblDivisionCourse->getId(),
                            'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                        ),
                        'Auswählen'
                    )
                );
            }

            if (empty($dataList)) {
                $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
            } else {
                $table = new TableData($dataList, null, array(
                    'Year' => 'Schuljahr',
                    'DivisionCourse' => 'Kurs',
                    'DivisionCourseType' => 'Kurs-Typ',
                    'SchoolTypes' => 'Schularten',
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
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        $table
                            ? new LayoutColumn(array($table))
                            : null
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision(bool $IsAllYears = false, $YearId = null): Stage
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Kurs auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::HEADMASTER, self::BASE_ROUTE);

        $yearFilterList = array();
        // nur Schulleitung darf History (Alle Schuljahre) sehen
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Headmaster',
            $IsAllYears, $YearId, Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting'), true, $yearFilterList);

        $dataList = array();
        $tblDivisionCourseList = array();
        if ($IsAllYears) {
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = $tblDivisionCourseListDivision;
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }
        } elseif ($yearFilterList) {
            foreach ($yearFilterList as $tblYear) {
                if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                    $tblDivisionCourseList = $tblDivisionCourseListDivision;
                }
                if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                    TblDivisionCourseType::TYPE_CORE_GROUP))) {
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
                }
            }
        }

        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            $dataList[] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                'Option' => new Standard(
                    '',
                    DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
                        ? self::BASE_ROUTE . '/SelectCourse'
                        : self::BASE_ROUTE . '/LessonContent',
                    new Select(),
                    array(
                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                        'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
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

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn($table)
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendLessonContent(
        $DivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Klassentagebuch');
        $stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft()));

        // Klassenbuch Ansicht
        if ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)) {
            // View speichern
            Consumer::useService()->createAccountSetting('LessonContentView', 'Day');

            $stage->setContent(
                ApiDigital::receiverModal()
                . ApiAbsence::receiverModal()
                . new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/LessonContent', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        ApiDigital::receiverBlock($this->loadLessonContentTable($tblDivisionCourse), 'LessonContentContent')
                    )), new Title(new Book() . ' Klassentagebuch')),
                ))
            );
        } else {
            return new Danger('Kurs nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $DateString
     * @param string $View
     *
     * @return string
     */
    public function loadLessonContentTable(TblDivisionCourse $tblDivisionCourse, string $DateString = 'today', string $View = 'Day'): string
    {
        $DivisionCourseId = $tblDivisionCourse->getId();
        $Date = ($DateString == 'today' ? (new DateTime('today'))->format('d.m.Y') : $DateString);

        $buttons = (new Primary(
            new Plus() . ' Thema/Hausaufgaben hinzufügen',
            ApiDigital::getEndpoint()
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $Date));

        if ($View == 'Day') {
            $buttons .= (new Primary(
                new Plus() . ' Fehlzeit hinzufügen',
                ApiAbsence::getEndpoint()
            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal(null, $DivisionCourseId, $Date));

            $content = $this->getDayViewContent($DateString, $tblDivisionCourse);
            $link = (new Link('Wochenansicht', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionCourseId, $DateString, 'Week'));
        } else {
            $content =  $this->getWeekViewContent($DateString, $tblDivisionCourse);
            $link = (new Link('Tagesansicht', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionCourseId, $DateString, 'Day'));
        }

        $datePicker = (new DatePicker('Data[Date]', $Date, '', new Calendar()))
            ->setAutoFocus()
            ->ajaxPipelineOnChange(ApiDigital::pipelineLoadLessonContentContent($DivisionCourseId, $DateString, $View));
        $form = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new PullRight(
                    $datePicker
                )
                , 12),
//            new FormColumn(
//                new PullRight((new Primary('Datum auswählen', '', new Select()))->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
//                    $DivisionId, $GroupId, $DateString, $View
//                )))
//                , 5)
        )))))->disableSubmitAction();

        $layout = new Layout(new LayoutGroup(new LayoutRow(array(
//                new LayoutColumn($buttons, $View == 'Day' ? 7 : 8),
//                new LayoutColumn($form, $View == 'Day' ? 5 : 4)
//                new LayoutColumn($buttons, 8),
//                new LayoutColumn($form, 4)
                new LayoutColumn($buttons, 9),
                new LayoutColumn($form, 3)
            ))))
            . new Container('&nbsp;')
            . new Panel(
                new Book() . ' Klassenbuch' . new PullRight($link),
                $content,
                Panel::PANEL_TYPE_PRIMARY
            );

        if ($View == 'Day') {
            $layout = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($this->getStudentPanel($tblDivisionCourse), 2),
                new LayoutColumn($layout, 10),
            ))));
        }

        return $layout;
    }

    /**
     * @param string $DateString
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    private function getDayViewContent(
        string $DateString,
        TblDivisionCourse $tblDivisionCourse
    ): string {
        $DivisionCourseId = $tblDivisionCourse->getId();
        $tblCompanyList = $tblDivisionCourse->getCompanyListFromStudents();
        $tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents();

        $date = new DateTime($DateString);
        $dayAtWeek = $date->format('w');
        $addDays = 1;
        $subDays = 1;
        $hasSaturdayLessons = false;
        $hasTypeOption = false;
        /** @var TblType $tblSchoolType */
        foreach ($tblSchoolTypeList as $tblSchoolType) {
            if (Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType)) {
                $hasSaturdayLessons = true;
            }
            if ($tblSchoolType->isTechnical()) {
                $hasTypeOption = true;
            }
        }
        if ($hasSaturdayLessons) {
            // nur zwischen Wochentagen springen
            switch ($dayAtWeek) {
                case 1: $subDays = 2; break;
                case 6: $addDays = 2; break;
            }
        } else {
            // nur zwischen Wochentagen springen
            switch ($dayAtWeek) {
                case 0: $subDays = 2; break;
                case 1: $subDays = 3; break;
                case 5: $addDays = 3; break;
                case 6: $addDays = 2; break;
            }
        }
        $nextDate = new DateTime($DateString);
        $nextDate = $nextDate->add(new DateInterval('P'. $addDays . 'D'));
        $previewsDate = new DateTime($DateString);
        $previewsDate = $previewsDate->sub(new DateInterval('P' . $subDays . 'D'));
        $dayName = array(
            '0' => 'Sonntag',
            '1' => 'Montag',
            '2' => 'Dienstag',
            '3' => 'Mittwoch',
            '4' => 'Donnerstag',
            '5' => 'Freitag',
            '6' => 'Samstag',
        );

        // Ferien, Feiertage
        $isHoliday = false;
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            if ($tblCompanyList) {
                foreach ($tblCompanyList as $tblCompany) {
                    if (($isHoliday = Term::useService()->getHolidayByDay($tblYear, $date, $tblCompany))) {
                        break;
                    }
                }
            } else {
                $isHoliday = Term::useService()->getHolidayByDay($tblYear, $date, null);
            }

            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateSchoolYear && $endDateSchoolYear) {
                if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                    return new Warning('Das ausgewählte Datum: ' . $DateString . ' befindet sich außerhalb des Schuljahres.', new Exclamation());
                }
                if ($previewsDate < $startDateSchoolYear) {
                    $previewsDate = false;
                }
                if ($nextDate > $endDateSchoolYear) {
                    $nextDate = false;
                }
            } else {
                return new Warning('Das Schuljahr besitzt keinen Zeitraum', new Exclamation());
            }
        } else {
            return new Warning('Kein Schuljahr gefunden', new Exclamation());
        }
        // aktueller Tag
        $isCurrentDay = (new DateTime('today'))->format('d.m.Y') ==  $date->format('d.m.Y');

        $headerList['Lesson'] = $this->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), '30px');
        $headerList['Subject'] = $this->getTableHeadColumn('Fach', '80px');
        $headerList['Room'] = $this->getTableHeadColumn('Raum', '50px');
        $headerList['Teacher'] = $this->getTableHeadColumn('Lehrer', '50px');
        $headerList['Content'] = $this->getTableHeadColumn('Thema');
        $headerList['Homework'] = $this->getTableHeadColumn('Hausaufgaben');
        $headerList['Absence'] = $this->getTableHeadColumn('Fehlzeiten');

        $maxLesson = 12;
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }
        $bodyList = array();
        $bodyBackgroundList = array();
        $divisionCourseList[] = $tblDivisionCourse;
        $absenceContent = array();
        if (($AbsenceList = Absence::useService()->getAbsenceAllByDay($date, null, null, $divisionCourseList, $hasTypeOption, null))) {
            foreach ($AbsenceList as $Absence) {
                if (($tblAbsence = Absence::useService()->getAbsenceById($Absence['AbsenceId']))) {
                    $lesson = $tblAbsence->getLessonStringByAbsence();
                    $type = $tblAbsence->getTypeDisplayShortName();
                    $remark = $tblAbsence->getRemark();
                    $toolTip = ($lesson ? $lesson . ' / ' : '') . ($type ? $type . ' / ' : '') . $tblAbsence->getStatusDisplayShortName()
                        . (($tblPersonStaff = $tblAbsence->getDisplayStaffToolTip()) ? ' - ' . $tblPersonStaff : '')
                        . ($remark ? ' - ' . $remark : '');

                    $item = (new Link(
                        $Absence['Person'],
                        ApiAbsence::getEndpoint(),
                        null,
                        array(),
                        $toolTip,
                        null,
                        $tblAbsence->getLinkType()
                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(), $DivisionCourseId));

                    if (($tblAbsenceLessonList = Absence::useService()->getAbsenceLessonAllByAbsence($tblAbsence))) {
                        foreach ($tblAbsenceLessonList as $tblAbsenceLesson) {
                            if (!isset($absenceContent[$tblAbsenceLesson->getLesson()])) {
                                $absenceContent[$tblAbsenceLesson->getLesson()] = array('0' => $item);
                            } else {
                                $absenceContent[$tblAbsenceLesson->getLesson()][] = $item;
                            }
                        }
                    } else {
                        if (!isset($absenceContent['Day'])) {
                            $absenceContent['Day'] = array('0' => $item);
                        } else {
                            $absenceContent['Day'][] = $item;
                        }
                    }
                }
            }

            if (isset($absenceContent['Day'])) {
                $bodyList[-1] = array(
                    'Lesson' => new ToolTip(new Bold('GT'), 'Ganztägig'),
                    'Subject' => '',
                    'Room' => '',
                    'Teacher' => '',
                    'Content' => '',
                    'Homework' => '',
                    'Absence' => implode(' - ', $absenceContent['Day'])
                );
            }

            if (isset($absenceContent[0]) && $minLesson > 0) {
                $bodyList[0] = array(
                    'Lesson' => new ToolTip(new Center(new Bold('0')), '0. Unterrichtseinheit'),
                    'Subject' => '',
                    'Room' => '',
                    'Teacher' => '',
                    'Content' => '',
                    'Homework' => '',
                    'Absence' => implode(' - ', $absenceContent[0])
                );
            }
        }

        if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDate($date, $tblDivisionCourse))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
                $lesson = $tblLessonContent->getLesson();
                if ($lesson > $maxLesson) {
                    $maxLesson = $lesson;
                }
                // es können mehrere Einträge zur selben Unterrichtseinheit vorhanden sein
                $index = $lesson * 10;
                while (isset($bodyList[$index])) {
                    $index++;
                }

                $isEditAllowed = Digital::useService()->getIsLessonContentEditAllowed($tblLessonContent);
                $lessonContentId = $tblLessonContent->getId();
                $bodyList[$index] = array(
                    'Lesson' => $isEditAllowed ? $this->getLessonsEditLink(new Bold(new Center($lesson)), $lessonContentId, $lesson) : new Bold(new Center($lesson)),
                    'Subject' => $isEditAllowed ? $this->getLessonsEditLink($tblLessonContent->getDisplaySubject(true), $lessonContentId, $lesson) : $tblLessonContent->getDisplaySubject(true),
                    'Room' => $isEditAllowed ? $this->getLessonsEditLink($tblLessonContent->getRoom(), $lessonContentId, $lesson) : $tblLessonContent->getRoom(),
                    'Teacher' => $isEditAllowed ? $this->getLessonsEditLink($tblLessonContent->getTeacherString(), $lessonContentId, $lesson) : $tblLessonContent->getTeacherString(),
                    'Content' => $isEditAllowed ? $this->getLessonsEditLink($tblLessonContent->getContent(), $lessonContentId, $lesson) : $tblLessonContent->getContent(),
                    'Homework' => $isEditAllowed ?$this->getLessonsEditLink($tblLessonContent->getHomework(), $lessonContentId, $lesson) : $tblLessonContent->getHomework(),

                    'Absence' => isset($absenceContent[$lesson]) ? implode(' - ', $absenceContent[$lesson]) : ''
                );

                $bodyBackgroundList[$index] = true;
            }
        }

        // leere Einträge bis $maxLesson auffüllen
        for ($i = $minLesson; $i <= $maxLesson; $i++) {
            if (!isset($bodyList[$i * 10])) {
                $linkLesson = (new Link(
                    new Center($i),
                    ApiDigital::getEndpoint(),
                    null,
                    array(),
                    $i . '. Thema/Hausaufgaben hinzufügen',
                    null,
                    AbstractLink::TYPE_MUTED_LINK
                ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $date->format('d.m.Y'), $i == 0 ? -1 : $i));

                //  Fach aus dem importierten Stundenplan anzeigen
                if (!$isHoliday && ($tblLessonContentTemp = Timetable::useService()->getLessonContentFromTimeTableNodeWithReplacementBy(
                    $tblDivisionCourse, $date, $i
                ))) {
                    $subject = $tblLessonContentTemp->getDisplaySubject(true);
                    $room = $tblLessonContentTemp->getRoom();
                //  alternativ zum importierten Stundenplan wird nach vorherige Einträge gesucht
                } elseif (!$isHoliday && ($tblLessonContentTemp  = Digital::useService()->getTimetableFromLastLessonContent(
                    $tblDivisionCourse, $date, $i
                ))) {
                    $subject = $tblLessonContentTemp->getDisplaySubject(true);
                    $room = $tblLessonContentTemp->getRoom();
                } else {
                    $subject = '';
                    $room = '';
                }

                $bodyList[$i * 10] = array(
                    'Lesson' => $linkLesson,
                    'Subject' => $this->getLessonsNewLink($subject, $date, $i, $DivisionCourseId),
                    'Room' => $this->getLessonsNewLink($room, $date, $i, $DivisionCourseId),
                    'Teacher' => $this->getLessonsNewLink('', $date, $i, $DivisionCourseId),
                    'Content' => $this->getLessonsNewLink('', $date, $i, $DivisionCourseId),
                    'Homework' => $this->getLessonsNewLink('', $date, $i, $DivisionCourseId),

                    'Absence' => isset($absenceContent[$i]) ? implode(' - ', $absenceContent[$i]) : ''
                );
            }
        }
        ksort($bodyList);

        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $key => $columnList) {
//            $rows[] = new TableRow($columnList);

            $columns = array();
            $count = 0;
            foreach ($columnList as $column) {
                $columns[] = (new TableColumn($column))
                    ->setVerticalAlign('middle')
                    ->setMinHeight('30px')
                    ->setPadding('3')
                    ->setBackgroundColor($key == -1 || ($key == 0 && $minLesson > 0) || (isset($bodyBackgroundList[$key]) && $count == 0) ? '#E0F0FF' : '');
                $count++;
            }
            $rows[] = new TableRow($columns);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $dayText = new Bold($dayName[$dayAtWeek] . ', den ' . $date->format('d.m.Y'));
        if ($isHoliday) {
            $dayText = $this->getTextColor($dayText, 'lightgray');
        } elseif ($isCurrentDay) {
            $dayText = $this->getTextColor($dayText, 'darkorange');
        }

        $content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        $previewsDate
                                            ? (new Link(new ChevronLeft(), ApiDigital::getEndpoint(), null, array(),
                                                $dayName[$previewsDate->format('w')] . ', den ' . $previewsDate->format('d.m.Y')))
                                                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionCourseId, $previewsDate->format('d.m.Y')))
                                            : ''
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new Center($dayText)
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        $nextDate
                                            ? (new Link(new ChevronRight(), ApiDigital::getEndpoint(), null, array(),
                                                $dayName[$nextDate->format('w')] . ', den ' . $nextDate->format('d.m.Y')))
                                                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionCourseId, $nextDate->format('d.m.Y')))
                                            : ''
                                    )
                                    , 1),
                                new LayoutColumn('&nbsp;', 3),
                            )))
                        )
                        . '<div style="height: 5px;"></div>'
                        , 12)
                ),
                new LayoutRow(
                    new LayoutColumn(
                        $table
                    )
                )
            ))
        );

        return $content . Digital::useService()->getCanceledSubjectOverview($date, $tblDivisionCourse) . ' ';
    }

    /**
     * @param string $DateString
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $hasNavigation
     * @param bool $isReadOnly
     *
     * @return string
     */
    public function getWeekViewContent(
        string $DateString,
        TblDivisionCourse $tblDivisionCourse,
        bool $hasNavigation = true,
        bool $isReadOnly = false
    ): string {
        $DivisionCourseId = $tblDivisionCourse->getId();
        $date = new DateTime($DateString);
        $tblCompanyList = $tblDivisionCourse->getCompanyListFromStudents();
        $tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents();

        $hasSaturdayLessons = false;
        /** @var TblType $tblSchoolType */
        foreach ($tblSchoolTypeList as $tblSchoolType) {
            if (Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType)) {
                $hasSaturdayLessons = true;
                break;
            }
        }
        if ($hasSaturdayLessons) {
            $daysInWeek = 6;
            $widthLesson =  '4%';
            $widthDay = '16%';
        } else {
            $daysInWeek = 5;
            $widthLesson =  '5%';
            $widthDay = '19%';
        }

        $currentWeek =  (int) $date->format('W');

        $nextWeekDate = new DateTime($DateString);
        $nextWeekDate = $nextWeekDate->add(new DateInterval('P7D'));
        $nextWeek = $nextWeekDate->format('W');

        $previewsWeekDate = new DateTime($DateString);
        $previewsWeekDate = $previewsWeekDate->sub(new DateInterval('P7D'));
        $previewsWeek = $previewsWeekDate->format('W');

        $dayName = array(
            '0' => 'Sonntag',
            '1' => 'Montag',
            '2' => 'Dienstag',
            '3' => 'Mittwoch',
            '4' => 'Donnerstag',
            '5' => 'Freitag',
            '6' => 'Samstag',
        );

        $maxLesson = 12;
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }
        $headerList = array();
        $headerList['Lesson'] = $this->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), $widthLesson);
        $bodyList = array();
        $dateStringList = array();
        $holidayList = array();

        $year = $date->format('Y');
        $week = str_pad($currentWeek, 2, '0', STR_PAD_LEFT);
        $startDate  = new DateTime(date('d.m.Y', strtotime("$year-W{$week}")));

        // Prüfung ob das Datum innerhalb des Schuljahres liegt.
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateSchoolYear && $endDateSchoolYear) {
                if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                    return new Warning('Das ausgewählte Datum: ' . $DateString . ' befindet sich außerhalb des Schuljahres.', new Exclamation());
                }
                if ($previewsWeekDate < $startDateSchoolYear) {
                    $previewsWeekDate = false;
                }
                if ($nextWeekDate > $endDateSchoolYear) {
                    $nextWeekDate = false;
                }
            } else {
                return new Warning('Das Schuljahr besitzt keinen Zeitraum', new Exclamation());
            }
        } else {
            return new Warning('Kein Schuljahr gefunden', new Exclamation());
        }

        for ($day = 1; $day <= $daysInWeek; $day++) {
            // Ferien, Feiertage
            $isHoliday = false;
            if ($tblCompanyList) {
                foreach ($tblCompanyList as $tblCompany) {
                    if (($isHoliday = Term::useService()->getHolidayByDay($tblYear, $startDate, $tblCompany))) {
                        break;
                    }
                }
            } else {
                $isHoliday = Term::useService()->getHolidayByDay($tblYear, $startDate, null);
            }
            if ($isHoliday) {
                $holidayList[$day] = true;
            }

            // aktueller Tag
            $isCurrentDay = (new DateTime('today'))->format('d.m.Y') ==  $startDate->format('d.m.Y');

            $headerContent = $dayName[$day] . new Muted(', den ' . $startDate->format('d.m.Y'));
            $headerList[$day] = $this->getTableHeadColumn(
                $isCurrentDay ? $this->getTextColor($headerContent, 'darkorange') : $headerContent,
                $widthDay,
                $isHoliday ? 'lightgray' : '#E0F0FF'
            );
            $dateStringList[$day] = $startDate->format('d.m.Y');
            if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDate($startDate, $tblDivisionCourse))) {
                foreach ($tblLessonContentList as $tblLessonContent) {
                    $teacher = '';
                    if (($tblPerson = $tblLessonContent->getServiceTblPerson())) {
                        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
                            && ($acronym = $tblTeacher->getAcronym())
                        ) {
                            $teacher = $acronym;
                        } else {
                            if (strlen($tblPerson->getLastName()) > 5) {
                                $teacher = substr($tblPerson->getLastName(), 0, 5) . '.';
                            }
                        }
                    }

                    $lesson = $tblLessonContent->getLesson();
                    if ($lesson > $maxLesson) {
                        $maxLesson = $lesson;
                    }

                    $display = $tblLessonContent->getDisplaySubject(false)
                        . ($teacher ? ' (' . $teacher . ')' : '')
                        . ($tblLessonContent->getContent() ? new Container('Inhalt: ' . $tblLessonContent->getContent()) : '')
                        . ($tblLessonContent->getHomework() ? new Container('Hausaufgaben: ' . $tblLessonContent->getHomework()) : '');
                    if ($isReadOnly || !Digital::useService()->getIsLessonContentEditAllowed($tblLessonContent)) {
                        $item = $display;
                    } else {
                        $item = $this->getLessonsEditLink($display, $tblLessonContent->getId(), $lesson);
                    }

                    if (isset($bodyList[$lesson][$day])) {
                        $bodyList[$lesson][$day] .= new Container(new Center('--------------------')) . new Container($item);
                    } else {
                        $bodyList[$lesson][$day] = $item;
                    }
                }
            }

            $startDate->modify('+1  day');
        }

        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        for ($i = $minLesson; $i <= $maxLesson; $i++) {
            $columns = array();
            $columns[] = (new TableColumn(new Center($i)))
                ->setVerticalAlign('middle')
                ->setMinHeight('30px')
                ->setPadding('3');
            for ($j = 1; $j<= $daysInWeek; $j++ ) {
                $isHoliday = isset($holidayList[$j]);
                if (isset($bodyList[$i][$j])) {
                    $cell = $bodyList[$i][$j];
                } elseif ($isHoliday) {
                    $cell = new Center(new Muted('f'));
                } elseif(!$isReadOnly) {
                    // Fach aus dem importierten Stundenplan anzeigen
                    if (($tblLessonContentTemp = Timetable::useService()->getLessonContentFromTimeTableNodeWithReplacementBy(
                        $tblDivisionCourse, new DateTime($dateStringList[$j]), $i
                    ))) {
                        $cellContent = $tblLessonContentTemp->getDisplaySubject(false);
                    // alternativ zum importierten Stundenplan wird nach vorherige Einträge gesucht
                    } elseif (($tblLessonContentTemp  = Digital::useService()->getTimetableFromLastLessonContent(
                        $tblDivisionCourse, new DateTime($dateStringList[$j]), $i
                    ))) {
                        $cellContent = $tblLessonContentTemp->getDisplaySubject(false);
                    } else {
                        $cellContent = '<div style="height: 22px"></div>';
                    }

                    $cell = (new Link(
                        $cellContent,
                        ApiDigital::getEndpoint(),
                        null,
                        array(),
                        $i . '. Thema/Hausaufgaben hinzufügen'
                    ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $dateStringList[$j], $i == 0 ? -1 : $i));
                } else {
                    $cell = '&nbsp;';
                }
                $columns[] = (new TableColumn($cell))
                    ->setBackgroundColor($isHoliday ? 'lightgray' : '')
                    ->setOpacity($isHoliday ? '0.5' : '1.0')
                    ->setVerticalAlign('middle')
                    ->setMinHeight('30px')
                    ->setPadding('3');
            }
            $rows[] = new TableRow($columns);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('&nbsp;', 3),
                            new LayoutColumn(
                                new Center(
                                    $previewsWeekDate && $hasNavigation
                                        ? (new Link(new ChevronLeft(), ApiDigital::getEndpoint(), null, array(), 'KW' . $previewsWeek))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionCourseId, $previewsWeekDate->format('d.m.Y'), 'Week'))
                                        : ''
                                )
                                , 1),
                            new LayoutColumn(
                                new Center(new Bold('KW' . $currentWeek. ' '))
                                , 4),
                            new LayoutColumn(
                                new Center(
                                    $nextWeekDate && $hasNavigation
                                        ? (new Link(new ChevronRight(), ApiDigital::getEndpoint(), null, array(), 'KW' . $nextWeek))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionCourseId, $nextWeekDate->format('d.m.Y'), 'Week'))
                                        : ''
                                )
                                , 1),
                            new LayoutColumn('&nbsp;', 3),
                        ))))
                        . '<div style="height: 5px;"></div>'
                        , 12)
                ),
                new LayoutRow(
                    new LayoutColumn(
                        $table
                    )
                )
            ))
        );

        return $content . Digital::useService()->getCanceledSubjectOverview($date, $tblDivisionCourse, !$isReadOnly) . ' ';
    }

    /**
     * @param string $name
     * @param int $LessonContentId
     * @param int $Lesson
     *
     * @return Link
     */
    private function getLessonsEditLink(string $name, int $LessonContentId, int $Lesson): Link
    {
        return (new Link(
            $name  == '' ? '<div style="height: 22px"></div>' : $name,
            ApiDigital::getEndpoint(),
            null,
            array(),
            $Lesson . '. Thema/Hausaufgaben bearbeiten'
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditLessonContentModal($LessonContentId));
    }

    /**
     * @param string $name
     * @param DateTime $date
     * @param int $Lesson
     * @param int $DivisionCourseId
     *
     * @return Link
     */
    private function getLessonsNewLink(string $name, DateTime $date, int $Lesson, int $DivisionCourseId): Link
    {
        return (new Link(
            $name ?: '<div style="height: 22px"></div>',
            ApiDigital::getEndpoint(),
            null,
            array(),
            $Lesson . '. Thema/Hausaufgaben hinzufügen'
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $date->format('d.m.Y'), $Lesson == 0 ? -1 : $Lesson));
    }

    /**
     * @param string $name
     * @param string $width
     * @param string $backgroundColor
     *
     * @return TableColumn
     */
    private function getTableHeadColumn(string $name, string $width = 'auto', string $backgroundColor = '#E0F0FF'): TableColumn
    {
        $size = 1;
        return (new TableColumn(new Center(new Bold($name)), $size, $width))
            ->setBackgroundColor($backgroundColor)
            ->setOpacity($backgroundColor == 'lightgray' ? '0.5' : '1.0')
            ->setVerticalAlign('middle')
            ->setMinHeight('35px');
    }

    /**
     * @param string $content
     * @param string $color
     *
     * @return string
     */
    private function getTextColor(string $content, string $color): string
    {
        return '<span style="color: ' . $color . ';">' . $content . '</span>';
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param null $LessonContentId
     * @param bool $setPost
     * @param string|null $Date
     * @param string|null $Lesson
     *
     * @return Form
     */
    public function formLessonContent(TblDivisionCourse $tblDivisionCourse, $LessonContentId = null, bool $setPost = false, string $Date = null, string $Lesson = null): Form
    {
        $tblSubject = false;
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $LessonContentId
            && ($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $tblLessonContent->getDate();
            $Global->POST['Data']['Lesson'] = $tblLessonContent->getLesson() === 0 ? -1 : $tblLessonContent->getLesson();
            $Global->POST['Data']['serviceTblSubject'] = ($tblSubject = $tblLessonContent->getServiceTblSubject()) ? $tblSubject->getId() : 0;
            $Global->POST['Data']['serviceTblSubstituteSubject'] =
                ($tblSubstituteSubject = $tblLessonContent->getServiceTblSubstituteSubject()) ? $tblSubstituteSubject->getId() : 0;
            $Global->POST['Data']['IsCanceled'] = $tblLessonContent->getIsCanceled();
            $Global->POST['Data']['serviceTblPerson'] = ($tblPerson = $tblLessonContent->getServiceTblPerson()) ? $tblPerson->getId() : 0;
            $Global->POST['Data']['Content'] = $tblLessonContent->getContent(false);
            $Global->POST['Data']['Homework'] = $tblLessonContent->getHomework();
            $Global->POST['Data']['Room'] = $tblLessonContent->getRoom();

            $Global->savePost();
        } elseif ($Date || $Lesson) {
            // hinzufügen mit Startwerten
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $Date;
            $Global->POST['Data']['Lesson'] = $Lesson;
            $Global->savePost();
        }

        if ($LessonContentId) {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineEditLessonContentSave($LessonContentId));
        } else {
            // befüllen bei neuen Einträge aus dem importierten Stundenplan
            if ($Date && $Lesson
                && ($tblLessonContentTemp = Timetable::useService()->getLessonContentFromTimeTableNodeWithReplacementBy(
                    $tblDivisionCourse, new DateTime($Date), (int) $Lesson
                ))
            ) {
                $Global = $this->getGlobal();

                $Global->POST['Data']['serviceTblSubject'] = ($tblSubject = $tblLessonContentTemp->getServiceTblSubject()) ? $tblSubject->getId() : 0;
                $Global->POST['Data']['serviceTblSubstituteSubject'] =
                    $tblLessonContentTemp->getServiceTblSubstituteSubject() ? $tblLessonContentTemp->getServiceTblSubstituteSubject()->getId() : 0;
                $Global->POST['Data']['Room'] = $tblLessonContentTemp->getRoom();
                $Global->POST['Data']['IsCanceled'] = $tblLessonContentTemp->getIsCanceled() ? 1 : 0;

                $Global->savePost();
            // alternativ zum importierten Stundenplan wird nach vorherige Einträge gesucht
            } elseif (($tblLessonContentTemp  = Digital::useService()->getTimetableFromLastLessonContent(
                $tblDivisionCourse, new DateTime($Date), (int) $Lesson
            ))) {
                $Global = $this->getGlobal();

                $Global->POST['Data']['serviceTblSubject'] = ($tblSubject = $tblLessonContentTemp->getServiceTblSubject()) ? $tblSubject->getId() : 0;
                $Global->POST['Data']['Room'] = $tblLessonContentTemp->getRoom();

                $Global->savePost();
            }

            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateLessonContentSave($tblDivisionCourse->getId()));
        }
        $buttonList[] = $saveButton;

//        $tblTeacherList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        $tblSubjectList = Subject::useService()->getSubjectAll();

        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }
        for ($i = 0; $i < 13; $i++) {
            $lessons[] = new SelectBoxItem($i, $i . '. Unterrichtseinheit');
        }
        if ($minLesson == 0) {
            $lessons[] = new SelectBoxItem(-1, '0. Unterrichtseinheit');
        }

        // Unterrichteinheit löchen
        if ($LessonContentId) {
            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger(
                'Löschen',
                ApiDigital::getEndpoint(),
                new Remove(),
                array(),
                false
            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteLessonContentModal($LessonContentId));
        }

        $formRowList[] = new FormRow(array(
            new FormColumn(
                (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                , 6),
            new FormColumn(
                (new SelectBox('Data[Lesson]', 'Unterrichtseinheit', array('{{ Name }}' => $lessons)))->setRequired()
                , 6),
        ));
        $formRowList[] = new FormRow(array(
            new FormColumn(
                (new SelectBox('Data[serviceTblSubject]', 'Fach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)))
                    ->ajaxPipelineOnChange(ApiDigital::pipelineLoadLessonContentLinkPanel($tblDivisionCourse->getId(), $tblSubject ? $tblSubject->getId() : null
                    ))
                , 6),
            new FormColumn(
                new SelectBox('Data[serviceTblSubstituteSubject]', 'Vertretungsfach / zusätzliches Fach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList))
                , 6),
//                    new FormColumn(
//                        new SelectBox('Data[serviceTblPerson]', 'Lehrer', array('{{ FullName }}' => $tblTeacherList))
//                        , 6),
        ));
        $formRowList[] = new FormRow(array(
            new FormColumn(
                new CheckBox('Data[IsCanceled]', 'Fach ist ausgefallen', 1)
            ),
        ));
        // nur beim neu anlegen kann Doppelstunde gecheckt werden
        if (!$LessonContentId) {
            $formRowList[] = new FormRow(array(
                new FormColumn(
                    new CheckBox('Data[IsDoubleLesson]', 'Doppelstunde ' . new ToolTip(new Info(),
                            'Beim Speichern werden die Daten auch für die nächste Unterrichtseinheit gespeichert.'), 1)
                ),
            ));
        }
        $formRowList[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Content]', 'Thema', 'Thema', new Edit())
            ),
        ));
        $formRowList[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Homework]', 'Hausaufgaben', 'Hausaufgaben', new Home())
            ),
        ));
        $formRowList[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Room]', 'Raum', 'Raum', new MapMarker())
            ),
        ));
        if (!$LessonContentId) {
            $formRowList[] = new FormRow(array(
                new FormColumn(
                    ApiDigital::receiverBlock(
                        $tblSubject ? Digital::useService()->getLessonContentLinkPanel($tblDivisionCourse, $tblSubject) : '',
                        'LessonContentLinkPanel'
                    )
                )
            ));
        }
        $formRowList[] = new FormRow(array(
            new FormColumn(
                $buttonList
            )
        ));

        return (new Form(new FormGroup(
             $formRowList
        )))->disableSubmitAction();
    }
}