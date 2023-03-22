<?php

namespace SPHERE\Application\Education\Absence;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class Frontend extends Extension implements IFrontendInterface
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

                $tblDivisionCourseList = (new Extension())->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
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
}