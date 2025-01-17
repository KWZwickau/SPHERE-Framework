<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Frontend\FrontendTabs;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblFullTimeContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Teacher\Teacher;
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

        $buttons .= (new Primary(
            new Plus() . ' Ganztägig hinzufügen',
            ApiDigital::getEndpoint()
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateFullTimeContentModal($DivisionCourseId, $Date));

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
        if ($tblSchoolTypeList) {
            /** @var TblType $tblSchoolType */
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if (Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType)) {
                    $hasSaturdayLessons = true;
                }
                if ($tblSchoolType->isTechnical()) {
                    $hasTypeOption = true;
                }
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

        // Ganztägig
        $fullTime = false;
        if (($tblFullTimeContentList = Digital::useService()->getFullTimeContentListByDivisionCourseAndDate($tblDivisionCourse, $date))) {
            /** @var TblFullTimeContent $tblFullTimeContent */
            $tblFullTimeContent = current($tblFullTimeContentList);
            $displayFullTimeContent = 'GT' . (($tempContent = $tblFullTimeContent->getContent()) ? ': ' . $tempContent : '');
            $fullTime = (new Link($displayFullTimeContent, ApiDigital::getEndpoint()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditFullTimeContentModal($tblFullTimeContent->getId()));
        }

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

        $isAutoTimeTable = ($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'IsAutoTimeTable'))
            && $tblSetting->getValue();

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

        $lessonContentList = array();
        if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDate($date, $tblDivisionCourse))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
                $lesson = $tblLessonContent->getLesson();
                if ($lesson > $maxLesson) {
                    $maxLesson = $lesson;
                }

                $lessonContentList[$lesson][$tblLessonContent->getId()] = $tblLessonContent;
            }
        }

        // leere Einträge bis $maxLesson auffüllen
        for ($i = $minLesson; $i <= $maxLesson; $i++) {
            $count = 0;
            $index = $i * 10;
            $subjectIdList = array();

            // Eintrag ist vorhanden
            if (isset($lessonContentList[$i])) {
                foreach ($lessonContentList[$i] as $tblLessonContentTemp) {
                    $SubjectId = ($tblSubjectTemp = $tblLessonContentTemp->getServiceTblSubject()) ? $tblSubjectTemp->getId() : null;
                    $subjectIdList[$SubjectId] = 1;
                    $index = $i * 10 + $count++;
                    $this->setDayViewEditBodyList($bodyList, $bodyBackgroundList, $absenceContent, $tblLessonContentTemp, $i, $index);
                }
                // weitere Einträge aus dem Stundenplan
                if (($tblLessonContentTempList = Timetable::useService()->getLessonContentListFromTimeTableNodeWithReplacementBy($tblDivisionCourse, $date, $i))) {
                    foreach ($tblLessonContentTempList as $tblLessonContentTemp) {
                        $index = $i * 10 + $count++;

                        $SubjectId = ($tblSubjectTemp = $tblLessonContentTemp->getServiceTblSubject()) ? $tblSubjectTemp->getId() : null;

                        if (!isset($subjectIdList[$SubjectId])) {
                            $subjectIdList[$SubjectId] = 1;

                            $this->setDayViewNewLinkBodyList($bodyList, $absenceContent, $i, $index, $DivisionCourseId, $date,
                                $tblLessonContentTemp->getDisplaySubject(true), $tblLessonContentTemp->getRoom(), $SubjectId);
                        }
                    }
                }
            // Ferien
            } elseif ($isHoliday) {
                $index = $i * 10;
                $this->setDayViewOnlyContentBodyList($bodyList, $absenceContent, $i, $index, new Center('f'));
            // ganztägig
            } elseif ($fullTime) {
                $this->setDayViewOnlyContentBodyList($bodyList, $absenceContent, $i, $index, $fullTime);
            //  Fach aus dem importierten Stundenplan anzeigen, mit mehreren Fächern gleichzeitig
            } elseif (($tblLessonContentTempList = Timetable::useService()->getLessonContentListFromTimeTableNodeWithReplacementBy($tblDivisionCourse, $date, $i))) {
                foreach ($tblLessonContentTempList as $tblLessonContentTemp) {
                    $index = $i * 10 + $count++;

                    $SubjectId = ($tblSubjectTemp = $tblLessonContentTemp->getServiceTblSubject()) ? $tblSubjectTemp->getId() : null;

                    if (!isset($subjectIdList[$SubjectId])) {
                        $subjectIdList[$SubjectId] = 1;

                        $this->setDayViewNewLinkBodyList($bodyList, $absenceContent, $i, $index, $DivisionCourseId, $date,
                            $tblLessonContentTemp->getDisplaySubject(true), $tblLessonContentTemp->getRoom(), $SubjectId);
                    }
                }
            //  alternativ zum importierten Stundenplan wird nach vorherige Einträge gesucht
            } elseif ($isAutoTimeTable
                && ($tblLessonContentTemp = Digital::useService()->getTimetableFromLastLessonContent(
                    $tblDivisionCourse, $date, $i
                ))
            ) {
                $this->setDayViewNewLinkBodyList($bodyList, $absenceContent, $i, $index, $DivisionCourseId, $date,
                    $tblLessonContentTemp->getDisplaySubject(true), $tblLessonContentTemp->getRoom());
            // neu links
            } else {
                $this->setDayViewNewLinkBodyList($bodyList, $absenceContent, $i, $index, $DivisionCourseId, $date, '', '');
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
                $backgroundColor = $key == -1 || ($key == 0 && $minLesson > 0) || (isset($bodyBackgroundList[$key]) && $count == 0) ? '#E0F0FF' : '';
                $columns[] = (new TableColumn($column))
                    ->setBackgroundColor($isHoliday ? 'lightgray' : $backgroundColor)
                    ->setOpacity($isHoliday ? '0.5' : '1.0')
                    ->setVerticalAlign('middle')
                    ->setMinHeight('30px')
                    ->setPadding('3');
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

    private function setDayViewEditBodyList(array &$bodyList, array &$bodyBackgroundList, array $absenceContent, TblLessonContent $tblLessonContent, int $lesson, int $index)
    {
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

    private function setDayViewNewLinkBodyList(array &$bodyList, array $absenceContent, int $lesson, int $index, int $DivisionCourseId, DateTime $date,
        string $subject, string $room, int $SubjectId = null)
    {
        $linkLesson = (new Link(
            new Center($lesson),
            ApiDigital::getEndpoint(),
            null,
            array(),
            $lesson . '. Thema/Hausaufgaben hinzufügen',
            null,
            AbstractLink::TYPE_MUTED_LINK
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal(
            $DivisionCourseId, $date->format('d.m.Y'), $lesson == 0 ? -1 : $lesson, $SubjectId
        ));

        $bodyList[$index] = array(
            'Lesson' => $linkLesson,
            'Subject' => $this->getLessonsNewLink($subject, $date, $lesson, $DivisionCourseId, $SubjectId),
            'Room' => $this->getLessonsNewLink($room, $date, $lesson, $DivisionCourseId, $SubjectId),
            'Teacher' => $this->getLessonsNewLink('', $date, $lesson, $DivisionCourseId, $SubjectId),
            'Content' => $this->getLessonsNewLink('', $date, $lesson, $DivisionCourseId, $SubjectId),
            'Homework' => $this->getLessonsNewLink('', $date, $lesson, $DivisionCourseId, $SubjectId),

            'Absence' => isset($absenceContent[$lesson]) ? implode(' - ', $absenceContent[$lesson]) : ''
        );
    }

    private function setDayViewOnlyContentBodyList(array &$bodyList, array $absenceContent, int $lesson, int $index, string $content)
    {
        $bodyList[$index] = array(
            'Lesson' => new Center($lesson),
            'Subject' => '',
            'Room' => '',
            'Teacher' => '',
            'Content' => $content,
            'Homework' => '',

            'Absence' => isset($absenceContent[$lesson]) ? implode(' - ', $absenceContent[$lesson]) : ''
        );
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

        if ($tblSchoolTypeList) {
            /** @var TblType $tblSchoolType */
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if (Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType)) {
                    $hasSaturdayLessons = true;
                    break;
                }
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

        $isAutoTimeTable = ($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'IsAutoTimeTable'))
            && $tblSetting->getValue();

        $headerList = array();
        $headerList['Lesson'] = $this->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), $widthLesson);
        $bodyList = array();
        $dateStringList = array();
        $holidayList = array();
        $fullTimeList = array();
        $subjectIdListByDayAndLesson = array();

//        $year = $date->format('Y');
//        $week = str_pad($currentWeek, 2, '0', STR_PAD_LEFT);
        $startDate  = new DateTime(date('d.m.Y', strtotime('monday this week', strtotime($DateString))));

        // Prüfung, ob das Datum innerhalb des Schuljahres liegt.
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

            // Ganztägig
            if (($tblFullTimeContentList = Digital::useService()->getFullTimeContentListByDivisionCourseAndDate($tblDivisionCourse, $startDate))) {
                /** @var TblFullTimeContent $tblFullTimeContent */
                $tblFullTimeContent = current($tblFullTimeContentList);
                $displayFullTimeContent = 'GT' . (($tempContent = $tblFullTimeContent->getContent()) ? ': ' . $tempContent : '');
                if ($isReadOnly) {
                    $fullTimeList[$day] = $displayFullTimeContent;
                } else {
                    $fullTimeList[$day] = (new Link($displayFullTimeContent, ApiDigital::getEndpoint()))
                        ->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditFullTimeContentModal($tblFullTimeContent->getId()));
                }
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
                                $teacher = mb_substr($tblPerson->getLastName(), 0, 5) . '.';
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

                    $SubjectId = ($tblSubject = $tblLessonContent->getServiceTblSubject()) ? $tblSubject->getId() : null;
                    $subjectIdListByDayAndLesson[$lesson][$day][$SubjectId] = 1;
                }
            }
            $startDate->modify('+1 day');
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
                $cell = '&nbsp;';
                $isHoliday = isset($holidayList[$j]);
                if (isset($bodyList[$i][$j])) {
                    $cell = $bodyList[$i][$j];
                    // Fach aus dem importierten Stundenplan anzeigen, auch mehrere Fächer gleichzeitig
                    if (!$isReadOnly
                        && ($tblLessonContentTempList = Timetable::useService()->getLessonContentListFromTimeTableNodeWithReplacementBy(
                            $tblDivisionCourse, new DateTime($dateStringList[$j]), $i
                        ))
                    ) {
                        foreach ($tblLessonContentTempList as $tblLessonContentTemp) {
                            $SubjectId = ($tblSubjectTemp = $tblLessonContentTemp->getServiceTblSubject()) ? $tblSubjectTemp->getId() : null;
                            if (!isset($subjectIdListByDayAndLesson[$i][$j][$SubjectId])) {
                                $subjectIdListByDayAndLesson[$i][$j][$SubjectId] = 1;

                                $cellContent = $tblLessonContentTemp->getDisplaySubject(false);

                                if ($cell) {
                                    $cell .= new Container(new Center('--------------------'));
                                }
                                $cell .= (new Link(
                                    $cellContent,
                                    ApiDigital::getEndpoint(),
                                    null,
                                    array(),
                                    $i . '. Thema/Hausaufgaben hinzufügen'
                                ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $dateStringList[$j], $i == 0 ? -1 : $i, $SubjectId));
                            }
                        }
                    }
                } elseif ($isHoliday) {
                    $cell = new Center(new Muted('f'));
                } elseif (isset($fullTimeList[$j])) {
                    $cell = new Center($fullTimeList[$j]);
                } elseif(!$isReadOnly) {
                    // Fach aus dem importierten Stundenplan anzeigen, auch mehrere Fächer gleichzeitig
                    if (($tblLessonContentTempList = Timetable::useService()->getLessonContentListFromTimeTableNodeWithReplacementBy(
                        $tblDivisionCourse, new DateTime($dateStringList[$j]), $i
                    ))) {
                        $cell = '';
                        $subjectIdList = array();

                        foreach ($tblLessonContentTempList as $tblLessonContentTemp) {
                            $SubjectId = ($tblSubjectTemp = $tblLessonContentTemp->getServiceTblSubject()) ? $tblSubjectTemp->getId() : null;
                            if (!isset($subjectIdList[$SubjectId])) {
                                $subjectIdList[$SubjectId] = 1;
                            }
                            $cellContent = $tblLessonContentTemp->getDisplaySubject(false);

                            if ($cell) {
                                $cell .= new Container(new Center('--------------------'));
                            }
                            $cell .= (new Link(
                                $cellContent,
                                ApiDigital::getEndpoint(),
                                null,
                                array(),
                                $i . '. Thema/Hausaufgaben hinzufügen'
                            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $dateStringList[$j], $i == 0 ? -1 : $i, $SubjectId));
                        }
                    // alternativ zum importierten Stundenplan wird nach vorherige Einträge gesucht
                    } elseif ($isAutoTimeTable
                        && ($tblLessonContentTemp  = Digital::useService()->getTimetableFromLastLessonContent(
                            $tblDivisionCourse, new DateTime($dateStringList[$j]), $i
                        ))
                    ) {
                        $cellContent = $tblLessonContentTemp->getDisplaySubject(false);
                        $cell = (new Link(
                            $cellContent,
                            ApiDigital::getEndpoint(),
                            null,
                            array(),
                            $i . '. Thema/Hausaufgaben hinzufügen'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $dateStringList[$j], $i == 0 ? -1 : $i));
                    } else {
                        $cellContent = '<div style="height: 22px"></div>';
                        $cell = (new Link(
                            $cellContent,
                            ApiDigital::getEndpoint(),
                            null,
                            array(),
                            $i . '. Thema/Hausaufgaben hinzufügen'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $dateStringList[$j], $i == 0 ? -1 : $i));
                    }
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
     * @param int|null $SubjectId
     *
     * @return Link
     */
    private function getLessonsNewLink(string $name, DateTime $date, int $Lesson, int $DivisionCourseId, int $SubjectId = null): Link
    {
        return (new Link(
            $name ?: '<div style="height: 22px"></div>',
            ApiDigital::getEndpoint(),
            null,
            array(),
            $Lesson . '. Thema/Hausaufgaben hinzufügen'
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionCourseId, $date->format('d.m.Y'), $Lesson == 0 ? -1 : $Lesson, $SubjectId));
    }

    /**
     * @param string $name
     * @param string $width
     * @param string $backgroundColor
     *
     * @return TableColumn
     */
    public function getTableHeadColumn(string $name, string $width = 'auto', string $backgroundColor = '#E0F0FF'): TableColumn
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
    public function getTextColor(string $content, string $color): string
    {
        return '<span style="color: ' . $color . ';">' . $content . '</span>';
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param null $LessonContentId
     * @param bool $setPost
     * @param string|null $Date
     * @param string|null $Lesson
     * @param string|null $SubjectId
     *
     * @return Form
     */
    public function formLessonContent(TblDivisionCourse $tblDivisionCourse, $LessonContentId = null, bool $setPost = false, string $Date = null,
        string $Lesson = null, string $SubjectId = null): Form
    {
        $isAutoTimeTable = ($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'IsAutoTimeTable'))
            && $tblSetting->getValue();

        $tblSubjectList = Subject::useService()->getSubjectAll();

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

            // deaktiviertes Fach hinzufügen
            if ($tblSubject && !$tblSubject->getIsActive()) {
                $tblSubjectList[] = $tblSubject;
            }
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
            if(null === $Date){
                $Date = 'now';
            }
            // befüllen bei neuen Einträge aus dem importierten Stundenplan
            if ($Date && $Lesson
                && ($tblLessonContentTempList = Timetable::useService()->getLessonContentListFromTimeTableNodeWithReplacementBy(
                    $tblDivisionCourse, new DateTime($Date), (int) $Lesson
                ))
            ) {
                $Global = $this->getGlobal();

                foreach ($tblLessonContentTempList as $tblLessonContentTemp) {
                    $tblSubjectTemp = $tblLessonContentTemp->getServiceTblSubject();
                    if (!$SubjectId || ($tblSubjectTemp && $tblSubjectTemp->getId() == $SubjectId)) {
                        $tblSubject = $tblSubjectTemp;
                        $Global->POST['Data']['serviceTblSubject'] = $tblSubjectTemp ? $tblSubjectTemp->getId() : 0;
                        $Global->POST['Data']['serviceTblSubstituteSubject'] =
                            $tblLessonContentTemp->getServiceTblSubstituteSubject() ? $tblLessonContentTemp->getServiceTblSubstituteSubject()->getId() : 0;
                        $Global->POST['Data']['Room'] = $tblLessonContentTemp->getRoom();
                        $Global->POST['Data']['IsCanceled'] = $tblLessonContentTemp->getIsCanceled() ? 1 : 0;
                        break;
                    }
                }
                $Global->savePost();
            // alternativ zum importierten Stundenplan wird nach vorherige Einträge gesucht
            } elseif ($isAutoTimeTable
                && ($tblLessonContentTemp  = Digital::useService()->getTimetableFromLastLessonContent(
                    $tblDivisionCourse, new DateTime($Date), (int) $Lesson
                ))
            ) {
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
        $inputContent = new TextField('Data[Content]', 'Thema', 'Thema', new Edit());
        if ($tblSubject) {
            $inputContent->setAutoFocus();
        }
        $formRowList[] = new FormRow(array(
            new FormColumn(
                $inputContent
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

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param null $FullTimeContentId
     * @param bool $setPost
     * @param string|null $Date
     *
     * @return Form
     */
    public function formFullTimeContent(TblDivisionCourse $tblDivisionCourse, $FullTimeContentId = null, bool $setPost = false, string $Date = null): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $FullTimeContentId
            && ($tblFullTimeContent = Digital::useService()->getFullTimeContentById($FullTimeContentId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['FromDate'] = $tblFullTimeContent->getFromDateString();
            $Global->POST['Data']['ToDate'] = $tblFullTimeContent->getToDateString();
            $Global->POST['Data']['Content'] = $tblFullTimeContent->getContent();

            $Global->savePost();
        } elseif ($Date) {
            // hinzufügen mit Startwerten
            $Global = $this->getGlobal();
            $Global->POST['Data']['FromDate'] = $Date;
            $Global->savePost();
        }

        if ($FullTimeContentId) {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineEditFullTimeContentSave($FullTimeContentId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateFullTimeContentSave($tblDivisionCourse->getId()));
        }
        $buttonList[] = $saveButton;

        // ganztägig löschen
        if ($FullTimeContentId) {
            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger(
                'Löschen',
                ApiDigital::getEndpoint(),
                new Remove(),
                array(),
                false
            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteFullTimeContentModal($FullTimeContentId));
        }

        $formRowList[] = new FormRow(array(
            new FormColumn(
                (new DatePicker('Data[FromDate]', '', 'von Datum', new Calendar()))->setRequired()
                , 6),
            new FormColumn(
                (new DatePicker('Data[ToDate]', '', 'bis Datum', new Calendar()))
                , 6),
        ));

        $formRowList[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Content]', 'Thema', 'Thema', new Edit())
            ),
        ));

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