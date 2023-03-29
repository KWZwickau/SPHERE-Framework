<?php

namespace SPHERE\Application\Education\Absence;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\IMessageInterface;
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
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class Frontend extends FrontendClassRegister
{
    /**
     * @return Stage
     */
    public function frontendAbsenceOverview(): Stage
    {
        $Stage = new Stage('Fehlzeiten', 'Eingabe');

        $now = new DateTime('now');

        $Stage->setContent(
            (new PrimaryLink(
                'Fehlzeit hinzufügen',
                ApiAbsence::getEndpoint(),
                new PlusSign()
            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal())
            . new Container('&nbsp;')
            . ApiAbsence::receiverModal()
            . new Panel(
                new Calendar() . ' Kalender',
                ApiAbsence::receiverBlock($this->LoadOrganizerWeekly($now->format('W') , $now->format('Y')), 'CalendarWeekContent'),
                Panel::PANEL_TYPE_PRIMARY
            )
        );

        return $Stage;
    }

    /**
     * @param string $WeekNumber
     * @param string $Year
     *
     * @return string
     */
    public function LoadOrganizerWeekly(string $WeekNumber = '', string $Year = ''): string
    {
        // Definition
        $currentDate = new DateTime('now');

        if ($WeekNumber == '') {
            $WeekNumber = (int)(new DateTime('now'))->format('W');
        } else {
            $WeekNumber = (int) $WeekNumber;
        }

        if ($Year == '') {
            $Year = (int)$currentDate->format('Y');
        } else {
            $Year = (int) $Year;
        }

        $headerList = array();
        $bodyList = array();

        $organizerBaseData = $this->convertOrganizerBaseData();
        $DayName = $organizerBaseData['dayName'];
        $MonthName = $organizerBaseData['monthNameShort'];

        // Kalenderwoche ermitteln
        $WeekNext = $WeekNumber + 1;
        $WeekBefore = $WeekNumber - 1;
        $YearNext = $Year;
        $YearBefore = $Year;
        $lastWeek = date('W', strtotime("31.12." . $Year));
        $countWeek = ($lastWeek == 1) ? 52 : $lastWeek;
        if ($WeekNumber == $countWeek) {
            $WeekNext = 1;
            $YearNext = $Year + 1;
        }
        if ($WeekNumber == 1) {
            $WeekBefore = $countWeek;
            $YearBefore = $Year - 1;
        }

        // Start-/Endtag der Woche ermitteln
        $Week = $WeekNumber;
        if ($WeekNumber < 10) {
            $Week = '0' . $WeekNumber;
        }
        $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));
        $endDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}-7")));

        $absenceList = array();
        $personList = array();
        if (($tblAbsenceList = Absence::useService()->getAbsenceAllBetween($startDate, $endDate))) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPerson = $tblAbsence->getServiceTblPerson())) {
                    if (!isset($personList[$tblPerson->getId()])) {
                        $personList[$tblPerson->getId()] = $tblPerson;
                    }

                    $fromDate = $tblAbsence->getFromDateTime();
                    $toDate = $tblAbsence->getToDateTime();

                    if ($toDate) {
                        if ($toDate > $fromDate) {
                            $date = $fromDate;
                            while ($date <= $toDate) {
                                self::setAbsenceWeekContent($absenceList, $tblPerson, $tblAbsence, $date->format('d.m.Y'));
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceWeekContent($absenceList, $tblPerson, $tblAbsence, $tblAbsence->getFromDate());
                        }
                    } else {
                        self::setAbsenceWeekContent($absenceList, $tblPerson, $tblAbsence, $tblAbsence->getFromDate());
                    }
                }
            }
        }

        if (!empty($personList)) {
            $startDateString = $startDate->format('d.m.Y');
            $personList = $this->getSorter($personList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            /** @var TblPerson $tblPerson */
            foreach ($personList as $tblPerson) {
                $personId = $tblPerson->getId();
                if (isset($absenceList[$personId])
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson, $startDateString))
                ) {
                    $tblDivision = $tblStudentEducation->getTblDivision();
                    $tblCoreGroup = $tblStudentEducation->getTblCoreGroup();
                    foreach ($absenceList[$personId] as $date => $item) {
                        if ($tblDivision) {
                            $dataList[$tblDivision->getId()][$date][$personId] = $item;
                        }
                        if ($tblCoreGroup) {
                            $dataList[$tblDivision->getId()][$date][$personId] = $item;
                        }
                    }
                }
            }
        }

        $backgroundColor = '#E0F0FF';
        $minHeightHeader = '56px';
        $minHeightBody = '38px';
        $padding = '3px';

        $headerList['Division'] = (new TableColumn(new Center(new Bold('Kurs'))))
            ->setBackgroundColor($backgroundColor)
            ->setVerticalAlign('middle')
            ->setMinHeight($minHeightHeader)
            ->setPadding($padding);

        // Kalender-Inhalt erzeugen
        if (($tblYearList = Term::useService()->getYearAllByDate($startDate))) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionCourseList = array();
                if (($tempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tempList);
                }
                if (($tempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tempList);
                }

                $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                // Content der je Kurs erstellen
                /** @var TblDivisionCourse $tblDivisionCourse */
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    $hasSaturdayLessons = $tblDivisionCourse->getHasSaturdayLessons();
                    $tblCompanyList = $tblDivisionCourse->getCompanyListFromStudents();

                    $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));
                    $countStudent = $tblDivisionCourse->getCountStudents();
                    $bodyList[$tblDivisionCourse->getId()]['Division'] = (new TableColumn(new Center(new Bold($tblDivisionCourse->getName())
                        . new ToolTip(new Small(' (' .  $countStudent  . ')'), $countStudent . ' Schüler'))))
                        ->setBackgroundColor($backgroundColor)
                        ->setVerticalAlign('middle')
                        ->setMinHeight($minHeightBody)
                        ->setPadding($padding);

                    if ($startDate && $endDate) {
                        while ($startDate <= $endDate) {
                            $DayAtWeek = $startDate->format('w');
                            $Day = (int)$startDate->format('d');
                            $Month = (int)$startDate->format('m');

                            if ($hasSaturdayLessons) {
                                $isWeekend = $DayAtWeek == 0;
                            } else {
                                $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
                            }
                            $isHoliday = Term::useService()->getHolidayByDayAndCompanyList($tblYear, $startDate, $tblCompanyList ?: array());

                            if (!isset($headerList['Day' . $Day])) {
                                $columnHeader = (new TableColumn(new Center($DayName[$DayAtWeek] . new Container($Day) . new Container($MonthName[$Month]))))
                                    ->setMinHeight($minHeightHeader)
                                    ->setPadding($padding);

                                if ((int)$currentDate->format('d') == $Day && (int)$currentDate->format('m') == $Month && $currentDate->format('Y') == $Year) {
                                    $columnHeader->setColor('darkorange');
                                }
                                if ($isWeekend || $isHoliday) {
                                    $columnHeader->setBackgroundColor('lightgray')->setOpacity(0.5);
                                } else {
                                    $columnHeader->setBackgroundColor($backgroundColor);
                                }

                                $headerList['Day' . $Day] = $columnHeader;
                            }

                            if ($isWeekend || $isHoliday) {
                                $columnBody = (new TableColumn(new Center($isWeekend ? new Muted(new Small('w')) : new Muted(new Small('f')))))
                                    ->setBackgroundColor('lightgrey')
                                    ->setVerticalAlign('middle')
                                    ->setOpacity(0.5);
                            } else {
                                $columnBody = new TableColumn(new Center(
                                    isset($dataList[$tblDivisionCourse->getId()][$startDate->format('d.m.Y')])
                                        ? implode('<br>', $dataList[$tblDivisionCourse->getId()][$startDate->format('d.m.Y')])
                                        : '&nbsp;'
                                ));
                            }

                            $bodyList[$tblDivisionCourse->getId()]['Day' . $Day] = $columnBody
                                ->setMinHeight($minHeightBody)
                                ->setVerticalAlign('middle')
                                ->setPadding($padding);

                            $startDate->modify('+1 day');
                        }
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
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));

        // Inhalt zusammenbasteln
        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronLeft(), ApiAbsence::getEndpoint(), null, array(), 'KW' . $WeekBefore))
                                            ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeWeek($WeekBefore, $YearBefore))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new ToolTip(new Center(new Bold('KW' . $WeekNumber. ' ')), $Year . '')
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronRight(), ApiAbsence::getEndpoint(), null, array(), 'KW' . $WeekNext))
                                            ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeWeek($WeekNext, $YearNext))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new PullRight((new Link(
                                        ' Herunterladen',
                                        '/Api/Reporting/Standard/Person/AbsenceBetweenList/Download',
                                        new Download(),
                                        array(
                                            'StartDate' => $startDate->format('d.m.Y'),
                                            'EndDate' => $endDate->format('d.m.Y'),
                                        )
                                    )))
                                    , 3)
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

        return $Content . ' ';
    }

    /**
     * @param $dataList
     * @param TblPerson $tblPerson
     * @param TblAbsence $tblAbsence
     * @param $date string
     */
    private static function setAbsenceWeekContent(
        &$dataList,
        TblPerson $tblPerson,
        TblAbsence $tblAbsence,
        string $date
    ) {
        // bei Unterrichtseinheiten dahinter in Klammern (1.UE)
        // E entschuldig, U unentschuldig
        // T Theorie, P Praxis
        // [Vorname] [Nachname] ( [[UE]] / [T/P] / [U/E])

        $countLessons = 0;
        $lesson = $tblAbsence->getLessonStringByAbsence($countLessons);
        $type = $tblAbsence->getTypeDisplayShortName();
        $tblPersonStaff = $tblAbsence->getDisplayStaff();
        $tblPersonStaffToolTip = $tblAbsence->getDisplayStaffToolTip();
        $remark = $tblAbsence->getRemark();

        $dataList[$tblPerson->getId()][$date] = (new Link(
            $tblPerson->getLastFirstName()
                . ' ('
                . $tblAbsence->getStatusDisplayShortName()
                . ($countLessons > 0 ? ' - ' . $countLessons . 'UE' : '')
                . ($tblPersonStaff ? ' - ' . $tblPersonStaff : '')
                . ')',
            ApiAbsence::getEndpoint(),
            null,
            array(),
            ($lesson ? $lesson . ' / ': '') . ($type ? $type . ' / ': '') . $tblAbsence->getStatusDisplayShortName()
                . ($tblPersonStaffToolTip ? ' - ' . $tblPersonStaffToolTip : '')
                . ($remark ? ' - ' . $remark : ''),
            null,
            $tblAbsence->getLinkType()
        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId()));
    }

    /**
     * @return array
     */
    public static function convertOrganizerBaseData(): array
    {
        $data['dayName'] = array(
            '0' => 'So',
            '1' => 'Mo',
            '2' => 'Di',
            '3' => 'Mi',
            '4' => 'Do',
            '5' => 'Fr',
            '6' => 'Sa',
        );

        $data['monthName'] = array(
            '1' =>"Januar",
            '2' =>"Februar",
            '3' =>"März",
            '4' =>"April",
            '5' =>"Mai",
            '6' =>"Juni",
            '7' =>"Juli",
            '8' =>"August",
            '9' =>"September",
            '10' =>"Oktober",
            '11' =>"November",
            '12' =>"Dezember"
        );

        $data['monthNameShort'] = array(
            '1' =>"Jan",
            '2' =>"Feb",
            '3' =>"März",
            '4' =>"Apr",
            '5' =>"Mai",
            '6' =>"Jun",
            '7' =>"Jul",
            '8' =>"Aug",
            '9' =>"Sept",
            '10' =>"Okt",
            '11' =>"Nov",
            '12' =>"Dez"
        );

        return $data;
    }

    /**
     * @param $AbsenceId
     * @param bool $hasSearch
     * @param $Search
     * @param $Data
     * @param $PersonId
     * @param $DivisionCourseId
     * @param IMessageInterface|null $messageSearch
     * @param IMessageInterface|null $messageLesson
     * @param $Date
     *
     * @return Form
     */
    public function formAbsence(
        $AbsenceId = null,
        bool $hasSearch = false,
        $Search = null,
        $Data = null,
        $PersonId = null,
        $DivisionCourseId = null,
        IMessageInterface $messageSearch = null,
        IMessageInterface $messageLesson = null,
        $Date = null
    ): Form {
        if ($Data === null && $AbsenceId === null) {
            $isFullDay = true;

            $global = $this->getGlobal();
            $global->POST['Data']['IsFullDay'] = $isFullDay;

            if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'DefaultStatusForNewAbsence'))) {
                $status = $tblSetting->getValue();
            } else {
                $status = TblAbsence::VALUE_STATUS_UNEXCUSED;
            }
            $global->POST['Data']['Status'] = $status;

            $global->POST['Data']['IsCertificateRelevant'] = true;
            if ($Date) {
                $global->POST['Data']['FromDate'] = $Date;
            }

            $global->savePost();
        } elseif ($Data === null && $AbsenceId && ($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            $global = $this->getGlobal();
            if(($lessons = Absence::useService()->getLessonAllByAbsence($tblAbsence))) {
                $isFullDay = false;
                foreach($lessons as $lesson) {
                    $global->POST['Data']['UE'][$lesson] = 1;
                }
            } else {
                $isFullDay = true;
            }

            $global->POST['Data']['IsFullDay'] = $isFullDay;
            $global->POST['Data']['FromDate'] = $tblAbsence->getFromDate();
            $global->POST['Data']['ToDate'] = $tblAbsence->getToDate();
            $global->POST['Data']['Remark'] = $tblAbsence->getRemark();
            $global->POST['Data']['Type'] = $tblAbsence->getType();
            $global->POST['Data']['Status'] = $tblAbsence->getStatus();
            $global->POST['Data']['IsCertificateRelevant'] = $tblAbsence->getIsCertificateRelevant();

            $global->savePost();
        } else {
            $isFullDay = $Data['IsFullDay'] ?? false;
        }

        if ($AbsenceId) {
            $saveButton = (new PrimaryLink('Speichern', ApiAbsence::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAbsence::pipelineEditAbsenceSave($AbsenceId, $DivisionCourseId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiAbsence::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAbsence::pipelineCreateAbsenceSave($PersonId, $DivisionCourseId, $hasSearch));
        }

        $formRows = array();
        if (!$PersonId && !$AbsenceId && $DivisionCourseId
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $formRows[] = new FormRow(new FormColumn(
                (new SelectBox('Data[PersonId]', 'Schüler', array('{{ LastFirstName }}' => $tblPersonList)))
                    ->setRequired()
                    ->ajaxPipelineOnChange(ApiAbsence::pipelineLoadType())
            ));
        } elseif ($hasSearch) {
            $formRows[] = new FormRow(array(
                new FormColumn(array(
                    new Panel(
                        'Schüler',
                        (new TextField(
                            'Search',
                            '',
                            'Suche',
                            new Search()
                        ))->ajaxPipelineOnKeyUp(ApiAbsence::pipelineSearchPerson())
                        . ApiAbsence::receiverBlock($this->loadPersonSearch($Search, $messageSearch), 'SearchPerson')
                        , Panel::PANEL_TYPE_INFO
                    )
                ))
            ));
        }

        $formRows[] = new FormRow(array(
            new FormColumn(
                new DatePicker('Data[FromDate]', '', 'Datum von', new Calendar()), 6
            ),
            new FormColumn(
                new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 6
            ),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(array(
                (new CheckBox('Data[IsFullDay]', 'ganztägig', 1))->ajaxPipelineOnClick(ApiAbsence::pipelineLoadLesson()),
                ApiAbsence::receiverBlock($this->loadLesson($isFullDay, $messageLesson), 'loadLesson')
            ))
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                ApiAbsence::receiverBlock($this->loadType($PersonId, $Date ?: 'now'), 'loadType')
            )
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Remark]', '', 'Bemerkung'), 12
            ),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                new Panel(
                    'Status',
                    array(
                        new RadioBox('Data[Status]', 'entschuldigt', TblAbsence::VALUE_STATUS_EXCUSED),
                        new RadioBox('Data[Status]', 'unentschuldigt', TblAbsence::VALUE_STATUS_UNEXCUSED)
                    ),
                    Panel::PANEL_TYPE_INFO
                )
            ),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                new CheckBox('Data[IsCertificateRelevant]', 'zeugnisrelevant', 1)
            )
        ));

        $buttons = array();
        $buttons[] = $saveButton;
        if ($AbsenceId) {
            $buttons[] = (new Danger(
                'Löschen',
                ApiAbsence::getEndpoint(),
                new Remove(),
                array(),
                false
            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenDeleteAbsenceModal($AbsenceId, $DivisionCourseId));
        }

        $formRows[] = new FormRow(array(
            new FormColumn($buttons)
        ));

        return (new Form(new FormGroup(
            $formRows
        )))->disableSubmitAction();
    }

    /**
     * @param $Search
     * @param IMessageInterface|null $message
     *
     * @return string
     */
    public function loadPersonSearch($Search, IMessageInterface $message = null): string
    {
        if ($Search != '' && strlen($Search) > 2) {
            $resultList = array();
            $result = '';
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                foreach ($tblPersonList as $tblPerson) {
                    // nur nach Schülern suchen
                    if (Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        $radio = (new RadioBox('Data[PersonId]', '&nbsp;', $tblPerson->getId()))->ajaxPipelineOnClick(
                            ApiAbsence::pipelineLoadType()
                        );

                        $resultList[] = array(
                            'Select' => $radio,
                            'FirstName' => $tblPerson->getFirstSecondName(),
                            'LastName' => $tblPerson->getLastName(),
                            'Division' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson)
                        );
                    }
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Select' => '',
                        'LastName' => 'Nachname',
                        'FirstName' => 'Vorname',
                        'Division' => 'Kurse'
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            }

            if (empty($resultList)) {
                $result = new Warning('Es wurden keine entsprechenden Schüler gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result . ($message ?: '');
    }

    /**
     * @param bool $IsFullDay
     * @param IMessageInterface|null $message
     *
     * @return string
     */
    public function loadLesson(bool $IsFullDay, IMessageInterface $message = null): string
    {
        if ($IsFullDay) {
            if ($message === null) {
                return '';
            } else {
                return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($message))));
            }

        } else {
            $left = array();
            $right = array();
            for ($i = 0; $i < 7; $i++) {
                $left[] = $this->setCheckBoxLesson($i);
                if ($i < 6) {
                    $right[] = $this->setCheckBoxLesson($i + 7);
                }
            }

            return new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn($left, 6),
                    new LayoutColumn($right, 6)
                )),
                new LayoutRow(array(
                    new LayoutColumn($message)
                )),
            )));
        }
    }

    /**
     * @param $i
     *
     * @return CheckBox
     */
    private function setCheckBoxLesson($i): CheckBox
    {
        return new CheckBox('Data[UE][' . $i . ']', $i . '. Unterrichtseinheit', 1);
    }

    /**
     * @param null $PersonId
     * @param string $date
     *
     * @return string
     */
    public function loadType($PersonId = null, string $date = 'today'): string
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson, $date))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && $tblSchoolType->isTechnical()
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['Type'] = TblAbsence::VALUE_TYPE_THEORY;
            $global->savePost();

            return new SelectBox('Data[Type]', 'Typ', array(
                TblAbsence::VALUE_TYPE_PRACTICE => 'Praxis',
                TblAbsence::VALUE_TYPE_THEORY => 'Theorie'
            ));
        }

        return '';
    }
}